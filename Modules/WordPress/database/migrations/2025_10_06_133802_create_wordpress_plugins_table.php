<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wordpress_plugins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wordpress_installation_id')->constrained('wordpress_installations')->onDelete('cascade');
            $table->string('slug');
            $table->string('name');
            $table->string('version');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->string('plugin_uri')->nullable();
            $table->string('author_uri')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_network_active')->default(false);
            $table->string('status')->default('inactive'); // active, inactive, must-use, dropin, broken
            $table->string('update_available')->nullable();
            $table->json('requires')->nullable(); // PHP/WP version requirements
            $table->boolean('auto_update')->default(false);
            $table->timestamp('last_checked')->nullable();
            $table->timestamps();

            $table->unique(['wordpress_installation_id', 'slug']);
            $table->index(['wordpress_installation_id', 'is_active']);
            $table->index(['wordpress_installation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wordpress_plugins');
    }
};
