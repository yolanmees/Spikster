<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\DaemonService;
use App\Services\RemoteDaemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SiteHealthController extends Controller
{
    public function __construct(
        protected DaemonService $daemon,
        protected RemoteDaemonService $remoteDaemon
    ) {}

    public function check(Request $request, string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->with('server')->firstOrFail();

        $results = [];

        $results[] = $this->checkHttpEndpoint($site);
        $results[] = $this->checkService($site, 'nginx');
        $results[] = $this->checkPhpFpm($site);
        $results[] = $this->checkDiskUsage($site);

        $passed = count(array_filter($results, fn ($r) => $r['status'] === 'pass'));
        $failed = count(array_filter($results, fn ($r) => $r['status'] === 'fail'));

        return response()->json([
            'success' => true,
            'summary' => "{$passed} passed, {$failed} failed",
            'checks' => $results,
        ]);
    }

    private function checkHttpEndpoint(Site $site): array
    {
        $start = microtime(true);

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->get("https://{$site->domain}");

            $duration = round((microtime(true) - $start) * 1000);

            return [
                'check' => 'HTTP Endpoint',
                'status' => $response->successful() ? 'pass' : 'warn',
                'detail' => "{$site->domain} responded with {$response->status()} in {$duration}ms",
            ];
        } catch (\Throwable $e) {
            try {
                $start = microtime(true);
                $response = Http::timeout(10)->get("http://{$site->domain}");
                $duration = round((microtime(true) - $start) * 1000);

                return [
                    'check' => 'HTTP Endpoint',
                    'status' => $response->successful() ? 'warn' : 'fail',
                    'detail' => "{$site->domain} responded with {$response->status()} in {$duration}ms (no HTTPS)",
                ];
            } catch (\Throwable $e2) {
                return [
                    'check' => 'HTTP Endpoint',
                    'status' => 'fail',
                    'detail' => "Cannot reach {$site->domain}: {$e2->getMessage()}",
                ];
            }
        }
    }

    private function checkService(Site $site, string $service): array
    {
        try {
            $daemon = $this->getDaemon($site);
            $status = $daemon->status($service);

            return [
                'check' => ucfirst($service),
                'status' => str_contains($status, 'running') || str_contains($status, 'active') ? 'pass' : 'fail',
                'detail' => $status,
            ];
        } catch (\Throwable $e) {
            return [
                'check' => ucfirst($service),
                'status' => 'warn',
                'detail' => "Cannot check {$service}: {$e->getMessage()}",
            ];
        }
    }

    private function checkPhpFpm(Site $site): array
    {
        $service = "php{$site->php}-fpm";

        return $this->checkService($site, $service);
    }

    private function checkDiskUsage(Site $site): array
    {
        try {
            $daemon = $this->getDaemon($site);
            $result = $daemon->send('server.disk-usage');

            if (isset($result['output'])) {
                preg_match('/(\d+)%/', $result['output'], $matches);
                $percent = (int) ($matches[1] ?? 0);

                return [
                    'check' => 'Disk Usage',
                    'status' => $percent > 90 ? 'fail' : ($percent > 80 ? 'warn' : 'pass'),
                    'detail' => "{$percent}% used",
                ];
            }

            return [
                'check' => 'Disk Usage',
                'status' => 'warn',
                'detail' => 'Could not determine disk usage',
            ];
        } catch (\Throwable $e) {
            return [
                'check' => 'Disk Usage',
                'status' => 'warn',
                'detail' => "Cannot check disk: {$e->getMessage()}",
            ];
        }
    }

    private function getDaemon(Site $site): DaemonService|RemoteDaemonService
    {
        $panelServerId = config('spikster.panel_server_id');

        if ($site->server->server_id === $panelServerId) {
            return $this->daemon;
        }

        return $this->remoteDaemon;
    }
}
