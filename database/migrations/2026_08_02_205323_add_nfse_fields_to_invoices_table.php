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
            $table->string('nfse_status')->default('not_issued'); // not_issued, issued, error, cancelled
            $table->string('nfse_chave_acesso')->nullable();
            $table->string('nfse_numero')->nullable();
            $table->string('nfse_xml_path')->nullable(); // path on the azure blob disk
            $table->text('nfse_error_message')->nullable();
            $table->timestamp('nfse_issued_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'nfse_status', 'nfse_chave_acesso', 'nfse_numero',
                'nfse_xml_path', 'nfse_error_message', 'nfse_issued_at',
            ]);
        });
    }
};
