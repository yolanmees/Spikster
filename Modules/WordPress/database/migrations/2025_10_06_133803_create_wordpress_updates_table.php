<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wordpress_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wordpress_installation_id')->constrained('wordpress_installations')->onDelete('cascade');
            $table->enum('update_type', ['core', 'theme', 'plugin', 'translation']);
            $table->string('item_slug')->nullable(); // Theme/plugin slug
            $table->string('current_version');
            $table->string('new_version');
            $table->string('package_url')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->boolean('is_auto_update')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['wordpress_installation_id', 'status']);
            $table->index(['update_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wordpress_updates');
    }
};
