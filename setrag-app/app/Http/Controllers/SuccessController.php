<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuccessController extends Controller
{
    public function index(Request $request)
    {
        $bookingData = session('booking_confirmed');
        
        if (!$bookingData) {
            return redirect()->route('book')->with('error', 'Aucune réservation confirmée');
        }

        // Try to get full booking details from database
        $booking = null;
        if (isset($bookingData['pnr'])) {
            $booking = \App\Models\Booking::with(['trip.originStation', 'trip.destinationStation'])
                ->where('pnr', $bookingData['pnr'])
                ->first();
        }

        return view('success', compact('booking', 'bookingData'));
    }
}

