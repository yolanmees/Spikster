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
        Schema::create('module_marketplace', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('package_name')->unique();
            $table->string('display_name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('author');
            $table->string('author_url')->nullable();
            $table->string('version');
            $table->string('minimum_core_version')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->enum('license_type', ['free', 'paid', 'freemium', 'open-source'])->default('free');
            $table->string('repository_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->integer('downloads')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('rating_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('screenshots')->nullable();
            $table->json('tags')->nullable();
            $table->json('compatibility')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'is_verified']);
            $table->index(['license_type', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_marketplace');
    }
};
