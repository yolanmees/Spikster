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
            $table->float('total');
            $table->float('available');
            $table->float('percent');
            $table->float('used');
            $table->float('free');
            $table->float('active');
            $table->float('inactive');
            $table->float('buffers');
            $table->float('cached');
            $table->float('shared');
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