<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            // Mesmos valores de BillingPolicy::frequencies() - once/monthly/
            // semiannual/annual - pra um plano aqui poder pré-preencher
            // frequência+valor direto numa Política de Faturamento.
            $table->string('frequency');
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['service_id', 'frequency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_pricing_plans');
    }
};
