<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class SecurityHelper
{
    /**
     * Sanitize user input to prevent XSS attacks
     */
    public static function sanitizeInput(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize for SQL-like queries (additional layer of protection)
     */
    public static function sanitizeSql(string $input): string
    {
        return str_replace(['--', ';', '/*', '*/', 'xp_', 'sp_'], '', $input);
    }

    /**
     * Validate and sanitize domain name
     */
    public static function sanitizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('/[^a-z0-9\-\.]/', '', $domain);

        return $domain;
    }

    /**
     * Validate IP address
     */
    public static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Generate secure random password
     */
    public static function generateSecurePassword(int $length = 16): string
    {
        return Str::password($length, symbols: true);
    }

    /**
     * Check if string contains suspicious patterns
     */
    public static function containsSuspiciousPatterns(string $input): bool
    {
        $patterns = [
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // Event handlers like onclick=
            '/<iframe/i',
            '/<embed/i',
            '/<object/i',
            '/eval\(/i',
            '/base64_decode/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize filename for safe file operations
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove any path separators
        $filename = basename($filename);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        // Prevent directory traversal
        $filename = str_replace(['..', './'], '', $filename);

        return $filename;
    }

    /**
     * Validate SSH command to prevent command injection
     */
    public static function sanitizeSshCommand(string $command): string
    {
        // Remove dangerous characters
        $dangerous = ['&', '|', ';', '`', '$', '(', ')', '<', '>', "\n", "\r"];
        $command = str_replace($dangerous, '', $command);

        return trim($command);
    }

    /**
     * Hash sensitive data for logs
     */
    public static function hashForLog(string $data): string
    {
        return substr(hash('sha256', $data), 0, 8).'...';
    }

    /**
     * Validate URL and ensure it's safe
     */
    public static function sanitizeUrl(string $url): ?string
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        // Only allow http and https protocols
        $parsed = parse_url($url);
        if (! in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return null;
        }

        return $url;
    }
}
