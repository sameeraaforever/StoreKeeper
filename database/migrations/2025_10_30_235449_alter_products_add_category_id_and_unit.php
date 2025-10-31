<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // add category_id (nullable to avoid failing on existing rows) and unit
            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete()->after('name');
            }
            if (!Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->nullable()->after('category_id');
            }

            // if legacy 'category' string column exists, drop it
            if (Schema::hasColumn('products', 'category')) {
                $table->dropColumn('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add back old category string (nullable) and drop the new fields
            if (!Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable()->after('name');
            }
            if (Schema::hasColumn('products', 'unit')) {
                $table->dropColumn('unit');
            }
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};
