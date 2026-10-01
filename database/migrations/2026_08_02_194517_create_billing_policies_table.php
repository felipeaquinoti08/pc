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
        Schema::create('billing_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            // Optional: what this policy bills for. A License (recurring
            // charge for an entitlement) or a Service (recurring/one-off
            // charge not tied to any license). Null billable = free-form
            // charge, described by `description`.
            $table->nullableMorphs('billable');
            $table->string('description')->nullable();
            $table->string('frequency'); // once, monthly, semiannual, annual
            $table->decimal('amount', 10, 2);
            $table->unsignedTinyInteger('due_day'); // day of month, 1-28
            $table->date('starts_at');
            $table->string('status')->default('active'); // active, paused, cancelled
            $table->text('notes')->nullable();
            $table->bigInteger('created_by_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_policies');
    }
};
