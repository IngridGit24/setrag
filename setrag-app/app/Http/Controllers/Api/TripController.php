<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $trips = Trip::with(['originStation', 'destinationStation'])->get();

        return response()->json($trips->map(function ($trip) {
            return [
                'id' => $trip->id,
                'origin_station_id' => $trip->origin_station_id,
                'destination_station_id' => $trip->destination_station_id,
                'departure_time' => $trip->departure_time->toIso8601String(),
                'arrival_time' => $trip->arrival_time->toIso8601String(),
            ];
        }));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_station_id' => 'required|exists:stations,id',
            'destination_station_id' => 'required|exists:stations,id',
            'departure_time' => 'required|date',
            'arrival_time' => 'required|date|after:departure_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $trip = Trip::create($request->all());

        return response()->json([
            'id' => $trip->id,
            'origin_station_id' => $trip->origin_station_id,
            'destination_station_id' => $trip->destination_station_id,
            'departure_time' => $trip->departure_time->toIso8601String(),
            'arrival_time' => $trip->arrival_time->toIso8601String(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $trip = Trip::with(['originStation', 'destinationStation'])->find($id);

        if (!$trip) {
            return response()->json(['error' => 'Trip not found'], 404);
        }

        return response()->json([
            'id' => $trip->id,
            'origin_station_id' => $trip->origin_station_id,
            'destination_station_id' => $trip->destination_station_id,
            'departure_time' => $trip->departure_time->toIso8601String(),
            'arrival_time' => $trip->arrival_time->toIso8601String(),
        ]);
    }
}
