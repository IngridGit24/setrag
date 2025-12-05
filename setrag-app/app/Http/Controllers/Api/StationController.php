<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stations = Station::all();

        return response()->json($stations->map(function ($station) {
            return [
                'id' => $station->id,
                'name' => $station->name,
                'latitude' => (float) $station->latitude,
                'longitude' => (float) $station->longitude,
            ];
        }));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $station = Station::create($request->all());

        return response()->json([
            'id' => $station->id,
            'name' => $station->name,
            'latitude' => (float) $station->latitude,
            'longitude' => (float) $station->longitude,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $station = Station::find($id);

        if (!$station) {
            return response()->json(['error' => 'Station not found'], 404);
        }

        return response()->json([
            'id' => $station->id,
            'name' => $station->name,
            'latitude' => (float) $station->latitude,
            'longitude' => (float) $station->longitude,
        ]);
    }
}
