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
        Schema::create('email_dkim_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('domain'); // Domain for DKIM
            $table->string('selector')->default('default'); // DKIM selector
            $table->text('private_key'); // RSA private key
            $table->text('public_key'); // RSA public key for DNS
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('site_id');
            $table->unique(['domain', 'selector']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_dkim_keys');
    }
};
