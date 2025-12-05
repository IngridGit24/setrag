<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function originTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'origin_station_id');
    }

    public function destinationTrips(): HasMany
    {
        return $this->hasMany(Trip::class, 'destination_station_id');
    }
}
