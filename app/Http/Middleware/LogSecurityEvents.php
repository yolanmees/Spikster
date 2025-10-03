<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogSecurityEvents
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log suspicious patterns in request
        $this->checkForSuspiciousActivity($request);

        // Continue with the request
        $response = $next($request);

        // Log certain response codes
        if ($response->getStatusCode() === 403) {
            AuditService::logPermissionDenied(
                action: $request->path(),
            );
        }

        return $response;
    }

    /**
     * Check for suspicious activity in the request.
     */
    protected function checkForSuspiciousActivity(Request $request): void
    {
        $suspicious = false;
        $patterns = [];

        // Check for SQL injection patterns
        $sqlPatterns = ['union', 'select', 'insert', 'update', 'delete', 'drop', '--', ';--'];
        foreach ($sqlPatterns as $pattern) {
            if (stripos($request->getContent(), $pattern) !== false) {
                $suspicious = true;
                $patterns[] = 'SQL injection attempt';
                break;
            }
        }

        // Check for XSS patterns
        $xssPatterns = ['<script', 'javascript:', 'onerror=', 'onload='];
        foreach ($xssPatterns as $pattern) {
            if (stripos($request->getContent(), $pattern) !== false) {
                $suspicious = true;
                $patterns[] = 'XSS attempt';
                break;
            }
        }

        // Check for path traversal
        if (str_contains($request->getContent(), '../') || str_contains($request->getContent(), '..\\')) {
            $suspicious = true;
            $patterns[] = 'Path traversal attempt';
        }

        // Check for command injection
        $cmdPatterns = ['&&', '||', ';', '`', '$(', '${'];
        foreach ($cmdPatterns as $pattern) {
            if (stripos($request->getContent(), $pattern) !== false) {
                $suspicious = true;
                $patterns[] = 'Command injection attempt';
                break;
            }
        }

        // Log suspicious activity
        if ($suspicious) {
            AuditService::logSuspiciousActivity(
                description: 'Suspicious patterns detected: '.implode(', ', $patterns),
                metadata: [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'patterns' => $patterns,
                    'user_agent' => $request->userAgent(),
                ]
            );
        }
    }
}
