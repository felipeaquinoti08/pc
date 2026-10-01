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
        // State is now free text, filled straight from the CEP lookup (see
        // resources/views/admin/customer/scripts.blade.php and
        // admin/user_profile/edit_profile.blade.php) instead of a
        // country-dependent <select> populated over AJAX.
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropColumn('state_id');
            $table->string('state')->nullable()->after('neighborhood');
        });

        Schema::table('users', function (Blueprint $table) {
            // No real FK constraint on users.state_id (commented out in the
            // original migration), so a plain dropColumn is enough here.
            $table->dropColumn('state_id');
            $table->string('state')->nullable()->after('neighborhood');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('state');
            $table->unsignedBigInteger('state_id')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('state');
            $table->unsignedBigInteger('state_id')->nullable();
        });
    }
};
