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
        Schema::create('user_site_access', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained()->onDelete('cascade');

            $table->enum('access_level', ['full', 'limited', 'readonly'])->default('readonly');
            $table->foreignId('granted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('granted_at');
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('site_id');
            $table->unique(['user_id', 'site_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_site_access');
    }
};
