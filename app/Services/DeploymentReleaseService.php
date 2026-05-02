<?php

namespace App\Services;

use App\Models\Deployment;
use App\Models\Site;

class DeploymentReleaseService
{
    public function deployWithSymlink(Site $site, string $branch = 'main'): Deployment
    {
        $releaseDir = '/home/'.$site->username.'/releases/'.now()->format('YmdHis');
        $currentLink = '/home/'.$site->username.'/current';
        $webDir = '/home/'.$site->username.'/web';

        $deployment = Deployment::create([
            'site_id' => $site->site_id,
            'server_id' => $site->server_id,
            'status' => 'running',
            'branch' => $branch,
            'started_at' => now(),
        ]);

        try {
            $daemon = app(DaemonService::class);

            // Create release directory
            $daemon->send('site.exec', ['command' => "mkdir -p {$releaseDir}"]);

            // Clone repo into release directory
            if ($site->repository) {
                $daemon->send('site.exec', [
                    'command' => "git clone --branch ".escapeshellarg($branch)." ".escapeshellarg($site->repository)." {$releaseDir}",
                ]);
            }

            // Run build steps
            $daemon->send('site.exec', ['command' => "cd {$releaseDir} && composer install --no-dev --optimize-autoloader 2>/dev/null || true"]);
            $daemon->send('site.exec', ['command' => "cd {$releaseDir} && npm install && npm run build 2>/dev/null || true"]);

            // Atomic symlink swap
            $daemon->send('site.exec', ['command' => "ln -sfn {$releaseDir} {$currentLink}"]);

            // Update web directory symlink
            $daemon->send('site.exec', ['command' => "rm -f {$webDir} && ln -sf {$currentLink}/public {$webDir}"]);

            $deployment->markAsCompleted(['commit_message' => "Deployed {$branch} to release {$releaseDir}"]);

        } catch (\Throwable $e) {
            $deployment->markAsFailed($e->getMessage());
            $daemon->send('site.exec', ['command' => "rm -rf {$releaseDir}"]);
        }

        return $deployment;
    }

    public function rollback(Site $site, string $releaseDir): Deployment
    {
        $currentLink = '/home/'.$site->username.'/current';
        $webDir = '/home/'.$site->username.'/web';

        $deployment = Deployment::create([
            'site_id' => $site->site_id,
            'server_id' => $site->server_id,
            'status' => 'running',
            'branch' => 'rollback',
            'started_at' => now(),
        ]);

        try {
            $daemon = app(DaemonService::class);
            $daemon->send('site.exec', ['command' => "ln -sfn {$releaseDir} {$currentLink}"]);

            if (is_dir($releaseDir.'/public')) {
                $daemon->send('site.exec', ['command' => "rm -f {$webDir} && ln -sf {$releaseDir}/public {$webDir}"]);
            }

            $deployment->markAsCompleted(['commit_message' => "Rolled back to {$releaseDir}"]);
        } catch (\Throwable $e) {
            $deployment->markAsFailed($e->getMessage());
        }

        return $deployment;
    }
}
