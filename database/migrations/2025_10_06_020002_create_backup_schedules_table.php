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
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('site_id');
            $table->string('name');
            $table->enum('type', ['full', 'incremental', 'database', 'files'])->default('full');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'custom']);
            $table->unsignedTinyInteger('hour')->default(2); // Hour of day (0-23)
            $table->unsignedTinyInteger('day_of_week')->nullable(); // 0-6, Sunday = 0
            $table->unsignedTinyInteger('day_of_month')->nullable(); // 1-31
            $table->string('cron_expression', 100)->nullable(); // For custom schedules
            $table->json('storage_locations'); // Array of storage location IDs
            $table->boolean('is_encrypted')->default(false);
            $table->unsignedInteger('retention_count')->default(7); // Keep last N backups
            $table->unsignedInteger('retention_days')->nullable(); // Or keep backups for N days
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();

            $table->foreign('site_id')->references('site_id')->on('sites')->cascadeOnDelete();

            $table->index(['site_id', 'is_active']);
            $table->index('next_run_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_schedules');
    }
};
