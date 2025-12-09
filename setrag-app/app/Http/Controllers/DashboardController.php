<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('auth')->with('error', 'Veuillez vous connecter');
        }

        // Get bookings by user_id or by passenger_email if user_id is null
        $bookings = Booking::where(function($query) use ($user) {
                if (isset($user['id'])) {
                    $query->where('user_id', $user['id']);
                }
                if (isset($user['email'])) {
                    $query->orWhere('passenger_email', $user['email']);
                }
            })
            ->with('trip.originStation', 'trip.destinationStation')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard', compact('bookings', 'user'));
    }

    /**
     * Afficher les détails d'une réservation
     */
    public function show(Booking $booking)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('auth')->with('error', 'Veuillez vous connecter');
        }

        // Vérifier que la réservation appartient à l'utilisateur
        $isOwner = false;
        if (isset($user['id']) && $booking->user_id == $user['id']) {
            $isOwner = true;
        }
        if (isset($user['email']) && $booking->passenger_email == $user['email']) {
            $isOwner = true;
        }

        if (!$isOwner) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas accès à cette réservation');
        }

        $booking->load('trip.originStation', 'trip.destinationStation');

        return view('booking.show', compact('booking', 'user'));
    }

    /**
     * Supprimer une réservation
     */
    public function destroy(Request $request, Booking $booking)
    {
        $user = session('user');
        
        if (!$user) {
            return redirect()->route('auth')->with('error', 'Veuillez vous connecter');
        }

        // Vérifier que la réservation appartient à l'utilisateur
        $isOwner = false;
        if (isset($user['id']) && $booking->user_id == $user['id']) {
            $isOwner = true;
        }
        if (isset($user['email']) && $booking->passenger_email == $user['email']) {
            $isOwner = true;
        }

        if (!$isOwner) {
            return redirect()->route('dashboard')->with('error', 'Vous n\'avez pas accès à cette réservation');
        }

        // Vérifier si la réservation peut être supprimée
        // On ne peut supprimer que les réservations PENDING ou CANCELLED
        // Les réservations CONFIRMED ne peuvent pas être supprimées (annulation nécessaire)
        if ($booking->status === 'CONFIRMED') {
            return redirect()->route('dashboard')->with('error', 'Les réservations confirmées ne peuvent pas être supprimées. Veuillez contacter le support pour une annulation.');
        }

        try {
            DB::beginTransaction();

            // Libérer le siège si la réservation était en attente
            if ($booking->status === 'PENDING' || $booking->status === 'HELD') {
                $seat = Seat::where('trip_id', $booking->trip_id)
                    ->where('seat_no', $booking->seat_no)
                    ->first();
                
                if ($seat) {
                    $seat->update([
                        'status' => 'AVAILABLE',
                        'hold_expires_at' => null,
                    ]);
                }
            }

            // Supprimer la réservation
            $booking->delete();

            DB::commit();

            return redirect()->route('dashboard')->with('success', 'Réservation supprimée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression de la réservation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard')->with('error', 'Erreur lors de la suppression de la réservation');
        }
    }
}

