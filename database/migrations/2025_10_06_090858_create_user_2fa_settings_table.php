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
        Schema::create('user_2fa_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->boolean('is_enabled')->default(false);
            $table->string('secret_key')->nullable(); // Encrypted TOTP secret - changed from text to string
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();

            $table->string('recovery_email')->nullable();
            $table->boolean('email_2fa_enabled')->default(false);

            // Security
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_2fa_settings');
    }
};
