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
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        // Drop children (pivots) before the tables they reference.
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Content (pages/blog/tags/categories) is gone for good - this app
        // isn't a CMS. Not worth recreating the full original schema just
        // to support rolling back; restore from a pre-migration backup if
        // you actually need that data back.
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable();
        });
    }
};
