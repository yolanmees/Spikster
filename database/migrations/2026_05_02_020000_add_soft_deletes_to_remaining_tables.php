<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'deployments', 'domains', 'ftp_users', 'cron_jobs',
            'aliases', 'webhooks', 'site_ssh_keys', 'databases',
            'database_users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes()->after('updated_at');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'deployments', 'domains', 'ftp_users', 'cron_jobs',
            'aliases', 'webhooks', 'site_ssh_keys', 'databases',
            'database_users',
        ];

        foreach ($tables as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
