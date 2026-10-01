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
        // Both columns were too narrow for real-world data (e.g. calling_code
        // "1-809, 1-829, 1-849" for the Dominican Republic, currency_symbol
        // "CA$" for some territories) - MySQL silently truncated on insert,
        // Postgres rejects it outright.
        Schema::table('countries', function (Blueprint $table) {
            $table->string('calling_code', 32)->nullable()->change();
            $table->string('currency_symbol', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('calling_code', 3)->nullable()->change();
            $table->string('currency_symbol', 3)->nullable()->change();
        });
    }
};
