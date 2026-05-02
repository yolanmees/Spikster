<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateOpenApiCommand extends Command
{
    protected $signature = 'spikster:openapi-generate
        {--output=public/docs/openapi.json : Output path for the spec}';

    protected $description = 'Generate OpenAPI specification';

    public function handle(): int
    {
        $this->info('Generating OpenAPI specification...');

        $outputPath = $this->option('output');

        // Use l5-swagger to generate
        $this->call('l5-swagger:generate');

        // Copy to the desired output location
        $generatedPath = storage_path('api-docs/api-docs.json');
        $targetPath = base_path($outputPath);
        $targetDir = dirname($targetPath);

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (file_exists($generatedPath)) {
            copy($generatedPath, $targetPath);
            $this->info("OpenAPI spec generated at: {$targetPath}");
        } else {
            // Generate a minimal spec if l5-swagger isn't fully configured
            $spec = $this->generateMinimalSpec();
            file_put_contents($targetPath, json_encode($spec, JSON_PRETTY_PRINT));
            $this->info("Minimal OpenAPI spec generated at: {$targetPath}");
        }

        return 0;
    }

    protected function generateMinimalSpec(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Spikster API',
                'version' => config('app.version', '2.0.0'),
                'description' => 'Spikster Server Control Panel API',
            ],
            'servers' => [
                ['url' => config('app.url').'/api', 'description' => 'API server'],
            ],
            'paths' => [
                '/health' => [
                    'get' => [
                        'summary' => 'Health check',
                        'responses' => ['200' => ['description' => 'OK']],
                    ],
                ],
                '/servers' => [
                    'get' => [
                        'summary' => 'List servers',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['200' => ['description' => 'List of servers']],
                    ],
                ],
                '/sites' => [
                    'get' => [
                        'summary' => 'List sites',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['200' => ['description' => 'List of sites']],
                    ],
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum',
                    ],
                ],
            ],
        ];
    }
}
