<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

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
}

