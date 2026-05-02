<?php

namespace App\Services;

class SecondaryDnsService
{
    public function configureZoneTransfer(string $zone, string $secondaryIp, string $action = 'allow'): string
    {
        $namedConf = '/etc/bind/named.conf.local';
        $conf = file_get_contents($namedConf);

        $zoneBlock = $this->findZoneBlock($conf, $zone);

        if (! $zoneBlock) {
            throw new \RuntimeException("Zone '{$zone}' not found in named.conf");
        }

        $allowTransferLine = "allow-transfer { {$secondaryIp}; };";

        if ($action === 'allow') {
            if (str_contains($zoneBlock, 'allow-transfer')) {
                // Update existing allow-transfer
                $updated = preg_replace(
                    '/allow-transfer\s*\{[^}]+\};/',
                    $allowTransferLine,
                    $zoneBlock
                );
            } else {
                // Add allow-transfer before the closing brace
                $updated = preg_replace(
                    '/\n\};$/',
                    "\n    {$allowTransferLine}\n};",
                    $zoneBlock
                );
            }
            $conf = str_replace($zoneBlock, $updated, $conf);
        } else {
            // Remove allow-transfer
            $conf = preg_replace('/\s*allow-transfer\s*\{[^}]+\};/', '', $conf);
        }

        file_put_contents($namedConf, $conf);
        exec('rndc reload 2>&1', $output, $code);

        if ($code !== 0) {
            throw new \RuntimeException('Failed to reload BIND: '.implode("\n", $output));
        }

        return "Zone transfer for '{$zone}' {$action}ed for {$secondaryIp}";
    }

    public function getZoneTransferStatus(string $zone): array
    {
        $conf = file_get_contents('/etc/bind/named.conf.local');
        $zoneBlock = $this->findZoneBlock($conf, $zone);

        if (! $zoneBlock) {
            return ['zone' => $zone, 'configured' => false];
        }

        preg_match('/allow-transfer\s*\{([^}]+)\};/', $zoneBlock, $matches);

        return [
            'zone' => $zone,
            'configured' => ! empty($matches),
            'allowed_ips' => $matches ? array_map('trim', explode(';', trim($matches[1]))) : [],
        ];
    }

    protected function findZoneBlock(string $conf, string $zone): ?string
    {
        preg_match('/zone\s+"'.preg_quote($zone, '/').'"\s*\{[^}]+\};/s', $conf, $matches);

        return $matches[0] ?? null;
    }
}
