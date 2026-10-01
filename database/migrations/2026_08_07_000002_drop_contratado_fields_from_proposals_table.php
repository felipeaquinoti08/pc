<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['contratado_nome', 'contratado_empresa', 'contratado_cnpj', 'contratado_responsavel']);
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->string('contratado_nome')->nullable();
            $table->string('contratado_empresa')->nullable();
            $table->string('contratado_cnpj')->nullable();
            $table->string('contratado_responsavel')->nullable();
        });
    }
};
