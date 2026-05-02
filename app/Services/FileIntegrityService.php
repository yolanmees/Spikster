<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class FileIntegrityService
{
    public function generateBaseline(Site $site): array
    {
        $rootPath = "/home/{$site->username}/web";
        if (! is_dir($rootPath)) {
            return ['generated' => false, 'error' => 'Site root not found'];
        }

        $baseline = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isReadable()) {
                $relativePath = str_replace($rootPath, '', $file->getPathname());
                $baseline[$relativePath] = [
                    'hash' => md5_file($file->getPathname()),
                    'size' => $file->getSize(),
                    'permissions' => substr(sprintf('%o', $file->getPerms()), -4),
                    'last_modified' => $file->getMTime(),
                ];
            }
        }

        $path = storage_path("app/integrity/{$site->site_id}.json");
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        file_put_contents($path, json_encode($baseline, JSON_PRETTY_PRINT));

        Log::info("Integrity baseline generated for {$site->domain}: ".count($baseline)." files");

        return ['generated' => true, 'files_count' => count($baseline)];
    }

    public function verify(Site $site): array
    {
        $path = storage_path("app/integrity/{$site->site_id}.json");
        if (! File::exists($path)) {
            return ['verified' => false, 'error' => 'No baseline found. Run generate first.'];
        }

        $baseline = json_decode(File::get($path), true);
        $rootPath = "/home/{$site->username}/web";

        if (! is_dir($rootPath)) {
            return ['verified' => false, 'error' => 'Site root not found'];
        }

        $changes = [];
        $currentFiles = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isReadable()) {
                $relativePath = str_replace($rootPath, '', $file->getPathname());
                $currentFiles[$relativePath] = true;

                $currentHash = md5_file($file->getPathname());

                if (! isset($baseline[$relativePath])) {
                    $changes[] = ['type' => 'new', 'file' => $relativePath, 'size' => $file->getSize()];
                } elseif ($baseline[$relativePath]['hash'] !== $currentHash) {
                    $changes[] = [
                        'type' => 'modified',
                        'file' => $relativePath,
                        'old_hash' => $baseline[$relativePath]['hash'],
                        'new_hash' => $currentHash,
                    ];
                }
            }
        }

        foreach ($baseline as $relativePath => $info) {
            if (! isset($currentFiles[$relativePath])) {
                $changes[] = ['type' => 'deleted', 'file' => $relativePath];
            }
        }

        Log::info("Integrity check for {$site->domain}: ".count($changes)." changes found");

        return [
            'verified' => empty($changes),
            'domain' => $site->domain,
            'total_changes' => count($changes),
            'changes' => $changes,
        ];
    }
}
