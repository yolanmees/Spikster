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
            $table->float('total');
            $table->float('user');
            $table->float('nice');
            $table->float('system');
            $table->float('idle');
            $table->float('iowait');
            $table->float('irq');
            $table->float('softirq');
            $table->float('steal');
            $table->float('guest');
            $table->float('guest_nice');
            $table->float('time_since_update');
            $table->integer('cpucore');
            $table->integer('ctx_switches');
            $table->integer('interrupts');
            $table->integer('soft_interrupts');
            $table->integer('syscalls');
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