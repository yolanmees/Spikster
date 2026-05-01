<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class MailStatusController extends Controller
{
    private const CHECKS = [
        ['label' => 'IMAP',       'port' => 143],
        ['label' => 'IMAPS',      'port' => 993],
        ['label' => 'SMTP',       'port' => 25],
        ['label' => 'Submission', 'port' => 587],
        ['label' => 'SMTPS',      'port' => 465],
    ];

    /**
     * Check IMAP/SMTP port availability for the site's server.
     */
    public function status(string $site_id): JsonResponse
    {
        $site = Site::where('site_id', $site_id)->with('server')->firstOrFail();

        $host = $site->server->ip ?? null;
        $results = [];

        foreach (self::CHECKS as $check) {
            $results[] = [
                'service' => $check['label'],
                'port' => $check['port'],
                'reachable' => $host ? $this->tcpCheck($host, $check['port']) : false,
            ];
        }

        $allOk = collect($results)->every(fn ($r) => $r['reachable']);

        return response()->json([
            'host' => $host,
            'status' => $allOk ? 'ok' : 'degraded',
            'checks' => $results,
        ]);
    }

    private function tcpCheck(string $host, int $port, int $timeoutSeconds = 3): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, $timeoutSeconds);

        if (is_resource($connection)) {
            fclose($connection);

            return true;
        }

        return false;
    }
}
