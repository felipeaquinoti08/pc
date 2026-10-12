<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
        });

        // Backfill dos serviços já cadastrados - identificador fixo que os
        // plugins GLPI vão gravar no próprio código pra se identificar na
        // validação de licença (ver LicenseValidationController).
        foreach (DB::table('services')->whereNull('slug')->get() as $service) {
            $base = Str::slug($service->name) ?: 'servico';
            $slug = $base;
            $i = 1;

            while (DB::table('services')->where('slug', $slug)->where('id', '!=', $service->id)->exists()) {
                $slug = $base . '-' . (++$i);
            }

            DB::table('services')->where('id', $service->id)->update(['slug' => $slug]);
        }

        // Evita Table::change() (exige doctrine/dbal, não instalado aqui) -
        // SQL direto pra tornar a coluna obrigatória e única.
        DB::statement('ALTER TABLE services ALTER COLUMN slug SET NOT NULL');
        Schema::table('services', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
