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
        Schema::create('wordpresses', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('username');
            $table->string('password');
            $table->foreignId('site_id')->constrained();
            $table->foreignId('database_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wordpresses');
    }
};
