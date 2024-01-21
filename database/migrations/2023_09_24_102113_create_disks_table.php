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
        Schema::create('stats_disks', function (Blueprint $table) {
            $table->id();
            $table->float('time_since_update');
            $table->string('disk_name');
            $table->float('read_count');
            $table->float('write_count');
            $table->float('read_bytes', 10, 2);
            $table->float('write_bytes', 10, 2);
            $table->string('key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stats_disks');
    }
};