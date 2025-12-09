<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Seat;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentSimulationController extends Controller
{
    public function __construct(
        private PricingService $pricingService
    ) {
    }

    /**
     * Afficher la page de simulation pour Airtel Money
     */
    public function showAirtel(Request $request)
    {
        $bookingId = $request->query('booking_id');
        $booking = Booking::with(['trip.originStation', 'trip.destinationStation'])->find($bookingId);
        
        if (!$booking) {
            return redirect()->route('book')->with('error', 'Réservation introuvable');
        }

        return view('payment.simulate.airtel', compact('booking'));
    }

    /**
     * Afficher la page de simulation pour Moov Money
     */
    public function showMoov(Request $request)
    {
        $bookingId = $request->query('booking_id');
        $booking = Booking::with(['trip.originStation', 'trip.destinationStation'])->find($bookingId);
        
        if (!$booking) {
            return redirect()->route('book')->with('error', 'Réservation introuvable');
        }

        return view('payment.simulate.moov', compact('booking'));
    }

    /**
     * Afficher la page de simulation pour Visa/Mastercard
     */
    public function showCard(Request $request)
    {
        $bookingId = $request->query('booking_id');
        $booking = Booking::with(['trip.originStation', 'trip.destinationStation'])->find($bookingId);
        
        if (!$booking) {
            return redirect()->route('book')->with('error', 'Réservation introuvable');
        }

        return view('payment.simulate.card', compact('booking'));
    }

    /**
     * Traiter le paiement simulé Airtel Money
     */
    public function processAirtel(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'phone_number' => 'required|string|min:9|max:15',
        ]);

        return $this->processSimulatedPayment($request->booking_id, 'airtel', [
            'phone_number' => $request->phone_number,
        ]);
    }

    /**
     * Traiter le paiement simulé Moov Money
     */
    public function processMoov(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'phone_number' => 'required|string|min:9|max:15',
        ]);

        return $this->processSimulatedPayment($request->booking_id, 'moov', [
            'phone_number' => $request->phone_number,
        ]);
    }

    /**
     * Traiter le paiement simulé Visa/Mastercard
     */
    public function processCard(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'card_number' => 'required|string|size:16',
            'card_holder' => 'required|string|max:255',
            'expiry_month' => 'required|string|size:2',
            'expiry_year' => 'required|string|size:4',
            'cvv' => 'required|string|size:3',
        ]);

        return $this->processSimulatedPayment($request->booking_id, 'card', [
            'card_number' => substr($request->card_number, -4), // Derniers 4 chiffres seulement
            'card_holder' => $request->card_holder,
        ]);
    }

    /**
     * Traiter un paiement simulé
     */
    private function processSimulatedPayment(int $bookingId, string $paymentMethod, array $paymentData)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::with(['trip.originStation', 'trip.destinationStation'])->find($bookingId);
            
            if (!$booking) {
                return redirect()->route('book')->with('error', 'Réservation introuvable');
            }

            // Vérifier que la réservation est en attente
            if ($booking->status !== 'PENDING') {
                return redirect()->route('book')->with('error', 'Cette réservation a déjà été traitée');
            }

            // Simuler un délai de traitement (1-2 secondes)
            sleep(1);

            // Confirmer la réservation
            $booking->update([
                'status' => 'CONFIRMED',
                'payment_status' => 'paid',
                'paid_at' => now(),
                'transaction_id' => 'SIM-' . strtoupper(Str::random(12)),
                'payment_method' => $paymentMethod,
            ]);

            // Confirmer le siège
            $seat = Seat::where('trip_id', $booking->trip_id)
                ->where('seat_no', $booking->seat_no)
                ->first();
            
            if ($seat) {
                $seat->update([
                    'status' => 'SOLD',
                    'hold_expires_at' => null,
                ]);
            }

            DB::commit();

            // Stocker les données pour la page de succès
            session(['booking_confirmed' => [
                'pnr' => $booking->pnr,
                'amount' => (float) $booking->amount,
                'currency' => $booking->currency,
                'payment_method' => $paymentMethod,
                'payment_data' => $paymentData,
            ]]);

            return redirect()->route('payment.simulation.success');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur simulation paiement', [
                'error' => $e->getMessage(),
                'booking_id' => $bookingId,
            ]);

            return redirect()->route('payment.failed')
                ->with('error', 'Erreur lors du traitement du paiement simulé');
        }
    }

    /**
     * Afficher la page de succès de la simulation
     */
    public function success()
    {
        $bookingData = session('booking_confirmed');
        
        if (!$bookingData) {
            return redirect()->route('book')->with('error', 'Aucune réservation confirmée');
        }

        // Récupérer les détails complets de la réservation
        $booking = null;
        if (isset($bookingData['pnr'])) {
            $booking = Booking::with(['trip.originStation', 'trip.destinationStation'])
                ->where('pnr', $bookingData['pnr'])
                ->first();
        }

        return view('payment.simulate.success', compact('booking', 'bookingData'));
    }
}

