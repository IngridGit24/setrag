<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainPosition extends Model
{
    protected $fillable = [
        'train_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'bearing_deg',
        'timestamp_utc',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'speed_kmh' => 'decimal:2',
            'bearing_deg' => 'decimal:2',
            'timestamp_utc' => 'datetime',
        ];
    }
}
