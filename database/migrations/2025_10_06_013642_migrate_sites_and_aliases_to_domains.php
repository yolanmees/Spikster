<?php

use App\Models\Domain;
use App\Models\Site;
use App\Models\Alias;
use App\Models\DnsRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate all sites to domains
        Site::whereNotNull('domain')->chunk(100, function ($sites) {
            foreach ($sites as $site) {
                // Create primary domain from site
                $domain = Domain::create([
                    'domain_id' => Str::uuid(),
                    'site_id' => $site->site_id,
                    'server_id' => $site->server_id,
                    'domain' => $site->domain,
                    'is_primary' => true,
                ]);

                // Update DNS records to link to domain
                DnsRecord::where('site_id', $site->site_id)
                    ->update(['domain_id' => $domain->domain_id]);
            }
        });

        // Migrate all aliases to domains
        Alias::chunk(100, function ($aliases) {
            foreach ($aliases as $alias) {
                $site = Site::where('site_id', $alias->site_id)->first();
                if ($site) {
                    Domain::create([
                        'domain_id' => $alias->alias_id,
                        'site_id' => $alias->site_id,
                        'server_id' => $site->server_id,
                        'domain' => $alias->domain,
                        'is_primary' => false,
                    ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove migrated domains and reset dns records
        Domain::chunk(100, function ($domains) {
            foreach ($domains as $domain) {
                DnsRecord::where('domain_id', $domain->domain_id)
                    ->update(['domain_id' => null]);
            }
        });
        
        Domain::truncate();
    }
};
