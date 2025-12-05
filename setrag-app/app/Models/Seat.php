<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    protected $fillable = [
        'trip_id',
        'seat_no',
        'class',
        'status',
        'hold_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'hold_expires_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function isAvailable(): bool
    {
        if ($this->status === 'AVAILABLE') {
            return true;
        }

        if ($this->status === 'HELD' && $this->hold_expires_at && $this->hold_expires_at->isPast()) {
            return true;
        }

        return false;
    }
}
