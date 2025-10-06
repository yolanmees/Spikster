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
        Schema::create('email_forwarders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('source'); // Can be specific email or @domain.com for catch-all
            $table->text('destination'); // Can be comma-separated for multiple destinations
            $table->boolean('keep_copy')->default(false); // Keep copy in source mailbox
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('site_id');
            $table->index(['site_id', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_forwarders');
    }
};
