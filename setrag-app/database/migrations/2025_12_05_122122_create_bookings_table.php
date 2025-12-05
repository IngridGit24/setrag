<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('pnr', 26)->unique();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->string('seat_no', 16);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 8)->default('XAF');
            $table->string('status', 16)->default('CONFIRMED'); // PENDING, CONFIRMED, CANCELLED
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('passenger_name')->nullable();
            $table->string('passenger_email')->nullable();
            $table->timestamps();
            
            $table->index('pnr');
            $table->index('trip_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
