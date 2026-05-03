<?php

namespace Tests\Unit;

use App\Helpers\SecurityHelper;
use Tests\TestCase;

class SecurityHelperTest extends TestCase
{
    public function test_sanitize_input_prevents_xss(): void
    {
        $this->assertEquals('&lt;script&gt;alert(1)&lt;/script&gt;', SecurityHelper::sanitizeInput('<script>alert(1)</script>'));
    }

    public function test_sanitize_domain_strips_invalid_chars(): void
    {
        $this->assertEquals('example.com', SecurityHelper::sanitizeDomain('ExAmple.COM'));
        $this->assertEquals('example.com', SecurityHelper::sanitizeDomain('example.com<script>'));
    }

    public function test_is_valid_ip(): void
    {
        $this->assertTrue(SecurityHelper::isValidIp('127.0.0.1'));
        $this->assertTrue(SecurityHelper::isValidIp('::1'));
        $this->assertFalse(SecurityHelper::isValidIp('not-an-ip'));
    }

    public function test_generate_secure_password(): void
    {
        $password = SecurityHelper::generateSecurePassword(24);
        $this->assertEquals(24, strlen($password));
    }

    public function test_contains_suspicious_patterns(): void
    {
        $this->assertTrue(SecurityHelper::containsSuspiciousPatterns('<script>'));
        $this->assertTrue(SecurityHelper::containsSuspiciousPatterns('javascript:alert(1)'));
        $this->assertTrue(SecurityHelper::containsSuspiciousPatterns('onclick=alert(1)'));
        $this->assertFalse(SecurityHelper::containsSuspiciousPatterns('hello world'));
    }

    public function test_sanitize_filename_prevents_traversal(): void
    {
        $this->assertEquals('file.txt', SecurityHelper::sanitizeFilename('../../file.txt'));
        $this->assertEquals('passwd', SecurityHelper::sanitizeFilename('/etc/passwd'));
    }

    public function test_sanitize_shell_value_blocks_metacharacters(): void
    {
        $clean = SecurityHelper::sanitizeShellValue('hello; rm -rf /');
        $this->assertStringNotContainsString(';', $clean);
        $this->assertStringNotContainsString('`', $clean);
        $this->assertStringNotContainsString('$', $clean);
    }

    public function test_sanitize_url_only_allows_http_https(): void
    {
        $this->assertEquals('http://example.com', SecurityHelper::sanitizeUrl('http://example.com'));
        $this->assertEquals('https://example.com', SecurityHelper::sanitizeUrl('https://example.com'));
        $this->assertNull(SecurityHelper::sanitizeUrl('javascript:alert(1)'));
        $this->assertNull(SecurityHelper::sanitizeUrl('ftp://example.com'));
    }

    public function test_hash_for_log_truncates(): void
    {
        $hash = SecurityHelper::hashForLog('sensitive-data');
        $this->assertStringEndsWith('...', $hash);
        $this->assertEquals(11, strlen($hash));
    }
}
