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
        Schema::table('customers', function (Blueprint $table) {
            // Código IBGE (7 dígitos) do município do endereço - já temos
            // city/state como texto livre, mas a DPS da NFS-e exige o
            // código oficial em toma.end.endNac.cMun. Sem isso o endereço
            // do tomador não é enviado (visto num envio real que o exige).
            $table->string('nfse_municipio_codigo', 10)->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('nfse_municipio_codigo');
        });
    }
};
