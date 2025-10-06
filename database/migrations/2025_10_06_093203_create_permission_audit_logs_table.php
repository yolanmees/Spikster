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
        Schema::create('permission_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('action'); // role_assigned, role_revoked, permission_granted, permission_revoked, site_access_granted, site_access_revoked
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->foreignId('permission_id')->nullable()->constrained('permissions')->onDelete('set null');

            $table->json('metadata')->nullable();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at');

            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index('target_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_audit_logs');
    }
};
