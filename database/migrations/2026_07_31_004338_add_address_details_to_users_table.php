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
        Schema::table('users', function (Blueprint $table) {
            // City already exists as `location` - not duplicated here.
            $table->string('number')->nullable()->after('address');
            $table->boolean('no_number')->default(false)->after('number');
            $table->string('complement')->nullable()->after('no_number');
            $table->string('neighborhood')->nullable()->after('complement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['number', 'no_number', 'complement', 'neighborhood']);
        });
    }
};
