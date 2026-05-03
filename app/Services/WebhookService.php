<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    public const EVENTS = [
        'server.created',
        'server.deleted',
        'server.provisioned',
        'site.created',
        'site.deleted',
        'site.deployed',
        'site.ssl_issued',
        'backup.completed',
        'backup.failed',
        'user.registered',
        'monitoring.alert',
    ];

    public function dispatch(string $event, array $payload = []): void
    {
        $webhooks = Webhook::active()->get()->filter(fn (Webhook $w) => $w->listensFor($event));

        foreach ($webhooks as $webhook) {
            $this->send($webhook, $event, $payload);
        }
    }

    public function send(Webhook $webhook, string $event, array $payload): WebhookDelivery
    {
        // Validate URL destination before sending — block SSRF to internal services
        if (! $this->isSafeUrl($webhook->url)) {
            throw new \InvalidArgumentException('Webhook URL targets an internal or unsafe destination.');
        }

        $body = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'payload' => $payload,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'Spikster-Webhook/1.0',
            'X-Webhook-Event' => $event,
            'X-Webhook-Timestamp' => (string) now()->timestamp,
        ];

        if ($webhook->secret) {
            $signature = hash_hmac('sha256', json_encode($body), $webhook->secret);
            $headers['X-Webhook-Signature'] = $signature;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->post($webhook->url, $body);

            $delivery = WebhookDelivery::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 5000),
                'status' => $response->successful() ? 'success' : 'failed',
            ]);

            $webhook->update([
                'last_sent_at' => now(),
                'last_response_code' => $response->status(),
                'failure_count' => $response->successful()
                    ? 0
                    : $webhook->failure_count + 1,
            ]);

            return $delivery;
        } catch (\Throwable $e) {
            $delivery = WebhookDelivery::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            $webhook->update([
                'last_sent_at' => now(),
                'failure_count' => $webhook->failure_count + 1,
            ]);

            return $delivery;
        }
    }

    public function test(Webhook $webhook): WebhookDelivery
    {
        return $this->send($webhook, 'ping', [
            'message' => 'This is a test webhook from Spikster.',
        ]);
    }

    public function availableEvents(): array
    {
        return self::EVENTS;
    }

    /**
     * Check if a URL is safe to send webhooks to — blocks internal/private IPs.
     */
    private function isSafeUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (! $host) {
            return false;
        }

        // Block localhost variations
        $lowerHost = strtolower($host);
        if (in_array($lowerHost, ['localhost', '127.0.0.1', '::1', '0.0.0.0'])) {
            return false;
        }

        // Resolve DNS and check for private/internal IPs
        $ip = gethostbyname($host);
        if ($ip === $host || $ip === '') {
            return false; // DNS resolution failed
        }

        // Block private, loopback, link-local, and cloud metadata IPs
        if (
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
        ) {
            return false;
        }

        return true;
    }
}
