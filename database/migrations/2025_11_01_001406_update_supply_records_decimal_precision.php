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
        Schema::table('supply_records', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->change();
            $table->decimal('total_amount', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supply_records', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 4)->change();
            $table->decimal('total_amount', 15, 4)->change();
        });
    }
};
