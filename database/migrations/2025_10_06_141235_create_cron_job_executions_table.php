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
        Schema::create('cron_job_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cron_job_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('duration')->nullable(); // Duration in seconds
            $table->enum('status', ['running', 'success', 'failed', 'timeout'])->default('running');
            $table->integer('exit_code')->nullable();
            $table->text('output')->nullable();
            $table->text('error_output')->nullable();
            $table->timestamps();
            
            $table->index(['cron_job_id', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_job_executions');
    }
};
