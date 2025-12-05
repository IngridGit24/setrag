<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Seat;
use App\Models\Trip;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        private PricingService $pricingService
    ) {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'passengers' => 'sometimes|integer|min:1|max:10',
            'idempotency_key' => 'sometimes|string|max:64',
            'passenger_name' => 'sometimes|string|max:255',
            'passenger_email' => 'sometimes|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check idempotency
        if ($request->has('idempotency_key')) {
            $existing = Booking::where('idempotency_key', $request->idempotency_key)->first();
            if ($existing) {
                return response()->json([
                    'pnr' => $existing->pnr,
                    'amount' => (float) $existing->amount,
                    'currency' => $existing->currency,
                ]);
            }
        }

        $trip = Trip::find($request->trip_id);
        $passengers = (int) ($request->input('passengers', 1));

        // Allocate a seat
        $seat = $this->allocateSeat($trip->id, 20); // 20 minutes hold

        if (!$seat) {
            return response()->json(['error' => 'No seats available'], 409);
        }

        // Get price quote
        $quote = $this->pricingService->getQuote($trip, $passengers);

        // Confirm the seat
        $this->confirmSeat($trip->id, $seat->seat_no);

        // Generate PNR (ULID-like format: 26 characters)
        $pnr = $this->generatePNR();

        // Create booking
        $booking = Booking::create([
            'pnr' => $pnr,
            'trip_id' => $trip->id,
            'seat_no' => $seat->seat_no,
            'amount' => $quote['total_price'],
            'currency' => $quote['currency'],
            'status' => 'CONFIRMED',
            'idempotency_key' => $request->idempotency_key,
            'user_id' => $request->user()?->id,
            'passenger_name' => $request->passenger_name,
            'passenger_email' => $request->passenger_email,
        ]);

        return response()->json([
            'pnr' => $booking->pnr,
            'amount' => (float) $booking->amount,
            'currency' => $booking->currency,
        ], 201);
    }

    private function allocateSeat(int $tripId, int $holdMinutes): ?Seat
    {
        $now = now();

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
            return null;
        }

        $seat->status = 'HELD';
        $seat->hold_expires_at = $now->addMinutes($holdMinutes);
        $seat->save();

        return $seat;
    }

    private function confirmSeat(int $tripId, string $seatNo): void
    {
        $seat = Seat::where('trip_id', $tripId)
            ->where('seat_no', $seatNo)
            ->first();

        if ($seat && in_array($seat->status, ['HELD', 'AVAILABLE'])) {
            $seat->status = 'SOLD';
            $seat->hold_expires_at = null;
            $seat->save();
        }
    }

    /**
     * Generate a PNR (Passenger Name Record) - ULID-like format
     * 26 characters: timestamp (10) + random (16)
     */
    private function generatePNR(): string
    {
        // Generate ULID-like identifier
        // Format: 10 chars timestamp + 16 chars random
        $timestamp = base_convert((string) (microtime(true) * 1000), 10, 36);
        $random = Str::random(16);
        
        // Ensure we have exactly 26 characters
        $pnr = str_pad($timestamp, 10, '0', STR_PAD_LEFT) . $random;
        $pnr = substr($pnr, 0, 26);
        
        // Ensure uniqueness
        while (Booking::where('pnr', $pnr)->exists()) {
            $random = Str::random(16);
            $pnr = str_pad($timestamp, 10, '0', STR_PAD_LEFT) . $random;
            $pnr = substr($pnr, 0, 26);
        }
        
        return strtoupper($pnr);
    }
}
