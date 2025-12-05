<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Seat;
use App\Services\EbillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EbillingCallbackController extends Controller
{
    public function __construct(
        private EbillingService $ebillingService
    ) {
    }

    /**
     * Recevoir et traiter les callbacks d'EBILLING
     */
    public function handleCallback(Request $request)
    {
        $callbackData = $request->all();

        Log::info('EBILLING: Callback reçu', ['data' => $callbackData]);

        // Valider les données
        if (!$this->ebillingService->validateCallback($callbackData)) {
            Log::warning('EBILLING: Callback invalide', ['data' => $callbackData]);
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
            ], 400);
        }

        // Normaliser les données
        $processedData = $this->ebillingService->processCallbackData($callbackData);
        $billId = $processedData['bill_id'];
        $status = $processedData['mapped_status'];

        Log::info('EBILLING: Traitement du callback', [
            'bill_id' => $billId,
            'status' => $status,
        ]);

        // Trouver la réservation correspondante
        $booking = Booking::where('bill_id', $billId)->first();

        if (!$booking) {
            Log::warning('EBILLING: Réservation non trouvée', ['bill_id' => $billId]);
            return response()->json([
                'success' => false,
                'message' => 'Réservation non trouvée',
            ], 404);
        }

        // Vérifier si le paiement a déjà été traité
        if ($booking->status === 'CONFIRMED' && $status === 'completed') {
            Log::info('EBILLING: Paiement déjà traité', ['bill_id' => $billId]);
            return response()->json([
                'success' => true,
                'message' => 'Paiement déjà traité',
            ]);
        }

        // Traiter le paiement selon le statut
        try {
            DB::beginTransaction();

            if ($status === 'completed') {
                // Paiement réussi
                $booking->update([
                    'status' => 'CONFIRMED',
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'transaction_id' => $processedData['transaction_id'],
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

                Log::info('EBILLING: Réservation confirmée', [
                    'booking_id' => $booking->id,
                    'pnr' => $booking->pnr,
                ]);

            } elseif (in_array($status, ['failed', 'cancelled', 'expired'])) {
                // Paiement échoué
                $booking->update([
                    'status' => 'CANCELLED',
                    'payment_status' => $status,
                ]);

                // Libérer le siège
                if ($booking->seat) {
                    $booking->seat->update([
                        'status' => 'AVAILABLE',
                        'hold_expires_at' => null,
                    ]);
                }

                Log::info('EBILLING: Réservation annulée', [
                    'booking_id' => $booking->id,
                    'status' => $status,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Callback traité avec succès',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('EBILLING: Erreur lors du traitement du callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement',
            ], 500);
        }
    }
}

