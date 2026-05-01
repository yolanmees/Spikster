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
        Schema::create('cron_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->onDelete('cascade');
            $table->string('command');
            $table->string('schedule'); // Cron expression (e.g., "0 * * * *")
            $table->string('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->string('output_file')->nullable(); // Log output path
            $table->boolean('notify_on_error')->default(false);
            $table->timestamps();

            $table->index(['server_id', 'enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_jobs');
    }
};
