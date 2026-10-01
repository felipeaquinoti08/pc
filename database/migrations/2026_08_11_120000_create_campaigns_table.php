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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Public URL segment: /campanha/{slug}.
            $table->string('slug')->unique();
            $table->unsignedBigInteger('service_id');
            $table->text('description')->nullable();
            // How long an auto-issued trial license stays valid for.
            $table->integer('trial_days')->default(15);
            // Install slots granted per trial license (License::quantity).
            $table->integer('quantity')->default(1);
            $table->string('repository_url')->nullable();
            $table->text('instructions')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->integer('status')->default(1); // 1 = aceitando cadastros, 0 = encerrada
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
