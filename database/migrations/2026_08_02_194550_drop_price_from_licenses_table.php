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
        // Billing now lives entirely in billing_policies/invoices - a
        // License is purely an entitlement/activation record (see
        // App\Models\License). Run only after any existing license price
        // has been migrated into a BillingPolicy.
        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('expires_at');
        });
    }
};
