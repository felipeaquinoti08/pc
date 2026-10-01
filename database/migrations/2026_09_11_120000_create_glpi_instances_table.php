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
        Schema::create('glpi_instances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->string('oauth_client_id');
            $table->text('oauth_client_secret'); // encrypted
            $table->string('username');
            $table->text('password'); // encrypted - técnica, para o grant OAuth2 "password"
            // Entidade GLPI (entities_id) padrão pra onde os ativos importados
            // dessa instância vão - 0 = raiz.
            $table->unsignedInteger('entities_id')->default(0);
            $table->integer('status')->default(1); // 1 = ativa, 0 = inativa
            $table->text('notes')->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('glpi_instances');
    }
};
