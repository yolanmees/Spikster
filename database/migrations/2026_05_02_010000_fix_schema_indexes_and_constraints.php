<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Soft deletes on critical tables ───────────────────────────────────
        foreach (['servers', 'sites', 'backups', 'email_accounts'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes()->after('updated_at');
                });
            }
        }

        // ── Foreign keys for databases ────────────────────────────────────────
        if (Schema::hasTable('databases')) {
            Schema::table('databases', function (Blueprint $t) {
                if (! Schema::hasIndex('databases', 'databases_site_id_foreign')) {
                    $t->foreign('site_id')->references('site_id')->on('sites')->cascadeOnDelete();
                }
            });
        }

        if (Schema::hasTable('database_users')) {
            Schema::table('database_users', function (Blueprint $t) {
                if (! Schema::hasIndex('database_users', 'database_users_site_id_foreign')) {
                    $t->foreign('site_id')->references('site_id')->on('sites')->cascadeOnDelete();
                }
            });
        }

        if (Schema::hasTable('database_user_links')) {
            Schema::table('database_user_links', function (Blueprint $t) {
                if (! Schema::hasIndex('database_user_links', 'dul_database_user_id_foreign')) {
                    $t->foreign('database_user_id')->references('id')->on('database_users')->cascadeOnDelete();
                }
                if (! Schema::hasIndex('database_user_links', 'dul_database_id_foreign')) {
                    $t->foreign('database_id')->references('id')->on('databases')->cascadeOnDelete();
                }
            });
        }

        // ── Foreign key for domains ──────────────────────────────────────────
        if (Schema::hasTable('domains')) {
            Schema::table('domains', function (Blueprint $t) {
                if (! Schema::hasIndex('domains', 'domains_site_id_foreign')) {
                    $t->foreign('site_id')->references('site_id')->on('sites')->cascadeOnDelete();
                }
            });
        }

        // ── Foreign key for deployments ──────────────────────────────────────
        if (Schema::hasTable('deployments')) {
            Schema::table('deployments', function (Blueprint $t) {
                if (! Schema::hasIndex('deployments', 'deployments_server_id_foreign')) {
                    $t->foreign('server_id')->references('id')->on('servers')->cascadeOnDelete();
                }
            });
        }

        // ── Missing indexes ──────────────────────────────────────────────────
        if (Schema::hasTable('backups') && ! Schema::hasIndex('backups', 'backups_status_index')) {
            Schema::table('backups', fn (Blueprint $t) => $t->index('status'));
        }

        if (Schema::hasTable('ftp_users') && ! Schema::hasIndex('ftp_users', 'ftp_users_locked_until_index')) {
            Schema::table('ftp_users', fn (Blueprint $t) => $t->index('locked_until'));
        }

        if (Schema::hasTable('deployments') && ! Schema::hasIndex('deployments', 'deployments_server_id_index')) {
            Schema::table('deployments', fn (Blueprint $t) => $t->index('server_id'));
        }
    }

    public function down(): void
    {
        foreach (['servers', 'sites', 'backups', 'email_accounts'] as $table) {
            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropSoftDeletes());
            }
        }

        $foreignKeys = [
            'databases' => ['databases_site_id_foreign'],
            'database_users' => ['database_users_site_id_foreign'],
            'database_user_links' => ['dul_database_user_id_foreign', 'dul_database_id_foreign'],
            'domains' => ['domains_site_id_foreign'],
            'deployments' => ['deployments_server_id_foreign'],
        ];

        foreach ($foreignKeys as $table => $keys) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($keys) {
                    foreach ($keys as $key) {
                        $t->dropForeign($key);
                    }
                });
            }
        }

        $indexes = [
            'backups' => ['backups_status_index'],
            'ftp_users' => ['ftp_users_locked_until_index'],
            'deployments' => ['deployments_server_id_index'],
        ];

        foreach ($indexes as $table => $indexesList) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($indexesList) {
                    foreach ($indexesList as $idx) {
                        $t->dropIndex($idx);
                    }
                });
            }
        }
    }
};
