<?php

namespace App\Console\Commands;

use App\Models\Site;
use App\Services\CloudflareService;
use Illuminate\Console\Command;

class CloudflareSync extends Command
{
    protected $signature = 'cloudflare:sync {--dry-run : Show what would be done without making changes}';
    protected $description = 'Sync all panel sites with Cloudflare DNS';

    public function handle(CloudflareService $cloudflare): int
    {
        if (! $cloudflare->isConfigured()) {
            $this->error('Cloudflare not configured. Set CLOUDFLARE_API_TOKEN in .env');
            return 1;
        }

        $server = \App\Models\Server::first();
        if (! $server) {
            $this->error('No server configured');
            return 1;
        }

        $serverIp = $server->ip;
        $sites = Site::all();
        $dryRun = $this->option('dry-run');
        $synced = 0;
        $created = 0;

        foreach ($sites as $site) {
            $domain = $site->domain;

            if ($dryRun) {
                $this->line("  [DRY-RUN] Would sync {$domain} → {$serverIp}");
                $synced++;
                continue;
            }

            $this->line("  Syncing {$domain}...");

            try {
                $zone = $cloudflare->ensureZoneAndARecord($domain, $serverIp);

                if (! $zone) {
                    $this->warn("    Could not create zone for {$domain}");
                    continue;
                }

                // Add www CNAME if not exists
                $records = $cloudflare->listDnsRecords($zone['id'], 'CNAME');
                $hasWww = false;
                foreach ($records as $rec) {
                    if ($rec['name'] === 'www.' . $domain) {
                        $hasWww = true;
                        break;
                    }
                }

                if (! $hasWww) {
                    $cloudflare->createDnsRecord($zone['id'], 'CNAME', 'www', $domain);
                    $this->line("    Added www CNAME");
                }

                $this->info("    ✅ {$domain} synced");
                $synced++;
            } catch (\Exception $e) {
                $this->error("    ❌ {$domain}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Done! {$synced} sites synced" . ($dryRun ? ' (dry-run)' : ''));

        return 0;
    }
}
