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
        Schema::create('train_positions', function (Blueprint $table) {
            $table->id();
            $table->string('train_id', 64);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('speed_kmh', 8, 2)->nullable();
            $table->decimal('bearing_deg', 6, 2)->nullable();
            $table->dateTime('timestamp_utc');
            $table->timestamps();
            
            $table->index('train_id');
            $table->index('timestamp_utc');
            $table->index(['train_id', 'timestamp_utc']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('train_positions');
    }
};
