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
        Schema::create('stats_cpus', function (Blueprint $table) {
            $table->id();
            $table->decimal('total', 8, 2);
            $table->decimal('user', 8, 2)->nullable();
            $table->decimal('nice', 8, 2)->nullable();
            $table->decimal('system', 8, 2)->nullable();
            $table->decimal('idle', 8, 2)->nullable();
            $table->decimal('iowait', 8, 2)->nullable();
            $table->decimal('irq', 8, 2)->nullable();
            $table->decimal('steal', 8, 2)->nullable();
            $table->decimal('guest', 8, 2)->nullable();
            $table->decimal('guest_nice', 8, 2)->nullable();
            $table->decimal('time_since_update', 8, 2)->nullable();
            $table->integer('cpucore')->nullable();
            $table->integer('ctx_switches')->nullable();
            $table->integer('interrupts')->nullable();
            $table->integer('soft_interrupts')->nullable();
            $table->integer('syscalls')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stats_cpus');
    }
};
