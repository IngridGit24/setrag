<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    public function listSeats(string $tripId)
    {
        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['error' => 'Trip not found'], 404);
        }

        $seats = Seat::where('trip_id', $tripId)
            ->orderBy('id')
            ->get();

        return response()->json($seats->map(function ($seat) {
            return [
                'seat_no' => $seat->seat_no,
                'status' => $seat->status,
                'hold_expires_at' => $seat->hold_expires_at?->toIso8601String(),
            ];
        }));
    }

    public function seedSeats(Request $request, string $tripId)
    {
        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['error' => 'Trip not found'], 404);
        }

        $count = (int) ($request->input('count', 100));

        // Check if seats already exist
        $existingCount = Seat::where('trip_id', $tripId)->count();

        if ($existingCount > 0) {
            // Return existing seats
            $seats = Seat::where('trip_id', $tripId)
                ->orderBy('id')
                ->get();

            return response()->json($seats->map(function ($seat) {
                return [
                    'seat_no' => $seat->seat_no,
                    'status' => $seat->status,
                    'hold_expires_at' => $seat->hold_expires_at?->toIso8601String(),
                ];
            }));
        }

        // Create new seats
        $seats = [];
        for ($i = 1; $i <= $count; $i++) {
            $seats[] = [
                'trip_id' => $tripId,
                'seat_no' => "{$i}A",
                'status' => 'AVAILABLE',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Seat::insert($seats);

        $createdSeats = Seat::where('trip_id', $tripId)
            ->orderBy('id')
            ->get();

        return response()->json($createdSeats->map(function ($seat) {
            return [
                'seat_no' => $seat->seat_no,
                'status' => $seat->status,
                'hold_expires_at' => $seat->hold_expires_at?->toIso8601String(),
            ];
        }));
    }

    public function allocateSeat(Request $request, string $tripId)
    {
        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['error' => 'Trip not found'], 404);
        }

        $holdMinutes = (int) ($request->input('hold_minutes', 15));
        $now = now();

        // Find an available or expired held seat
        $seat = Seat::where('trip_id', $tripId)
            ->where(function ($query) use ($now) {
                $query->where('status', 'AVAILABLE')
                    ->orWhere(function ($q) use ($now) {
                        $q->where('status', 'HELD')
                            ->where(function ($q2) use ($now) {
                                $q2->whereNull('hold_expires_at')
                                    ->orWhere('hold_expires_at', '<', $now);
                            });
                    });
            })
            ->orderBy('id')
            ->first();

        if (!$seat) {
            return response()->json(['error' => 'No seats available'], 409);
        }

        $seat->status = 'HELD';
        $seat->hold_expires_at = $now->addMinutes($holdMinutes);
        $seat->save();

        return response()->json([
            'seat_no' => $seat->seat_no,
            'status' => $seat->status,
            'hold_expires_at' => $seat->hold_expires_at->toIso8601String(),
        ]);
    }

    public function confirmSeat(string $tripId, string $seatNo)
    {
        $trip = Trip::find($tripId);

        if (!$trip) {
            return response()->json(['error' => 'Trip not found'], 404);
        }

        $seat = Seat::where('trip_id', $tripId)
            ->where('seat_no', $seatNo)
            ->first();

        if (!$seat) {
            return response()->json(['error' => 'Seat not found'], 404);
        }

        if (!in_array($seat->status, ['HELD', 'AVAILABLE'])) {
            return response()->json(['error' => 'Seat not confirmable'], 409);
        }

        $seat->status = 'SOLD';
        $seat->hold_expires_at = null;
        $seat->save();

        return response()->json([
            'seat_no' => $seat->seat_no,
            'status' => $seat->status,
        ]);
    }
}
