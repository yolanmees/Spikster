<?php

namespace Tests\Feature;

use App\Models\Server;
use App\Models\Site;
use App\Services\MigrationImporterService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PleskMigrationTest extends TestCase
{
    use RefreshDatabase;

    private MigrationImporterService $importer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->importer = app(MigrationImporterService::class);
        Server::factory()->create(['default' => true, 'name' => 'Default Server', 'ip' => '10.0.0.1']);
    }

    public function test_preflight_detects_domain_conflicts(): void
    {
        Site::factory()->create(['domain' => 'existing.com']);

        $data = [
            'sites' => [
                ['domain' => 'existing.com', 'username' => 'existing'],
                ['domain' => 'new-site.com', 'username' => 'newsite'],
            ],
        ];

        $result = $this->importer->preflight($data);

        $this->assertContains('existing.com', $result['domain_conflicts']);
        $this->assertCount(1, $result['domain_conflicts']);
    }

    public function test_preflight_passes_with_no_conflicts(): void
    {
        $data = [
            'servers' => [['ip' => '10.0.0.1', 'name' => 'Server 1']],
            'sites' => [['domain' => 'brand-new.com', 'username' => 'brandnew']],
            'databases' => [['name' => 'test_db']],
        ];

        $result = $this->importer->preflight($data);
        $this->assertTrue($result['passed']);
    }

    public function test_import_sites_creates_records(): void
    {
        $data = [
            'sites' => [
                [
                    'domain' => 'imported.com',
                    'username' => 'imported',
                    'password' => 'pass123',
                    'php' => '8.3',
                    'aliases' => ['www.imported.com'],
                ],
            ],
        ];

        $result = $this->importer->import($data, 'plesk');

        $this->assertEquals(1, $result['success_count']);
        $this->assertEquals(0, $result['fail_count']);
        $this->assertDatabaseHas('sites', ['domain' => 'imported.com']);
    }

    public function test_import_skips_existing_domains(): void
    {
        Site::factory()->create(['domain' => 'exists.com']);

        $data = [
            'sites' => [
                ['domain' => 'exists.com', 'username' => 'exists'],
                ['domain' => 'new.com', 'username' => 'new'],
            ],
        ];

        $result = $this->importer->import($data, 'plesk');

        $this->assertEquals(1, $result['success_count']);
        $this->assertEquals(0, $result['fail_count']);
    }

    public function test_dry_run_reports_estimated_imports(): void
    {
        $data = [
            'sites' => [['domain' => 'test.com', 'username' => 'test']],
            'databases' => [['name' => 'test_db', 'site_domain' => 'test.com']],
            'dns' => [['type' => 'A', 'zone' => 'test.com', 'value' => '1.2.3.4']],
            'mailboxes' => [['email' => 'admin@test.com', 'domain' => 'test.com']],
        ];

        $report = $this->importer->getDryRunReport($data);

        $this->assertTrue($report['dry_run']);
        $this->assertEquals(4, $report['estimated_imports']);
        $this->assertArrayHasKey('breakdown', $report);
    }

    public function test_full_plesk_import_flow(): void
    {
        $data = [
            'sites' => [
                ['domain' => 'migrated.com', 'username' => 'migrated', 'php' => '8.3'],
                ['domain' => 'another.com', 'username' => 'another', 'php' => '8.2'],
            ],
            'databases' => [
                ['name' => 'migrated_db', 'site_domain' => 'migrated.com'],
            ],
        ];

        $result = $this->importer->import($data, 'plesk');

        $this->assertEquals(3, $result['success_count']);
        $this->assertEquals(0, $result['fail_count']);
        $this->assertTrue($result['completed']);
    }
}
