<?php

use App\Models\Alias;
use App\Models\DnsRecord;
use App\Models\Domain;
use App\Models\Site;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate all sites to domains
        Site::whereNotNull('domain')->with('server')->chunk(100, function ($sites) {
            foreach ($sites as $site) {
                $domain = Domain::create([
                    'domain_id' => Str::uuid(),
                    'site_id' => $site->site_id,
                    'server_id' => $site->server?->server_id ?? '',
                    'domain' => $site->domain,
                    'is_primary' => true,
                ]);

                // Update DNS records to link to domain
                DnsRecord::where('site_id', $site->site_id)
                    ->update(['domain_id' => $domain->domain_id]);
            }
        });

        // Migrate all aliases to domains
        Alias::with('site.server')->chunk(100, function ($aliases) {
            foreach ($aliases as $alias) {
                $site = $alias->site;
                if ($site) {
                    Domain::create([
                        'domain_id' => $alias->alias_id,
                        'site_id' => $site->site_id,
                        'server_id' => $site->server?->server_id ?? '',
                        'domain' => $alias->domain,
                        'is_primary' => false,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Domain::chunk(100, function ($domains) {
            foreach ($domains as $domain) {
                DnsRecord::where('domain_id', $domain->domain_id)
                    ->update(['domain_id' => null]);
            }
        });

        Domain::truncate();
    }
};
