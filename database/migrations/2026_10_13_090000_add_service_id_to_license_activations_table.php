<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('license_activations', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('license_id')->constrained()->cascadeOnDelete();
        });

        // Backfill: toda ativação existente é de antes do licenciamento em
        // pacote, quando cada licença só cobria um serviço - usa esse único
        // serviço do pivot. Daqui pra frente o controller de validação
        // sempre grava o service_id certo.
        foreach (DB::table('license_activations')->whereNull('service_id')->get() as $activation) {
            $serviceId = DB::table('license_service')->where('license_id', $activation->license_id)->value('service_id');

            if ($serviceId) {
                DB::table('license_activations')->where('id', $activation->id)->update(['service_id' => $serviceId]);
            }
        }

        // O limite de "quantity" passa a valer por serviço dentro do
        // pacote, não mais compartilhado entre todos - uma mesma instância
        // pode validar vários plugins do mesmo pacote sem brigar por vaga.
        Schema::table('license_activations', function (Blueprint $table) {
            $table->dropUnique('license_activations_license_id_instance_identifier_unique');
            $table->unique(['license_id', 'service_id', 'instance_identifier'], 'license_activations_license_service_instance_unique');
        });
    }

    public function down(): void
    {
        Schema::table('license_activations', function (Blueprint $table) {
            $table->dropUnique('license_activations_license_service_instance_unique');
            $table->unique(['license_id', 'instance_identifier'], 'license_activations_license_id_instance_identifier_unique');
            $table->dropConstrainedForeignId('service_id');
        });
    }
};
