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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // SSO (Microsoft Entra ID)
            $table->string('sso_tenant_id')->nullable();
            $table->string('sso_client_id')->nullable();
            $table->text('sso_client_secret')->nullable();

            // Armazenamento (Azure Blob Storage)
            $table->string('blob_account_name')->nullable();
            $table->text('blob_account_key')->nullable();
            $table->string('blob_container')->nullable();
            $table->string('blob_endpoint_suffix')->default('core.windows.net');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
