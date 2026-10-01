<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('document_label');
            $table->unsignedSmallInteger('proposal_year');
            $table->text('about_text')->nullable();

            $table->decimal('total_value', 12, 2);
            $table->decimal('down_payment_percent', 5, 2)->default(40);
            $table->text('down_payment_note')->nullable();
            $table->decimal('final_payment_percent', 5, 2)->default(60);
            $table->text('final_payment_note')->nullable();
            $table->string('estimated_days')->nullable();

            // Contratante já vem do Customer (nome/CNPJ) - só o nome do
            // responsável pela assinatura fica aqui, já que Customer não
            // rastreia um contato específico por proposta.
            $table->string('contratante_responsavel')->nullable();

            // Contratado (CCITI) - snapshot editável por proposta, não
            // vinculado a Configurações, pra um contrato já assinado nunca
            // mudar de conteúdo se os dados da empresa mudarem depois.
            $table->string('contratado_nome')->nullable();
            $table->string('contratado_empresa')->nullable();
            $table->string('contratado_cnpj')->nullable();
            $table->string('contratado_responsavel')->nullable();

            $table->text('object_intro')->nullable();
            $table->text('exclusions_text')->nullable();
            $table->text('prerequisites_intro')->nullable();
            $table->text('schedule_note')->nullable();
            $table->text('payment_intro')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_agency')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_beneficiary')->nullable();
            $table->string('bank_pix')->nullable();

            $table->text('confidentiality_text')->nullable();

            $table->string('status')->default('draft'); // draft, sent, accepted, rejected

            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
