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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('class', 20)->default('second_class')->after('seat_no');
            // VIP, first_class, second_class
            $table->string('passenger_type', 20)->default('adult')->after('passenger_email');
            // adult, student, senior, child
            $table->date('passenger_birth_date')->nullable()->after('passenger_type');
            $table->decimal('base_price', 10, 2)->after('amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('base_price');
            $table->decimal('commission', 10, 2)->default(0)->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['class', 'passenger_type', 'passenger_birth_date', 'base_price', 'discount_amount', 'commission']);
        });
    }
};

