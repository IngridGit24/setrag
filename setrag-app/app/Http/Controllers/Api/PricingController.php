<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PricingController extends Controller
{
    public function __construct(
        private PricingService $pricingService
    ) {
    }

    public function quote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'seat_no' => 'sometimes|string',
            'passengers' => 'sometimes|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $trip = Trip::find($request->trip_id);
        $passengers = (int) ($request->input('passengers', 1));

        $quote = $this->pricingService->getQuote($trip, $passengers);

        return response()->json($quote);
    }
}
