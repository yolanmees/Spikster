<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wordpress_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wordpress_installation_id')->constrained('wordpress_installations')->onDelete('cascade');
            $table->string('slug');
            $table->string('name');
            $table->string('version');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('theme_uri')->nullable();
            $table->string('author_uri')->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('template')->nullable(); // Parent theme for child themes
            $table->string('status')->default('inactive'); // active, inactive, broken
            $table->string('update_available')->nullable(); // New version if update available
            $table->json('requires')->nullable(); // PHP/WP version requirements
            $table->timestamp('last_checked')->nullable();
            $table->timestamps();
            
            $table->unique(['wordpress_installation_id', 'slug']);
            $table->index(['wordpress_installation_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wordpress_themes');
    }
};
