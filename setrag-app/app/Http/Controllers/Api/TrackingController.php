<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrainPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    /**
     * Get all latest positions (one per train)
     */
    public function index()
    {
        // Get the latest position for each train
        $positions = TrainPosition::select('train_positions.*')
            ->join(
                \DB::raw('(SELECT train_id, MAX(timestamp_utc) as max_timestamp 
                    FROM train_positions 
                    GROUP BY train_id) as latest'),
                function ($join) {
                    $join->on('train_positions.train_id', '=', 'latest.train_id')
                        ->on('train_positions.timestamp_utc', '=', 'latest.max_timestamp');
                }
            )
            ->get();

        return response()->json($positions->map(function ($position) {
            return [
                'train_id' => $position->train_id,
                'latitude' => (float) $position->latitude,
                'longitude' => (float) $position->longitude,
                'speed_kmh' => $position->speed_kmh ? (float) $position->speed_kmh : null,
                'bearing_deg' => $position->bearing_deg ? (float) $position->bearing_deg : null,
                'timestamp_utc' => $position->timestamp_utc->toIso8601String(),
            ];
        }));
    }

    /**
     * Store a single position
     */
    public function storeSingle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'train_id' => 'required|string|max:64',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed_kmh' => 'sometimes|numeric|min:0',
            'bearing_deg' => 'sometimes|numeric|between:0,360',
            'timestamp_utc' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $position = TrainPosition::create([
            'train_id' => $request->train_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed_kmh' => $request->speed_kmh,
            'bearing_deg' => $request->bearing_deg,
            'timestamp_utc' => $request->timestamp_utc ?? now(),
        ]);

        return response()->json([
            'train_id' => $position->train_id,
            'latitude' => (float) $position->latitude,
            'longitude' => (float) $position->longitude,
            'speed_kmh' => $position->speed_kmh ? (float) $position->speed_kmh : null,
            'bearing_deg' => $position->bearing_deg ? (float) $position->bearing_deg : null,
            'timestamp_utc' => $position->timestamp_utc->toIso8601String(),
        ]);
    }

    /**
     * Store multiple positions
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'positions' => 'required|array',
            'positions.*.train_id' => 'required|string|max:64',
            'positions.*.latitude' => 'required|numeric|between:-90,90',
            'positions.*.longitude' => 'required|numeric|between:-180,180',
            'positions.*.speed_kmh' => 'sometimes|numeric|min:0',
            'positions.*.bearing_deg' => 'sometimes|numeric|between:0,360',
            'positions.*.timestamp_utc' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $positions = [];
        foreach ($request->positions as $posData) {
            $position = TrainPosition::create([
                'train_id' => $posData['train_id'],
                'latitude' => $posData['latitude'],
                'longitude' => $posData['longitude'],
                'speed_kmh' => $posData['speed_kmh'] ?? null,
                'bearing_deg' => $posData['bearing_deg'] ?? null,
                'timestamp_utc' => $posData['timestamp_utc'] ?? now(),
            ]);

            $positions[] = [
                'train_id' => $position->train_id,
                'latitude' => (float) $position->latitude,
                'longitude' => (float) $position->longitude,
                'speed_kmh' => $position->speed_kmh ? (float) $position->speed_kmh : null,
                'bearing_deg' => $position->bearing_deg ? (float) $position->bearing_deg : null,
                'timestamp_utc' => $position->timestamp_utc->toIso8601String(),
            ];
        }

        return response()->json($positions);
    }

    /**
     * Search positions with filters
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'train_id' => 'sometimes|string',
            'since' => 'sometimes|date',
            'until' => 'sometimes|date|after_or_equal:since',
            'limit' => 'sometimes|integer|min:1|max:1000',
            'offset' => 'sometimes|integer|min:0',
            'order' => 'sometimes|in:asc,desc',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $query = TrainPosition::query();

        if ($request->has('train_id')) {
            $query->where('train_id', $request->train_id);
        }

        if ($request->has('since')) {
            $query->where('timestamp_utc', '>=', $request->since);
        }

        if ($request->has('until')) {
            $query->where('timestamp_utc', '<=', $request->until);
        }

        $order = $request->input('order', 'desc');
        $query->orderBy('timestamp_utc', $order);

        $limit = (int) ($request->input('limit', 100));
        $offset = (int) ($request->input('offset', 0));
        $query->offset($offset)->limit($limit);

        $positions = $query->get();

        return response()->json($positions->map(function ($position) {
            return [
                'train_id' => $position->train_id,
                'latitude' => (float) $position->latitude,
                'longitude' => (float) $position->longitude,
                'speed_kmh' => $position->speed_kmh ? (float) $position->speed_kmh : null,
                'bearing_deg' => $position->bearing_deg ? (float) $position->bearing_deg : null,
                'timestamp_utc' => $position->timestamp_utc->toIso8601String(),
            ];
        }));
    }

    /**
     * Get the latest position for a specific train
     */
    public function getTrainPosition(string $trainId)
    {
        $position = TrainPosition::where('train_id', $trainId)
            ->orderBy('timestamp_utc', 'desc')
            ->first();

        if (!$position) {
            return response()->json(['error' => 'Train not found'], 404);
        }

        return response()->json([
            'train_id' => $position->train_id,
            'latitude' => (float) $position->latitude,
            'longitude' => (float) $position->longitude,
            'speed_kmh' => $position->speed_kmh ? (float) $position->speed_kmh : null,
            'bearing_deg' => $position->bearing_deg ? (float) $position->bearing_deg : null,
            'timestamp_utc' => $position->timestamp_utc->toIso8601String(),
        ]);
    }
}
