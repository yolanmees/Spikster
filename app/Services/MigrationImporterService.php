<?php

namespace App\Services;

use App\Models\Alias;
use App\Models\Database;
use App\Models\DatabaseUser;
use App\Models\DnsRecord;
use App\Models\EmailAccount;
use App\Models\EmailForwarder;
use App\Models\Server;
use App\Models\Site;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MigrationImporterService
{
    protected array $importLog = [];
    protected int $successCount = 0;
    protected int $failCount = 0;

    public function import(array $data, string $source = 'plesk'): array
    {
        $this->importLog = [];
        $this->successCount = 0;
        $this->failCount = 0;

        $steps = ['preflight', 'sites', 'databases', 'dns', 'mailboxes'];

        foreach ($steps as $step) {
            if (isset($data[$step])) {
                $method = "import{$step}";
                $this->$method($data[$step], $source);
            }
        }

        return [
            'success_count' => $this->successCount,
            'fail_count' => $this->failCount,
            'log' => $this->importLog,
            'completed' => $this->failCount === 0,
        ];
    }

    public function preflight(array $data): array
    {
        $checks = [];
        $allPassed = true;

        if (isset($data['servers'])) {
            foreach ($data['servers'] as $i => $server) {
                $valid = Validator::make($server, [
                    'ip' => 'required|ip',
                    'name' => 'required|string',
                ])->passes();
                $checks["server_{$i}"] = $valid;
                if (! $valid) $allPassed = false;
            }
        }

        if (isset($data['sites'])) {
            foreach ($data['sites'] as $i => $site) {
                $valid = Validator::make($site, [
                    'domain' => 'required|string',
                ])->passes();
                $checks["site_{$i}"] = $valid;
                if (! $valid) $allPassed = false;
            }
        }

        if (isset($data['databases'])) {
            foreach ($data['databases'] as $i => $db) {
                $valid = Validator::make($db, [
                    'name' => 'required|string',
                ])->passes();
                $checks["database_{$i}"] = $valid;
                if (! $valid) $allPassed = false;
            }
        }

        $existingDomains = Site::pluck('domain')->map(fn ($d) => strtolower($d))->toArray();
        $conflicts = [];
        if (isset($data['sites'])) {
            foreach ($data['sites'] as $site) {
                if (in_array(strtolower($site['domain']), $existingDomains)) {
                    $conflicts[] = $site['domain'];
                }
            }
        }

        return [
            'passed' => $allPassed,
            'checks' => $checks,
            'domain_conflicts' => $conflicts,
            'server_count' => count($data['servers'] ?? []),
            'site_count' => count($data['sites'] ?? []),
            'database_count' => count($data['databases'] ?? []),
        ];
    }

    protected function importSites(array $sites, string $source): void
    {
        $defaultServer = Server::where('default', true)->first();

        foreach ($sites as $siteData) {
            try {
                $domain = strtolower($siteData['domain']);

                if (Site::where('domain', $domain)->exists()) {
                    $this->log('skip', "Domain {$domain} already exists");
                    continue;
                }

                $site = Site::create([
                    'site_id' => 'ste_'.Str::random(16),
                    'server_id' => $defaultServer?->id ?? 1,
                    'domain' => $domain,
                    'username' => $siteData['username'] ?? str_replace('.', '_', $domain),
                    'password' => $siteData['password'] ?? Str::random(24),
                    'database' => $siteData['db_password'] ?? Str::random(24),
                    'php' => $siteData['php'] ?? config('spikster.default_php', '8.3'),
                    'basepath' => $siteData['basepath'] ?? '/public',
                ]);

                if (! empty($siteData['aliases'])) {
                    foreach ($siteData['aliases'] as $alias) {
                        Alias::create([
                            'alias_id' => 'als_'.Str::random(16),
                            'site_id' => $site->id,
                            'domain' => strtolower($alias),
                        ]);
                    }
                }

                $this->successCount++;
                $this->log('success', "Imported site {$domain}");
            } catch (\Throwable $e) {
                $this->failCount++;
                $this->log('error', "Failed to import site {$siteData['domain']}: {$e->getMessage()}");
            }
        }
    }

    protected function importDatabases(array $databases, string $source): void
    {
        foreach ($databases as $dbData) {
            try {
                $site = Site::where('domain', $dbData['site_domain'] ?? '')->first();
                if (! $site) {
                    $this->log('skip', "No site found for database {$dbData['name']}");
                    continue;
                }

                $db = Database::create([
                    'database_name' => $dbData['name'],
                    'site_id' => $site->site_id,
                    'user_id' => auth()->id(),
                ]);

                if (! empty($dbData['users'])) {
                    foreach ($dbData['users'] as $userData) {
                        $user = DatabaseUser::firstOrCreate([
                            'username' => $userData['username'],
                            'password' => $userData['password'] ?? Str::random(24),
                        ]);
                        $db->users()->attach($user->id);
                    }
                }

                $this->successCount++;
                $this->log('success', "Imported database {$dbData['name']}");
            } catch (\Throwable $e) {
                $this->failCount++;
                $this->log('error', "Failed to import database {$dbData['name']}: {$e->getMessage()}");
            }
        }
    }

    protected function importDns(array $records, string $source): void
    {
        foreach ($records as $recordData) {
            try {
                DnsRecord::create([
                    'domain_id' => $recordData['domain_id'] ?? null,
                    'site_id' => $recordData['site_id'] ?? null,
                    'type' => $recordData['type'] ?? 'A',
                    'zone' => $recordData['zone'] ?? '',
                    'value' => $recordData['value'] ?? '',
                    'ttl' => $recordData['ttl'] ?? 3600,
                    'priority' => $recordData['priority'] ?? null,
                ]);
                $this->successCount++;
            } catch (\Throwable $e) {
                $this->failCount++;
                $this->log('error', "DNS import failed: {$e->getMessage()}");
            }
        }
    }

    protected function importMailboxes(array $mailboxes, string $source): void
    {
        foreach ($mailboxes as $mbData) {
            try {
                $site = Site::where('domain', $mbData['domain'] ?? '')->first();
                if (! $site) {
                    continue;
                }

                EmailAccount::create([
                    'site_id' => $site->site_id,
                    'email' => $mbData['email'],
                    'password' => $mbData['password'] ?? Str::random(24),
                    'quota_mb' => $mbData['quota_mb'] ?? 1024,
                    'active' => true,
                ]);

                if (! empty($mbData['forwarders'])) {
                    foreach ($mbData['forwarders'] as $fwd) {
                        EmailForwarder::create([
                            'site_id' => $site->site_id,
                            'source' => $fwd['source'] ?? $mbData['email'],
                            'destination' => $fwd['destination'],
                        ]);
                    }
                }

                $this->successCount++;
                $this->log('success', "Imported mailbox {$mbData['email']}");
            } catch (\Throwable $e) {
                $this->failCount++;
                $this->log('error', "Mailbox import failed: {$e->getMessage()}");
            }
        }
    }

    protected function log(string $level, string $message): void
    {
        $this->importLog[] = ['level' => $level, 'message' => $message, 'time' => now()->toIso8601String()];
        Log::info("[MigrationImporter] {$message}");
    }

    public function getDryRunReport(array $data): array
    {
        $serverCount = count($data['sites'] ?? []);
        $dbCount = count($data['databases'] ?? []);
        $dnsCount = count($data['dns'] ?? []);
        $mailCount = count($data['mailboxes'] ?? []);

        $conflicts = [];
        foreach (($data['sites'] ?? []) as $site) {
            if (Site::where('domain', strtolower($site['domain']))->exists()) {
                $conflicts[] = $site['domain'];
            }
        }

        return [
            'dry_run' => true,
            'estimated_imports' => $serverCount + $dbCount + $dnsCount + $mailCount,
            'domain_conflicts' => $conflicts,
            'breakdown' => [
                'sites' => $serverCount,
                'databases' => $dbCount,
                'dns_records' => $dnsCount,
                'mailboxes' => $mailCount,
            ],
            'warning' => empty($conflicts) ? null : "Domain conflicts detected: ".implode(', ', $conflicts),
        ];
    }
}
