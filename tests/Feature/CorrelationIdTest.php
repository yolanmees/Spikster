<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CorrelationIdTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function response_has_request_id_header(): void
    {
        $response = $this->get('/api/health');

        $response->assertHeader('X-Request-Id');
    }

    #[Test]
    public function request_id_is_valid_uuid(): void
    {
        $response = $this->get('/api/health');
        $requestId = $response->headers->get('X-Request-Id');

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $requestId
        );
    }

    #[Test]
    public function client_can_provide_request_id(): void
    {
        $clientId = 'my-trace-id-123';

        $response = $this->call('GET', '/api/health', server: [
            'HTTP_X-Request-Id' => $clientId,
        ]);

        $this->assertEquals($clientId, $response->headers->get('X-Request-Id'));
    }

    #[Test]
    public function blocked_ip_still_gets_request_id(): void
    {
        config(['security.ip.enable_whitelist' => true]);
        config(['security.ip.whitelist' => '10.0.0.1']);

        $clientId = 'blocked-trace-'.now()->timestamp;

        $response = $this->call('GET', '/api/health', server: [
            'HTTP_X-Request-Id' => $clientId,
            'REMOTE_ADDR' => '192.168.1.1',
        ]);

        $this->assertEquals($clientId, $response->headers->get('X-Request-Id'));
        $response->assertStatus(403);
    }
}
