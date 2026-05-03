<?php

namespace Tests\Unit;

use App\Services\DnsService;
use Tests\TestCase;

class DnsServiceTest extends TestCase
{
    private DnsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DnsService;
    }

    public function test_allowed_record_types_are_validated(): void
    {
        $this->assertContains('A', DnsService::ALLOWED_RECORD_TYPES);
        $this->assertContains('AAAA', DnsService::ALLOWED_RECORD_TYPES);
        $this->assertContains('CNAME', DnsService::ALLOWED_RECORD_TYPES);
        $this->assertContains('MX', DnsService::ALLOWED_RECORD_TYPES);
        $this->assertContains('TXT', DnsService::ALLOWED_RECORD_TYPES);
        $this->assertContains('SRV', DnsService::ALLOWED_RECORD_TYPES);
        $this->assertContains('CAA', DnsService::ALLOWED_RECORD_TYPES);
    }

    public function test_add_zone_requires_zone_email_and_nameservers(): void
    {
        $this->expectException(\Exception::class);
        $this->service->addZone('', '', []);
    }

    public function test_invalid_zone_name_rejected(): void
    {
        $this->expectException(\Exception::class);
        $this->service->addZone('-invalid-zone-', 'admin@example.com', ['ns1.example.com', 'ns2.example.com']);
    }

    public function test_add_record_requires_all_params(): void
    {
        $this->expectException(\Exception::class);
        $this->service->addRecord('', '', '', '');
    }

    public function test_unsupported_record_type_rejected(): void
    {
        $this->expectException(\Exception::class);
        $this->service->addRecord('example.com', '@', 'INVALID_TYPE', '127.0.0.1');
    }

    public function test_delete_zone_requires_zone_param(): void
    {
        $this->expectException(\Exception::class);
        $this->service->deleteZone('');
    }

    public function test_delete_record_requires_all_params(): void
    {
        $this->expectException(\Exception::class);
        $this->service->deleteRecord('', '', '', '');
    }

    public function test_unsupported_record_type_rejected_on_delete(): void
    {
        $this->expectException(\Exception::class);
        $this->service->deleteRecord('example.com', '@', 'INVALID', '127.0.0.1');
    }

    public function test_add_zone_with_fewer_than_2_nameservers_rejected(): void
    {
        $result = $this->service->addZone('example.com', 'admin@example.com', ['ns1.example.com']);
        $decoded = json_decode($result, true);
        $this->assertSame(1, $decoded['code']);
        $this->assertStringContainsString('At least 2', $decoded['message']);
    }

    public function test_add_zone_with_more_than_13_nameservers_rejected(): void
    {
        $ns = [];
        for ($i = 0; $i < 14; $i++) {
            $ns[] = "ns{$i}.example.com";
        }
        $result = $this->service->addZone('example.com', 'admin@example.com', $ns);
        $decoded = json_decode($result, true);
        $this->assertSame(1, $decoded['code']);
        $this->assertStringContainsString('at most 13', $decoded['message']);
    }

    public function test_invalid_email_rejected(): void
    {
        $result = $this->service->addZone('example.com', 'not-an-email', ['ns1.example.com', 'ns2.example.com']);
        $decoded = json_decode($result, true);
        $this->assertSame(1, $decoded['code']);
    }
}
