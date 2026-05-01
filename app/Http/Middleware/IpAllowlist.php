<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpAllowlist
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.ip.enable_whitelist', false)) {
            return $next($request);
        }

        $whitelist = config('security.ip.whitelist', '');
        if (empty($whitelist)) {
            return $next($request);
        }

        $allowedIps = array_map('trim', explode(',', $whitelist));
        $requestIp = $request->ip();

        if (in_array($requestIp, $allowedIps, true)) {
            return $next($request);
        }

        foreach ($allowedIps as $allowed) {
            if (str_contains($allowed, '*') && fnmatch($allowed, $requestIp)) {
                return $next($request);
            }
            if (str_contains($allowed, '/')) {
                if ($this->ipInCidr($requestIp, $allowed)) {
                    return $next($request);
                }
            }
        }

        AuditLog::create([
            'event_type' => 'ip_blocked',
            'description' => "Request from non-whitelisted IP: {$requestIp}",
            'ip_address' => $requestIp,
            'user_agent' => $request->userAgent(),
            'severity' => 'warning',
        ]);

        return response()->json([
            'message' => 'Access denied from your IP address.',
            'errors' => 'IP not allowed.',
        ], 403);
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        $parts = explode('/', $cidr);
        if (count($parts) !== 2) {
            return false;
        }

        $network = ip2long($parts[0]);
        $mask = (int) $parts[1];

        if ($network === false || $mask < 0 || $mask > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false;
        }

        $maskLong = -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($network & $maskLong);
    }
}
