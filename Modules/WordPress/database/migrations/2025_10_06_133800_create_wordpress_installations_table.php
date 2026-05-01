<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wordpress_installations', function (Blueprint $table) {
            $table->id();
            $table->string('site_id');
            $table->string('path');
            $table->string('url')->nullable();
            $table->string('version')->nullable();
            $table->string('admin_username');
            $table->text('admin_password')->nullable(); // Encrypted
            $table->foreignId('database_id')->constrained()->onDelete('cascade');
            $table->foreignId('database_user_id')->constrained()->onDelete('cascade');
            $table->string('locale')->default('en_US');
            $table->string('status')->default('active'); // active, staging, inactive
            $table->boolean('auto_update')->default(false);
            $table->boolean('is_multisite')->default(false);
            $table->json('wp_config')->nullable(); // Additional wp-config.php settings
            $table->timestamp('last_update_check')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wordpress_installations');
    }
};
