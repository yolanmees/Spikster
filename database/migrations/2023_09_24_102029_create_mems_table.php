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
        Schema::create('stats_mems', function (Blueprint $table) {
            $table->id();
            $table->float('total', 12, 2);
            $table->float('available', 12, 2);
            $table->float('percent', 12, 2);
            $table->float('used', 12, 2);
            $table->float('free', 12, 2);
            $table->float('active', 12, 2);
            $table->float('inactive', 12, 2);
            $table->float('buffers', 12, 2);
            $table->float('cached', 12, 2);
            $table->float('shared', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stats_mems');
    }
};