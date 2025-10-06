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
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('device_token')->unique(); // UUID
            $table->string('device_name')->nullable(); // User agent parsed
            $table->string('device_type')->nullable(); // Desktop, Mobile, Tablet
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            $table->string('ip_address');

            $table->timestamp('last_used_at');
            $table->timestamp('expires_at'); // 30 days from last use

            $table->timestamps();

            $table->index('user_id');
            $table->index('device_token');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
