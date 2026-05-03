<?php

namespace App\Services;

use App\Models\Server;
use App\Models\Site;

/**
 * Deployment Service
 *
 * Handles site deployment operations via the Go daemon.
 */
class DeploymentService
{
    public function __construct(
        protected RemoteDaemonService $daemon
    ) {}

    protected function validateBranch(string $branch): string
    {
        if (! preg_match('/^[a-zA-Z0-9\/\-_\.]+$/', $branch)) {
            throw new \InvalidArgumentException("Invalid branch name: {$branch}");
        }

        return $branch;
    }

    protected function validateRepositoryUrl(string $url): string
    {
        if (! preg_match('/^(https?:\/\/|git:\/\/|git@|ssh:\/\/)[\w\.\/\-_:@]+(\.git)?$/', $url)) {
            throw new \InvalidArgumentException("Invalid or unsafe repository URL: {$url}");
        }

        return $url;
    }

    public function deploySite(Site $site, bool $dryRun = false): array
    {
        if (! $site->hasRepository()) {
            throw new \Exception('Site does not have a repository configured');
        }

        $server = $site->server;

        if ($dryRun) {
            return [
                'success' => true,
                'dry_run' => true,
                'message' => 'Dry run completed',
                'commands' => [
                    "git pull origin {$site->branch}",
                    "composer install --no-dev --optimize-autoloader",
                    "php artisan migrate --force",
                    "php artisan cache:clear",
                    "npm install && npm run build",
                    "systemctl reload php{$site->php}-fpm",
                    "chown {$site->username}:{$site->username}",
                ],
            ];
        }

        try {
            $result = $this->daemon->deploySite($server, [
                'username' => $site->username,
                'repo_url' => $this->validateRepositoryUrl($site->repository),
                'branch' => $this->validateBranch($site->branch ?? 'main'),
                'php' => $site->php ?? '8.3',
                'composer' => 'true',
                'npm' => 'true',
                'artisan_migrate' => 'true',
                'artisan_cache' => 'true',
            ]);

            return [
                'success' => $result['success'] ?? false,
                'message' => $result['output'] ?? 'Deployment completed',
                'error' => $result['error'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function cloneRepository(Site $site): bool
    {
        if (! $site->hasRepository()) {
            throw new \Exception('Site does not have a repository configured');
        }

        $server = $site->server;
        $sitePath = "/home/{$site->username}/git";

        $this->daemon->deleteDirectory($server, $sitePath);

        $result = $this->daemon->deploySite($server, [
            'username' => $site->username,
            'repo_url' => $this->validateRepositoryUrl($site->repository),
            'branch' => $this->validateBranch($site->branch ?? 'main'),
            'php' => $site->php ?? '8.3',
            'composer' => 'false',
            'npm' => 'false',
            'artisan_migrate' => 'false',
            'artisan_cache' => 'false',
        ]);

        return $result['success'] ?? false;
    }

    public function runCustomScript(Site $site, string $script): string
    {
        $server = $site->server;
        $scriptPath = "/tmp/deploy_{$site->site_id}.sh";

        $this->daemon->uploadFile($server, $scriptPath, $script);

        $result = $this->daemon->send($server, 'site.deploy-script', [
            'username' => $site->username,
            'content' => $script,
        ]);

        return $result['output'] ?? '';
    }

    public function getDeploymentHistory(Site $site): array
    {
        $server = $site->server;

        $result = $this->daemon->deployHistory($server, $site->username, 10);

        if (! ($result['success'] ?? false)) {
            return [];
        }

        $output = $result['output'] ?? '';
        $commits = [];
        $lines = explode("\n", trim($output));

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $parts = explode(' ', $line, 2);
            $commits[] = [
                'hash' => $parts[0] ?? '',
                'message' => $parts[1] ?? '',
            ];
        }

        return $commits;
    }

    public function rollback(Site $site, string $commitHash): bool
    {
        if (! preg_match('/^[0-9a-f]{7,40}$/i', $commitHash)) {
            throw new \InvalidArgumentException('Invalid commit hash format');
        }

        $server = $site->server;

        $result = $this->daemon->rollbackDeploy($server, $site->username, $commitHash);

        return $result['success'] ?? false;
    }
}
