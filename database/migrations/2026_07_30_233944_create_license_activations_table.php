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
        Schema::create('license_activations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_id');
            // Fingerprint the plugin generates once and reuses on every
            // check-in (e.g. a UUID it stores locally). One row per
            // distinct installation, counted against the license's quantity.
            $table->string('instance_identifier');
            $table->string('instance_url')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('status')->default('active'); // active, revoked (freed manually from the admin panel)
            $table->timestamp('activated_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->foreign('license_id')->references('id')->on('licenses')->onDelete('cascade');
            $table->unique(['license_id', 'instance_identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_activations');
    }
};
