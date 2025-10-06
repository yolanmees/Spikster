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
        Schema::create('backups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('site_id');
            $table->uuid('server_id');
            $table->uuid('backup_schedule_id')->nullable();
            $table->enum('type', ['full', 'incremental', 'database', 'files'])->default('full');
            $table->string('filename');
            $table->text('filepath');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedBigInteger('compressed_size_bytes')->default(0);
            $table->boolean('is_encrypted')->default(false);
            $table->string('encryption_method', 50)->nullable();
            $table->string('storage_location', 100)->default('local');
            $table->text('remote_path')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'deleted'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('includes_database')->default(true);
            $table->boolean('includes_files')->default(true);
            $table->unsignedBigInteger('database_dump_size')->nullable();
            $table->unsignedInteger('files_count')->nullable();
            $table->string('checksum', 64)->nullable(); // SHA-256 hash
            $table->json('metadata')->nullable(); // Additional backup metadata
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('site_id')->references('site_id')->on('sites')->cascadeOnDelete();
            $table->foreign('server_id')->references('server_id')->on('servers')->cascadeOnDelete();
            $table->foreign('backup_schedule_id')->references('id')->on('backup_schedules')->nullOnDelete();

            $table->index(['site_id', 'status']);
            $table->index('created_at');
            $table->index('storage_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
