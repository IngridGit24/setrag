<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Trip;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $stations = Station::all();
        $trips = collect();
        
        // If search parameters are provided, filter trips
        if ($request->has('origin_station_id') && $request->has('destination_station_id')) {
            $query = Trip::with(['originStation', 'destinationStation'])
                ->where('origin_station_id', $request->origin_station_id)
                ->where('destination_station_id', $request->destination_station_id);
            
            // Filter by date if provided
            if ($request->has('departure_date') && $request->departure_date) {
                $query->whereDate('departure_time', $request->departure_date);
            }
            
            // Only show future trips
            $query->where('departure_time', '>=', now());
            
            $trips = $query->orderBy('departure_time', 'asc')->get();
        }
        
        return view('book', compact('stations', 'trips'));
    }
}

