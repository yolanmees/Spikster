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
        Schema::create('server_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('server_id')->index();
            
            // CPU metrics
            $table->decimal('cpu_percent', 5, 2);
            $table->integer('cpu_cores')->nullable();
            
            // Memory metrics
            $table->bigInteger('memory_total');
            $table->bigInteger('memory_used');
            $table->bigInteger('memory_free');
            $table->bigInteger('memory_available')->nullable();
            $table->decimal('memory_percent', 5, 2);
            $table->bigInteger('memory_cached')->nullable();
            $table->bigInteger('memory_buffers')->nullable();
            
            // Disk metrics
            $table->bigInteger('disk_total');
            $table->bigInteger('disk_used');
            $table->bigInteger('disk_free');
            $table->decimal('disk_percent', 5, 2);
            
            // Load metrics
            $table->decimal('load_1', 8, 2);
            $table->decimal('load_5', 8, 2);
            $table->decimal('load_15', 8, 2);
            
            // Network metrics
            $table->bigInteger('network_bytes_sent');
            $table->bigInteger('network_bytes_recv');
            $table->bigInteger('network_packets_sent')->nullable();
            $table->bigInteger('network_packets_recv')->nullable();
            
            // System metrics
            $table->bigInteger('uptime_seconds');
            
            // Timestamps
            $table->timestamp('measured_at')->index();
            $table->timestamps();
            
            // Composite indexes for efficient querying
            $table->index(['server_id', 'measured_at']);
            $table->index(['server_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_metrics');
    }
};
