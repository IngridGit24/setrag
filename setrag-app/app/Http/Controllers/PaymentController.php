<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Booking;
use App\Models\Seat;
use App\Services\PricingService;
use App\Services\EbillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        private PricingService $pricingService,
        private EbillingService $ebillingService
    ) {
    }

    public function store(Request $request)
    {
        // Store booking info in session from POST request
        $validator = \Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'passengers' => 'sometimes|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->route('book')->withErrors($validator)->withInput();
        }

        $bookingInfo = [
            'trip_id' => $request->trip_id,
            'passengers' => $request->passengers ?? 1,
        ];
        
        session(['pending_booking' => $bookingInfo]);

        return redirect()->route('payment');
    }

    public function show(Request $request)
    {
        // Get booking info from session
        $bookingInfo = session('pending_booking');
        
        if (!$bookingInfo || !isset($bookingInfo['trip_id'])) {
            return redirect()->route('book')->with('error', 'Veuillez sélectionner un trajet');
        }

        $trip = Trip::with(['originStation', 'destinationStation'])->find($bookingInfo['trip_id']);
        
        if (!$trip) {
            return redirect()->route('book')->with('error', 'Trajet introuvable');
        }

        // Get default values from request or session
        $class = $request->input('class', $bookingInfo['class'] ?? 'second_class');
        $passengerType = $request->input('passenger_type', $bookingInfo['passenger_type'] ?? 'adult');
        $birthDate = $request->input('passenger_birth_date', $bookingInfo['passenger_birth_date'] ?? null);
        $passengers = $bookingInfo['passengers'] ?? 1;

        // Calculate quote with class and passenger type
        $quote = $this->pricingService->getQuote($trip, $class, $passengerType, $birthDate, $passengers);

        // Update booking info in session
        $bookingInfo['class'] = $class;
        $bookingInfo['passenger_type'] = $passengerType;
        $bookingInfo['passenger_birth_date'] = $birthDate;
        session(['pending_booking' => $bookingInfo]);

        return view('payment', compact('trip', 'bookingInfo', 'quote'));
    }

    public function process(Request $request)
    {
        $bookingInfo = session('pending_booking');
        
        if (!$bookingInfo) {
            return redirect()->route('book')->with('error', 'Aucune réservation en cours');
        }

        $validator = \Validator::make($request->all(), [
            'payment_method' => 'required|in:card,airtel,moov',
            'passenger_name' => 'required|string|max:255',
            'passenger_email' => 'required|email|max:255',
            'class' => 'required|in:second_class,first_class,VIP',
            'passenger_type' => 'required|in:adult,student,senior,child',
            'passenger_birth_date' => 'nullable|date|before:today',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create booking directly using the booking service logic
        try {
            $trip = Trip::find($bookingInfo['trip_id']);
            if (!$trip) {
                return back()->with('error', 'Trajet introuvable');
            }

            $passengers = $bookingInfo['passengers'] ?? 1;
            $class = $request->input('class', $bookingInfo['class'] ?? 'second_class');
            $passengerType = $request->input('passenger_type', $bookingInfo['passenger_type'] ?? 'adult');
            $birthDate = $request->input('passenger_birth_date', $bookingInfo['passenger_birth_date'] ?? null);
            
            // Vérifier si c'est un enfant de moins de 5 ans (doit être accompagné)
            if ($passengerType === 'child' && $birthDate) {
                $age = \Carbon\Carbon::parse($birthDate)->age;
                if ($age < 5 && $passengers === 1) {
                    return back()->with('error', 'Les enfants de moins de 5 ans doivent être accompagnés d\'un adulte.');
                }
            }
            
            // Allocate a seat of the requested class
            $seat = \App\Models\Seat::where('trip_id', $trip->id)
                ->where('class', $class)
                ->where(function ($query) {
                    $query->where('status', 'AVAILABLE')
                        ->orWhere(function ($q) {
                            $q->where('status', 'HELD')
                                ->where(function ($q2) {
                                    $q2->whereNull('hold_expires_at')
                                        ->orWhere('hold_expires_at', '<', now());
                                });
                        });
                })
                ->orderBy('id')
                ->first();

            if (!$seat) {
                return back()->with('error', 'Aucun siège disponible en ' . $this->getClassLabel($class) . ' pour ce trajet');
            }

            // Hold the seat
            $seat->status = 'HELD';
            $seat->hold_expires_at = now()->addMinutes(20);
            $seat->save();

            // Get price quote with class and passenger type
            $quote = $this->pricingService->getQuote($trip, $class, $passengerType, $birthDate, $passengers);

            // Generate PNR
            $pnr = $this->generatePNR();

            // Get user ID from session
            $userId = null;
            if (session('user') && isset(session('user')['id'])) {
                $userId = session('user')['id'];
            }

            // Create booking with PENDING status (will be confirmed after EBILLING payment)
            $booking = Booking::create([
                'pnr' => $pnr,
                'trip_id' => $trip->id,
                'seat_no' => $seat->seat_no,
                'class' => $class,
                'amount' => $quote['total_price'],
                'base_price' => $quote['base_price'],
                'discount_amount' => $quote['discount_amount'],
                'commission' => $quote['commission'],
                'currency' => $quote['currency'],
                'status' => 'PENDING', // Will be confirmed after EBILLING payment
                'payment_status' => 'pending',
                'idempotency_key' => 'web-' . now()->timestamp . '-' . uniqid(),
                'user_id' => $userId,
                'passenger_name' => $request->passenger_name,
                'passenger_email' => $request->passenger_email,
                'passenger_type' => $passengerType,
                'passenger_birth_date' => $birthDate,
                'payment_method' => $request->payment_method,
            ]);

            // Vérifier si on est en mode simulation (localhost ou credentials non configurés)
            $isSimulationMode = $this->shouldUseSimulation();

            if ($isSimulationMode) {
                // Mode simulation : rediriger vers la page de simulation appropriée
                session(['pending_booking_simulation' => [
                    'booking_id' => $booking->id,
                    'pnr' => $pnr,
                ]]);
                session()->forget('pending_booking');

                // Rediriger vers la page de simulation selon la méthode choisie
                $paymentMethod = $request->payment_method;
                return match($paymentMethod) {
                    'airtel' => redirect()->route('payment.simulation.airtel', ['booking_id' => $booking->id]),
                    'moov' => redirect()->route('payment.simulation.moov', ['booking_id' => $booking->id]),
                    'card' => redirect()->route('payment.simulation.card', ['booking_id' => $booking->id]),
                    default => redirect()->route('payment.simulation.card', ['booking_id' => $booking->id]),
                };
            }

            // Mode production : utiliser EBILLING
            $ebillingData = [
                'amount' => $quote['total_price'],
                'customer' => [
                    'name' => $request->passenger_name,
                    'email' => $request->passenger_email,
                    'phone' => session('user')['phone'] ?? '',
                ],
                'description' => "Réservation billet SETRAG - {$trip->originStation->name} → {$trip->destinationStation->name}",
                'reference' => $pnr,
                'metadata' => [
                    'booking_id' => $booking->id,
                    'trip_id' => $trip->id,
                    'pnr' => $pnr,
                ],
            ];

            $ebillingResult = $this->ebillingService->createPayment($ebillingData);

            if (!$ebillingResult['success']) {
                // Rollback: release seat and delete booking
                $seat->status = 'AVAILABLE';
                $seat->hold_expires_at = null;
                $seat->save();
                $booking->delete();

                $errorMessage = $ebillingResult['error'] ?? 'Erreur inconnue lors de l\'initialisation du paiement';
                
                // Si c'est un problème de configuration, afficher un message plus détaillé
                if (str_contains($errorMessage, 'Configuration EBILLING incomplète') || 
                    str_contains($errorMessage, 'Identifiants EBILLING invalides')) {
                    return back()->with('error', $errorMessage . '. Contactez l\'administrateur pour plus d\'informations.');
                }

                return back()->with('error', $errorMessage);
            }

            // Save bill_id to booking
            $booking->update(['bill_id' => $ebillingResult['bill_id']]);

            // Store booking info in session for redirect
            session(['pending_ebilling_payment' => [
                'booking_id' => $booking->id,
                'pnr' => $pnr,
            ]]);
            session()->forget('pending_booking');

            // Redirect to EBILLING payment page
            return redirect($ebillingResult['payment_link']);
        } catch (\Exception $e) {
            \Log::error('Booking error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la création de la réservation: ' . $e->getMessage());
        }
    }

    /**
     * Generate a PNR (Passenger Name Record) - ULID-like format
     */
    private function generatePNR(): string
    {
        // Generate ULID-like identifier
        // Format: 10 chars timestamp + 16 chars random
        $timestamp = base_convert((string) (microtime(true) * 1000), 10, 36);
        $random = Str::random(16);
        
        // Ensure we have exactly 26 characters
        $pnr = str_pad($timestamp, 10, '0', STR_PAD_LEFT) . $random;
        $pnr = substr($pnr, 0, 26);
        
        // Ensure uniqueness
        while (\App\Models\Booking::where('pnr', $pnr)->exists()) {
            $random = Str::random(16);
            $pnr = str_pad($timestamp, 10, '0', STR_PAD_LEFT) . $random;
            $pnr = substr($pnr, 0, 26);
        }
        
        return strtoupper($pnr);
    }

    /**
     * Get human-readable class label
     */
    private function getClassLabel(string $class): string
    {
        return match($class) {
            'VIP' => 'VIP',
            'first_class' => '1ère classe',
            'second_class' => '2ème classe',
            default => $class,
        };
    }

    /**
     * Handle successful payment redirect from EBILLING
     */
    public function handleSuccess(Request $request)
    {
        $pendingPayment = session('pending_ebilling_payment');
        
        if (!$pendingPayment) {
            return redirect()->route('book')->with('error', 'Session expirée');
        }

        $booking = Booking::with(['trip.originStation', 'trip.destinationStation'])
            ->find($pendingPayment['booking_id']);

        if (!$booking) {
            return redirect()->route('book')->with('error', 'Réservation introuvable');
        }

        // Check if payment was confirmed via callback
        if ($booking->payment_status === 'paid' && $booking->status === 'CONFIRMED') {
            session(['booking_confirmed' => [
                'pnr' => $booking->pnr,
                'amount' => (float) $booking->amount,
                'currency' => $booking->currency,
            ]]);
            session()->forget('pending_ebilling_payment');
            
            return redirect()->route('success');
        }

        // If not yet confirmed, check status with EBILLING
        if ($booking->bill_id) {
            $status = $this->ebillingService->getBillStatus($booking->bill_id);
            
            if ($status['success'] && in_array($status['status'], ['paid', 'processed'])) {
                // Payment confirmed, update booking
                $booking->update([
                    'status' => 'CONFIRMED',
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);

                // Confirm seat
                $seat = Seat::where('trip_id', $booking->trip_id)
                    ->where('seat_no', $booking->seat_no)
                    ->first();
                if ($seat) {
                    $seat->status = 'SOLD';
                    $seat->hold_expires_at = null;
                    $seat->save();
                }

                session(['booking_confirmed' => [
                    'pnr' => $booking->pnr,
                    'amount' => (float) $booking->amount,
                    'currency' => $booking->currency,
                ]]);
                session()->forget('pending_ebilling_payment');
                
                return redirect()->route('success');
            }
        }

        // Still pending, show waiting message
        return view('payment.pending', compact('booking'));
    }

    /**
     * Handle failed payment redirect from EBILLING
     */
    public function handleFailure(Request $request)
    {
        $pendingPayment = session('pending_ebilling_payment');
        
        if ($pendingPayment) {
            $booking = Booking::find($pendingPayment['booking_id']);
            
            if ($booking) {
                // Release seat
                $seat = Seat::where('trip_id', $booking->trip_id)
                    ->where('seat_no', $booking->seat_no)
                    ->first();
                if ($seat) {
                    $seat->status = 'AVAILABLE';
                    $seat->hold_expires_at = null;
                    $seat->save();
                }

                // Update booking status
                $booking->update([
                    'status' => 'CANCELLED',
                    'payment_status' => 'failed',
                ]);
            }

            session()->forget('pending_ebilling_payment');
        }

            return view('payment.failed');
    }

    /**
     * Détermine si on doit utiliser le mode simulation
     */
    private function shouldUseSimulation(): bool
    {
        // Mode simulation si :
        // 1. On est sur localhost
        // 2. Les credentials EBILLING ne sont pas configurés
        // 3. Variable d'environnement FORCE_SIMULATION est définie
        
        $appUrl = config('app.url');
        $isLocalhost = str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1');
        
        $ebillingUsername = config('ebilling.username');
        $ebillingSharedKey = config('ebilling.shared_key');
        $credentialsMissing = empty($ebillingUsername) || empty($ebillingSharedKey);
        
        $forceSimulation = env('FORCE_PAYMENT_SIMULATION', false);
        
        return $isLocalhost || $credentialsMissing || $forceSimulation;
    }
}

