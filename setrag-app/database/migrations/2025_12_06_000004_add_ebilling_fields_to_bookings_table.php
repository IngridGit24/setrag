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
            $table->string('bill_id', 64)->nullable()->after('idempotency_key');
            $table->string('payment_status', 20)->default('pending')->after('status');
            // pending, paid, failed, cancelled, expired
            $table->string('transaction_id', 128)->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('transaction_id');
            $table->string('payment_method', 20)->nullable()->after('paid_at');
            // card, airtel, moov
            
            $table->index('bill_id');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['bill_id']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn(['bill_id', 'payment_status', 'transaction_id', 'paid_at', 'payment_method']);
        });
    }
};

