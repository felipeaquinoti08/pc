<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['down_payment_note', 'final_payment_note', 'payment_intro']);
        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->text('down_payment_note')->nullable();
            $table->text('final_payment_note')->nullable();
            $table->text('payment_intro')->nullable();
        });
    }
};
