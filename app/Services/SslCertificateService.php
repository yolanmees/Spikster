<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Cache;

class SslCertificateService
{
    public function getCertificateInfo(Site $site): array
    {
        $cacheKey = "ssl_cert_{$site->domain}";

        return Cache::remember($cacheKey, 3600, function () use ($site) {
            $certInfo = $this->fetchCertInfo($site->domain);

            return [
                'domain' => $site->domain,
                'issuer' => $certInfo['issuer'] ?? null,
                'subject' => $certInfo['subject'] ?? null,
                'valid_from' => $certInfo['valid_from'] ?? null,
                'valid_to' => $certInfo['valid_to'] ?? null,
                'days_remaining' => $certInfo['days_remaining'] ?? 0,
                'is_expired' => ($certInfo['days_remaining'] ?? 0) <= 0,
                'is_expiring_soon' => ($certInfo['days_remaining'] ?? 999) < 30,
                'fingerprint_sha256' => $certInfo['fingerprint_sha256'] ?? null,
                'subject_alt_names' => $certInfo['subject_alt_names'] ?? [],
            ];
        });
    }

    public function getDashboardStats(): array
    {
        $sites = Site::nonPanel()->get();
        $stats = [
            'total' => $sites->count(),
            'valid' => 0,
            'expiring_soon' => 0,
            'expired' => 0,
            'unknown' => 0,
        ];

        foreach ($sites as $site) {
            $info = $this->getCertificateInfo($site);
            if ($info['is_expired']) {
                $stats['expired']++;
            } elseif ($info['is_expiring_soon']) {
                $stats['expiring_soon']++;
            } elseif ($info['days_remaining'] > 0) {
                $stats['valid']++;
            } else {
                $stats['unknown']++;
            }
        }

        return $stats;
    }

    protected function fetchCertInfo(string $domain): array
    {
        $cert = @stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false]]);
        $client = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $cert);

        if (! $client) {
            return [];
        }

        $params = stream_context_get_params($client);
        fclose($client);

        if (! isset($params['options']['ssl']['peer_certificate'])) {
            return [];
        }

        $certData = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

        if (! $certData) {
            return [];
        }

        $validTo = $certData['validTo_time_t'] ?? 0;
        $now = time();

        return [
            'issuer' => $certData['issuer']['O'] ?? $certData['issuer']['CN'] ?? 'Unknown',
            'subject' => $certData['subject']['CN'] ?? $domain,
            'valid_from' => date('Y-m-d H:i:s', $certData['validFrom_time_t'] ?? $now),
            'valid_to' => date('Y-m-d H:i:s', $validTo),
            'days_remaining' => (int) ceil(($validTo - $now) / 86400),
            'fingerprint_sha256' => $certData['fingerprint_sha256'] ?? null,
            'subject_alt_names' => explode(', ', str_replace('DNS:', '', $certData['extensions']['subjectAltName'] ?? '')),
        ];
    }
}
