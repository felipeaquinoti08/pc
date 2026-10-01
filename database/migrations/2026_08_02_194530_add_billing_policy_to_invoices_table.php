<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('license_id')
                ->constrained('customers')->nullOnDelete();
            $table->foreignId('billing_policy_id')->nullable()->after('customer_id')
                ->constrained('billing_policies')->nullOnDelete();
        });

        // Backfill customer_id from the license this invoice already has,
        // before the column is put to real use.
        DB::statement('
            UPDATE invoices
            SET customer_id = licenses.customer_id
            FROM licenses
            WHERE invoices.license_id = licenses.id
        ');

        // license_id stops being mandatory - an invoice can now bill a
        // Service or a free-form charge with no license involved at all.
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['license_id', 'reference_month', 'reference_year']);
        });

        DB::statement('ALTER TABLE invoices ALTER COLUMN license_id DROP NOT NULL');

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['billing_policy_id', 'reference_month', 'reference_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['billing_policy_id', 'reference_month', 'reference_year']);
        });

        DB::statement('ALTER TABLE invoices ALTER COLUMN license_id SET NOT NULL');

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['license_id', 'reference_month', 'reference_year']);
            $table->dropConstrainedForeignId('billing_policy_id');
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};
