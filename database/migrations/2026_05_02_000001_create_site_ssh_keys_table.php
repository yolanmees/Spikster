<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_ssh_keys', function (Blueprint $table) {
            $table->id();
            $table->string('site_id', 64)->index();
            $table->string('label', 128);
            $table->text('public_key');
            $table->string('fingerprint', 128)->index();
            $table->timestamps();

            $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
            $table->unique(['site_id', 'fingerprint']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_ssh_keys');
    }
};
