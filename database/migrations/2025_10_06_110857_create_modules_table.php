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
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('alias')->unique();
            $table->text('description')->nullable();
            $table->string('version', 20);
            $table->string('author')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('color', 20)->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_core')->default(false);
            $table->boolean('is_installed')->default(false);
            $table->timestamp('installed_at')->nullable();
            $table->unsignedBigInteger('installed_by')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('health_status', ['healthy', 'warning', 'error'])->default('healthy');
            $table->json('health_data')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();

            $table->foreign('installed_by')->references('id')->on('users')->onDelete('set null');
            $table->index('is_active');
            $table->index('category');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
