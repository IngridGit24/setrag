<?php

namespace App\Http\Controllers;

use App\Models\TrainPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackController extends Controller
{
    public function index()
    {
        // Get latest position for each train
        $subquery = TrainPosition::select('train_id', \DB::raw('MAX(timestamp_utc) as max_timestamp'))
            ->groupBy('train_id');

        $positions = TrainPosition::select('train_positions.*')
            ->joinSub($subquery, 'latest', function ($join) {
                $join->on('train_positions.train_id', '=', 'latest.train_id')
                    ->on('train_positions.timestamp_utc', '=', 'latest.max_timestamp');
            })
            ->get();

        return view('track', compact('positions'));
    }
}

