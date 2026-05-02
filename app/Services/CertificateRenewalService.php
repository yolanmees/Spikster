<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CertificateRenewalService
{
    public function __construct(
        protected SslCertificateService $certService,
        protected DaemonService $daemonService
    ) {}

    public function checkAndRenew(): array
    {
        $results = [];
        $sites = Site::nonPanel()->get();

        foreach ($sites as $site) {
            try {
                $info = $this->certService->getCertificateInfo($site);
                $needsRenewal = $info['days_remaining'] < 30 && $info['days_remaining'] > 0;
                $needsUrgentRenewal = $info['days_remaining'] < 7 && $info['days_remaining'] > 0;

                if ($needsUrgentRenewal || ($needsRenewal && $this->shouldRenew($site))) {
                    $result = $this->renewCertificate($site);
                    $results[] = [
                        'domain' => $site->domain,
                        'action' => 'renewed',
                        'success' => $result,
                        'days_remaining' => $info['days_remaining'],
                    ];
                } elseif ($info['is_expired']) {
                    $result = $this->renewCertificate($site);
                    $results[] = [
                        'domain' => $site->domain,
                        'action' => 'expired_renewed',
                        'success' => $result,
                    ];
                } else {
                    $results[] = [
                        'domain' => $site->domain,
                        'action' => 'skipped',
                        'days_remaining' => $info['days_remaining'],
                    ];
                }
            } catch (\Throwable $e) {
                Log::error("Cert renewal check failed for {$site->domain}: {$e->getMessage()}");
                $results[] = ['domain' => $site->domain, 'action' => 'error', 'error' => $e->getMessage()];
            }
        }

        $renewed = count(array_filter($results, fn ($r) => in_array($r['action'], ['renewed', 'expired_renewed'])));
        $failed = count(array_filter($results, fn ($r) => $r['action'] === 'error' || (isset($r['success']) && ! $r['success'])));

        return [
            'total' => count($results),
            'renewed' => $renewed,
            'failed' => $failed,
            'skipped' => count($results) - $renewed - $failed,
            'details' => $results,
        ];
    }

    public function renewCertificate(Site $site): bool
    {
        try {
            $success = $this->daemonService->enableSSL($site->username, $site->domain);
            Cache::forget("ssl_cert_{$site->domain}");
            Log::info("Certificate renewed for {$site->domain}: ".($success ? 'success' : 'failed'));

            return $success;
        } catch (\Throwable $e) {
            Log::error("Certificate renewal failed for {$site->domain}: {$e->getMessage()}");
            return false;
        }
    }

    protected function shouldRenew(Site $site): bool
    {
        $lastRenewal = Cache::get("last_cert_renewal_{$site->domain}");
        if (! $lastRenewal) {
            return true;
        }
        return now()->diffInHours($lastRenewal) > 24;
    }
}
