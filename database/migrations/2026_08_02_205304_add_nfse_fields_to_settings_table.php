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
            // Environment + sequencing
            $table->string('nfse_environment')->default('homologacao'); // homologacao, producao
            $table->unsignedInteger('nfse_next_dps_number')->default(1);
            // 1-49999: aplicativo próprio/API. 50000-69999: emissor móvel.
            // 70000-79999: Emissor Web do governo - nunca usar essa faixa
            // aqui (rejeição E0010, "série não pertence à faixa definida
            // para o tipo de emissor").
            $table->string('nfse_dps_serie')->default('1');

            // Prestador (emit/DPS.prest) - mirrors the fields seen on the
            // real NFSe XML sample (emit/enderNac, DPS.infDPS.prest).
            $table->string('nfse_cnpj')->nullable();
            $table->string('nfse_inscricao_municipal')->nullable();
            $table->unsignedTinyInteger('nfse_op_simples_nacional')->nullable(); // opSimpNac: 1,2,3
            $table->unsignedTinyInteger('nfse_reg_esp_trib')->nullable(); // regEspTrib
            $table->string('nfse_telefone')->nullable();
            $table->string('nfse_email')->nullable();
            $table->string('nfse_municipio_codigo')->nullable(); // cMun/cLocEmi, ex: 3304557
            $table->string('nfse_municipio_nome')->nullable();
            $table->string('nfse_uf', 2)->nullable();
            $table->string('nfse_cep')->nullable();
            $table->string('nfse_logradouro')->nullable();
            $table->string('nfse_numero')->nullable();
            $table->string('nfse_bairro')->nullable();

            // Serviço (DPS.infDPS.serv/valores)
            $table->string('nfse_codigo_tributacao_nacional')->nullable(); // cTribNac
            $table->string('nfse_codigo_tributacao_municipal')->nullable(); // cTribMun
            $table->string('nfse_codigo_nbs')->nullable();
            $table->decimal('nfse_aliquota_iss', 5, 2)->nullable();
            $table->decimal('nfse_aliquota_pis', 5, 2)->nullable();
            $table->decimal('nfse_aliquota_cofins', 5, 2)->nullable();

            // Certificado A1 - o arquivo .pfx em si fica no Azure Blob
            // (Storage::disk('azure')), aqui só o caminho (não sensível) e
            // a senha (sensível, encrypted no model).
            $table->string('nfse_certificate_path')->nullable();
            $table->text('nfse_certificate_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'nfse_environment', 'nfse_next_dps_number', 'nfse_dps_serie',
                'nfse_cnpj', 'nfse_inscricao_municipal', 'nfse_op_simples_nacional', 'nfse_reg_esp_trib',
                'nfse_telefone', 'nfse_email', 'nfse_municipio_codigo', 'nfse_municipio_nome', 'nfse_uf',
                'nfse_cep', 'nfse_logradouro', 'nfse_numero', 'nfse_bairro',
                'nfse_codigo_tributacao_nacional', 'nfse_codigo_tributacao_municipal', 'nfse_codigo_nbs',
                'nfse_aliquota_iss', 'nfse_aliquota_pis', 'nfse_aliquota_cofins',
                'nfse_certificate_path', 'nfse_certificate_password',
            ]);
        });
    }
};
