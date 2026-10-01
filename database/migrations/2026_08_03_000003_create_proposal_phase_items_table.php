<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_phase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_phase_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_phase_items');
    }
};
