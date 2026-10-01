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
        Schema::table('invoices', function (Blueprint $table) {
            // Set from the webhook's own `live_mode` field when a Mercado
            // Pago notification confirms payment - lets a test checkout
            // (sandbox card, no real money) be told apart from a real one.
            $table->boolean('is_test_payment')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('is_test_payment');
        });
    }
};
