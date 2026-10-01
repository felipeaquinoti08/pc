<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            // PDF gerado no momento da versão, guardado no blob (nunca em
            // disco local) - cada alteração salva vira uma versão nova, pra
            // sempre dar pra ver exatamente o que foi enviado/assinado em
            // cada ponto no tempo.
            $table->string('pdf_path');
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_versions');
    }
};
