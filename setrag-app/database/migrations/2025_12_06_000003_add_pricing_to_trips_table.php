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
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('price_second_class', 10, 2)->nullable()->after('arrival_time');
            $table->decimal('price_first_class', 10, 2)->nullable()->after('price_second_class');
            $table->decimal('price_vip', 10, 2)->nullable()->after('price_first_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['price_second_class', 'price_first_class', 'price_vip']);
        });
    }
};

