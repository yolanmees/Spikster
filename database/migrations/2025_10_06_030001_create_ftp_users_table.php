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
        Schema::create('ftp_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('site_id');
            $table->foreign('site_id')->references('site_id')->on('sites')->onDelete('cascade');
            $table->string('server_id');
            $table->foreign('server_id')->references('server_id')->on('servers')->onDelete('cascade');

            // FTP User Details
            $table->string('username')->unique(); // user@domain.com format
            $table->string('password'); // Encrypted with bcrypt
            $table->string('home_directory'); // Chroot path: /var/www/vhosts/domain.com/httpdocs

            // Quota & Limits
            $table->unsignedInteger('quota_mb')->default(1024); // 1GB default
            $table->unsignedInteger('max_connections')->default(5);
            $table->unsignedInteger('bandwidth_limit_kbps')->nullable(); // KB/s upload/download limit

            // Permissions (JSON)
            $table->json('permissions')->nullable();

            // Status & Security
            $table->boolean('is_active')->default(true);
            $table->boolean('require_ssl')->default(true); // Force FTPS
            $table->string('allowed_ip')->nullable(); // IP whitelist (single IP or CIDR)

            // Usage Tracking
            $table->unsignedBigInteger('current_usage_bytes')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->unsignedInteger('failed_login_count')->default(0);
            $table->timestamp('locked_until')->nullable(); // Account lockout expiration

            // Metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('site_id');
            $table->index('server_id');
            $table->index('is_active');
            $table->index('username');
            $table->index(['site_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ftp_users');
    }
};
