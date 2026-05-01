<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->string('site_id');
            $table->unsignedBigInteger('server_id');
            $table->string('status'); // pending, running, success, failed
            $table->string('branch')->nullable();
            $table->string('commit_hash')->nullable();
            $table->text('commit_message')->nullable();
            $table->json('steps')->nullable();
            $table->text('error_message')->nullable();
            $table->text('output_log')->nullable();
            $table->string('triggered_by')->nullable(); // user, webhook, api
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('site_id');
            $table->index('status');
            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
