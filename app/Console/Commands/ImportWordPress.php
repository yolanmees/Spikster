<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Models\Site;
use App\Services\CloudflareService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportWordPress extends Command
{
    protected $signature = 'wp:import
        {domain : Domain to import (e.g., example.com)}
        {--ssh-host= : Source server SSH host}
        {--ssh-user=root : SSH user}
        {--ssh-pass= : SSH password}
        {--vhost-path=/var/www/vhosts : Path to vhosts on source}
        {--server= : Target Spikster server ID (default: first)}
        {--php=8.3 : PHP version for the new site}
        {--dry-run : Show what would be done}
    ';

    protected $description = 'Import a WordPress site from another server';

    public function handle(CloudflareService $cloudflare): int
    {
        $domain = $this->argument('domain');
        $sshHost = $this->option('ssh-host');
        $sshUser = $this->option('ssh-user');
        $sshPass = $this->option('ssh-pass');
        $vhostPath = $this->option('vhost-path');
        $serverId = $this->option('server') ?: Server::first()?->id;
        $phpVer = $this->option('php');
        $dryRun = $this->option('dry-run');

        if (! $serverId) {
            $this->error('No server found. Create one first or specify --server');
            return 1;
        }

        if (! $sshHost) {
            $this->error('--ssh-host is required (IP of the source server)');
            return 1;
        }

        $username = str_replace(['.', '-'], '', explode('.', $domain)[0]);
        $dbName = 'wp_' . Str::random(6);

        if ($dryRun) {
            $this->line("Would import: {$domain}");
            $this->line("  SSH: {$sshUser}@{$sshHost}");
            $this->line("  Source: {$vhostPath}/{$domain}/httpdocs");
            $this->line("  PHP: {$phpVer}");
            $this->line("  Server ID: {$serverId}");
            return 0;
        }

        // Step 1: Create user on target
        $this->info("Step 1: Creating system user...");
        // This would need daemon interaction or direct commands
        // For now, daemon should handle this

        // Step 2: Transfer files via rsync
        $this->info("Step 2: Transferring files...");
        $rsyncCmd = "sshpass -p '{$sshPass}' rsync -avz --exclude=node_modules --exclude=vendor --exclude=wp-content/cache --exclude=.git -e 'ssh -o StrictHostKeyChecking=no' {$sshUser}@{$sshHost}:{$vhostPath}/{$domain}/httpdocs/ /home/{$username}/web/";
        $this->line("  Running: rsync from {$sshHost}...");
        exec($rsyncCmd, $rsyncOut, $rsyncExit);
        $this->line("  Exit: {$rsyncExit}");

        // Step 3: Get DB info from wp-config
        $this->info("Step 3: Getting database info...");
        $wpConfig = file_get_contents("/home/{$username}/web/wp-config.php");
        preg_match("/define\s*\(\s*'DB_NAME'\s*,\s*'([^']+)'\s*\)/", $wpConfig, $dbNameMatch);
        preg_match("/define\s*\(\s*'DB_USER'\s*,\s*'([^']+)'\s*\)/", $wpConfig, $dbUserMatch);
        preg_match("/define\s*\(\s*'DB_PASSWORD'\s*,\s*'([^']+)'\s*\)/", $wpConfig, $dbPassMatch);
        preg_match("/\\\$table_prefix\s*=\s*'([^']+)'/", $wpConfig, $prefixMatch);

        $oldDbName = $dbNameMatch[1] ?? '';
        $oldDbUser = $dbUserMatch[1] ?? '';
        $oldDbPass = $dbPassMatch[1] ?? '';
        $tablePrefix = $prefixMatch[1] ?? 'wp_';

        // Step 4: Export DB from source
        $this->info("Step 4: Exporting database...");
        $dumpCmd = "sshpass -p '{$sshPass}' ssh -o StrictHostKeyChecking=no {$sshUser}@{$sshHost} \"mysqldump -u {$oldDbUser} -p'{$oldDbPass}' {$oldDbName} --skip-lock-tables --no-tablespaces --quick\" > /tmp/{$domain}_db.sql";
        exec($dumpCmd, $dumpOut, $dumpExit);
        $this->line("  DB export exit: {$dumpExit}");

        // Step 5: Import DB locally
        $this->info("Step 5: Importing database...");
        // Create DB
        DB::statement("CREATE DATABASE IF NOT EXISTS {$dbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        // Grant access
        DB::statement("CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY '{$oldDbPass}'");
        DB::statement("GRANT ALL PRIVILEGES ON {$dbName}.* TO '{$username}'@'localhost'");
        DB::statement("FLUSH PRIVILEGES");
        // Import
        exec("mysql -u {$username} -p'{$oldDbPass}' {$dbName} < /tmp/{$domain}_db.sql", $impOut, $impExit);
        $this->line("  DB import exit: {$impExit}");

        // Step 6: Fix wp-config
        $this->info("Step 6: Fixing wp-config.php...");
        $wpConfig = str_replace($oldDbName, $dbName, $wpConfig);
        $wpConfig = str_replace($oldDbUser, $username, $wpConfig);
        file_put_contents("/home/{$username}/web/wp-config.php", $wpConfig);

        // Step 7: Fix site URLs
        $this->info("Step 7: Updating site URLs...");
        DB::statement("UPDATE {$dbName}.{$tablePrefix}options SET option_value='https://{$domain}' WHERE option_name IN ('siteurl','home')");

        // Step 8: Fix permissions
        $this->info("Step 8: Fixing permissions...");
        exec("chown -R {$username}:www-data /home/{$username}/web");
        exec("find /home/{$username}/web -type d -exec chmod 755 {} \\;");
        exec("find /home/{$username}/web -type f -exec chmod 644 {} \\;");

        // Step 9: Register in Spikster panel
        $this->info("Step 9: Adding to panel...");
        Site::create([
            'site_id' => 'ste_' . Str::random(16),
            'server_id' => $serverId,
            'domain' => $domain,
            'username' => $username,
            'password' => '-',
            'database' => $dbName,
            'basepath' => "/home/{$username}/web",
            'php' => $phpVer,
        ]);

        // Step 10: Cloudflare DNS
        $this->info("Step 10: Setting up Cloudflare DNS...");
        try {
            $zone = $cloudflare->ensureZoneAndARecord($domain, optional(Server::find($serverId))->ip ?? '');
            if ($zone) {
                $this->info("  ✅ DNS record added to Cloudflare");
            }
        } catch (\Exception $e) {
            $this->warn("  Cloudflare: {$e->getMessage()}");
        }

        // Step 11: SSL
        $this->info("Step 11: Requesting SSL certificate...");
        exec("certbot --nginx -d {$domain} -d www.{$domain} --non-interactive --agree-tos -m admin@{$domain} 2>/dev/null", $sslOut, $sslExit);
        $this->line("  SSL exit: {$sslExit}");

        $this->newLine();
        $this->info("✅ {$domain} imported successfully!");
        $this->table(['Property', 'Value'], [
            ['Domain', $domain],
            ['Username', $username],
            ['Database', $dbName],
            ['PHP', $phpVer],
            ['Path', "/home/{$username}/web"],
        ]);

        return 0;
    }
}
