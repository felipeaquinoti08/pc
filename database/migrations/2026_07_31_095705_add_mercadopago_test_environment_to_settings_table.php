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
            // Which credential set attachPreference() uses for NEW payment
            // links. Existing mp_public_key/mp_access_token/mp_webhook_secret
            // columns are treated as the "production" set from here on.
            $table->string('mp_environment')->default('test');
            $table->string('mp_test_public_key')->nullable();
            $table->text('mp_test_access_token')->nullable();
            $table->text('mp_test_webhook_secret')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['mp_environment', 'mp_test_public_key', 'mp_test_access_token', 'mp_test_webhook_secret']);
        });
    }
};
