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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->string('seat_no', 16);
            $table->string('status', 16)->default('AVAILABLE'); // AVAILABLE, HELD, SOLD
            $table->dateTime('hold_expires_at')->nullable();
            $table->timestamps();
            
            $table->index('trip_id');
            $table->index(['trip_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
