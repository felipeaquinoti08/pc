<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unique(['license_id', 'service_id']);
        });

        // Uma licença passa a poder cobrir vários serviços (pacote com o
        // mesmo serial) - migra o service_id único que já existia pra uma
        // linha no pivot, antes de remover a coluna.
        foreach (DB::table('licenses')->whereNotNull('service_id')->get(['id', 'service_id']) as $license) {
            DB::table('license_service')->insert([
                'license_id' => $license->id,
                'service_id' => $license->service_id,
            ]);
        }

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
        });

        foreach (DB::table('license_service')->get() as $row) {
            DB::table('licenses')->where('id', $row->license_id)->update(['service_id' => $row->service_id]);
        }

        Schema::dropIfExists('license_service');
    }
};
