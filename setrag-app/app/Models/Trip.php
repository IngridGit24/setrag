<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    protected $fillable = [
        'origin_station_id',
        'destination_station_id',
        'departure_time',
        'arrival_time',
        'price_second_class',
        'price_first_class',
        'price_vip',
    ];

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'arrival_time' => 'datetime',
            'price_second_class' => 'decimal:2',
            'price_first_class' => 'decimal:2',
            'price_vip' => 'decimal:2',
        ];
    }

    public function originStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'origin_station_id');
    }

    public function destinationStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'destination_station_id');
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
