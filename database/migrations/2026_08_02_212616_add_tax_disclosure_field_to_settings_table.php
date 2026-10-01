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
        Schema::table('settings', function (Blueprint $table) {
            // "Percentual aproximado dos tributos federais" - the Lei da
            // Transparência Fiscal disclosure required in totTrib.pTotTrib.
            // For Simples Nacional this depends on the specific anexo/faixa
            // (revenue bracket), so it's not something to compute from the
            // other tax fields - the accountant provides it directly (seen
            // as 11.33 on the real, already-issued NFSe used as reference).
            $table->decimal('nfse_percentual_trib_federal', 5, 2)->nullable()->after('nfse_aliquota_cofins');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('nfse_percentual_trib_federal');
        });
    }
};
