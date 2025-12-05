<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'pnr',
        'trip_id',
        'seat_no',
        'class',
        'amount',
        'base_price',
        'discount_amount',
        'commission',
        'currency',
        'status',
        'idempotency_key',
        'bill_id',
        'payment_status',
        'transaction_id',
        'paid_at',
        'payment_method',
        'user_id',
        'passenger_name',
        'passenger_email',
        'passenger_type',
        'passenger_birth_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'base_price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'commission' => 'decimal:2',
            'passenger_birth_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seat()
    {
        return $this->hasOne(Seat::class, 'trip_id', 'trip_id')
            ->where('seat_no', $this->seat_no);
    }
}
