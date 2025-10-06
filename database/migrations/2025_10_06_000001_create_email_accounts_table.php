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
        Schema::create('email_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('email')->unique();
            $table->string('password'); // Hashed password
            $table->integer('quota_mb')->default(1024); // Mailbox quota in MB, 0 = unlimited
            $table->boolean('spam_filter')->default(true);
            $table->decimal('spam_score', 3, 1)->default(5.0); // SpamAssassin threshold
            $table->boolean('antivirus')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('site_id');
            $table->index('email');
            $table->index(['site_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_accounts');
    }
};
