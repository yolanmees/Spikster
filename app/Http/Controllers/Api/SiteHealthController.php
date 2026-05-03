<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScanResult;
use App\Models\Site;
use App\Services\DaemonService;
use App\Services\MalwareScannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SiteHealthController extends Controller
{
    public function __construct(
        protected DaemonService $daemon
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
        $failed  = count(array_filter($results, fn ($r) => $r['status'] === 'fail'));

        return response()->json([
            'success' => true,
            'summary' => "{$passed} passed, {$failed} failed",
            'checks'  => $results,
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
                'check'  => 'HTTP Endpoint',
                'status' => $response->successful() ? 'pass' : 'warn',
                'detail' => "{$site->domain} responded with {$response->status()} in {$duration}ms",
            ];
        } catch (\Throwable $e) {
            try {
                $start    = microtime(true);
                $response = Http::timeout(10)->get("http://{$site->domain}");
                $duration = round((microtime(true) - $start) * 1000);

                return [
                    'check'  => 'HTTP Endpoint',
                    'status' => $response->successful() ? 'warn' : 'fail',
                    'detail' => "{$site->domain} responded with {$response->status()} in {$duration}ms (no HTTPS)",
                ];
            } catch (\Throwable $e2) {
                return [
                    'check'  => 'HTTP Endpoint',
                    'status' => 'fail',
                    'detail' => "Cannot reach {$site->domain}: {$e2->getMessage()}",
                ];
            }
        }
    }

    private function checkService(Site $site, string $service): array
    {
        try {
            $status = $this->daemon->status($service);

            return [
                'check'  => ucfirst($service),
                'status' => str_contains($status, 'running') || str_contains($status, 'active') ? 'pass' : 'fail',
                'detail' => $status,
            ];
        } catch (\Throwable $e) {
            return [
                'check'  => ucfirst($service),
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
            $result = $this->daemon->send('site.deploy-script', [
                'username' => $site->username,
                'script'   => "df / | awk 'NR==2{print $5}'",
            ]);

            if (isset($result['output'])) {
                preg_match('/(\d+)%/', $result['output'], $matches);
                $percent = (int) ($matches[1] ?? 0);

                return [
                    'check'  => 'Disk Usage',
                    'status' => $percent > 90 ? 'fail' : ($percent > 80 ? 'warn' : 'pass'),
                    'detail' => "{$percent}% used",
                ];
            }

            return [
                'check'  => 'Disk Usage',
                'status' => 'warn',
                'detail' => 'Could not determine disk usage',
            ];
        } catch (\Throwable $e) {
            return [
                'check'  => 'Disk Usage',
                'status' => 'warn',
                'detail' => "Cannot check disk: {$e->getMessage()}",
            ];
        }
    }

    public function fileScan(string $siteId): JsonResponse
    {
        $site = Site::where('site_id', $siteId)->firstOrFail();

        try {
            $scanner = app(MalwareScannerService::class);
            $result = $scanner->scanSite($site);

            ScanResult::create([
                'site_id' => $siteId,
                'type' => 'site-scan',
                'status' => $result['is_clean'] ? 'clean' : 'warning',
                'findings' => $result['findings'] ?? [],
                'findings_count' => $result['findings_count'] ?? 0,
                'files_scanned' => $result['files_scanned'] ?? 0,
                'scanned_by' => auth()->user()?->email ?? 'system',
                'scanned_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'scan' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
