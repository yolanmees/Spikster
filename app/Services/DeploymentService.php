<?php

namespace App\Services;

use App\Models\Site;

/**
 * Deployment Service
 *
 * Handles site deployment operations.
 */
class DeploymentService
{
    public function __construct(
        protected SSHService $sshService
    ) {}

    /**
     * Deploy a site from Git repository.
     */
    public function deploySite(Site $site): array
    {
        if (! $site->hasRepository()) {
            throw new \Exception('Site does not have a repository configured');
        }

        $server = $site->server;
        $sitePath = "/home/{$site->username}/{$site->domain}";

        $steps = [
            'pull_code' => false,
            'install_dependencies' => false,
            'run_migrations' => false,
            'clear_cache' => false,
            'restart_services' => false,
        ];

        try {
            // Pull latest code
            $this->sshService->executeCommand(
                $server,
                "cd {$sitePath} && git pull origin {$site->branch}"
            );
            $steps['pull_code'] = true;

            // Install Composer dependencies if composer.json exists
            $composerCheck = $this->sshService->executeCommand(
                $server,
                "test -f {$sitePath}/composer.json && echo 'exists' || echo 'not found'"
            );

            if (str_contains($composerCheck, 'exists')) {
                $this->sshService->executeCommand(
                    $server,
                    "cd {$sitePath} && composer install --no-dev --optimize-autoloader"
                );
                $steps['install_dependencies'] = true;
            }

            // Run Laravel migrations if artisan exists
            $artisanCheck = $this->sshService->executeCommand(
                $server,
                "test -f {$sitePath}/artisan && echo 'exists' || echo 'not found'"
            );

            if (str_contains($artisanCheck, 'exists')) {
                $this->sshService->executeCommand(
                    $server,
                    "cd {$sitePath} && php artisan migrate --force"
                );
                $steps['run_migrations'] = true;

                // Clear Laravel cache
                $this->sshService->executeCommand(
                    $server,
                    "cd {$sitePath} && php artisan cache:clear && php artisan config:clear && php artisan view:clear"
                );
                $steps['clear_cache'] = true;
            }

            // Install NPM dependencies if package.json exists
            $npmCheck = $this->sshService->executeCommand(
                $server,
                "test -f {$sitePath}/package.json && echo 'exists' || echo 'not found'"
            );

            if (str_contains($npmCheck, 'exists')) {
                $this->sshService->executeCommand(
                    $server,
                    "cd {$sitePath} && npm install && npm run build"
                );
            }

            // Restart PHP-FPM for the site's PHP version
            $this->sshService->restartService($server, "php{$site->php}-fpm");
            $steps['restart_services'] = true;

            // Change ownership
            $this->sshService->changeOwnership($server, $sitePath, $site->username, $site->username);

            return [
                'success' => true,
                'steps' => $steps,
                'message' => 'Deployment completed successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'steps' => $steps,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clone a Git repository to a site.
     */
    public function cloneRepository(Site $site): bool
    {
        if (! $site->hasRepository()) {
            throw new \Exception('Site does not have a repository configured');
        }

        $server = $site->server;
        $sitePath = "/home/{$site->username}/{$site->domain}";

        // Remove existing directory
        $this->sshService->deleteDirectory($server, $sitePath);

        // Clone repository
        $this->sshService->executeCommand(
            $server,
            "git clone {$site->repository} {$sitePath}"
        );

        // Checkout specific branch
        if ($site->branch && $site->branch !== 'main' && $site->branch !== 'master') {
            $this->sshService->executeCommand(
                $server,
                "cd {$sitePath} && git checkout {$site->branch}"
            );
        }

        // Change ownership
        $this->sshService->changeOwnership($server, $sitePath, $site->username, $site->username);

        return true;
    }

    /**
     * Run custom deployment script.
     */
    public function runCustomScript(Site $site, string $script): string
    {
        $server = $site->server;
        $sitePath = "/home/{$site->username}/{$site->domain}";

        // Create temporary script file
        $scriptPath = "/tmp/deploy_{$site->site_id}.sh";

        // Write script to server
        $this->sshService->executeCommand(
            $server,
            "echo '{$script}' > {$scriptPath} && chmod +x {$scriptPath}"
        );

        // Execute script
        $output = $this->sshService->executeCommand(
            $server,
            "cd {$sitePath} && {$scriptPath}"
        );

        // Cleanup
        $this->sshService->executeCommand($server, "rm {$scriptPath}");

        return $output;
    }

    /**
     * Get deployment status/history.
     */
    public function getDeploymentHistory(Site $site): array
    {
        $server = $site->server;
        $sitePath = "/home/{$site->username}/{$site->domain}";

        // Get last 10 git commits
        $output = $this->sshService->executeCommand(
            $server,
            "cd {$sitePath} && git log --oneline -10"
        );

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

    /**
     * Rollback to a previous commit.
     */
    public function rollback(Site $site, string $commitHash): bool
    {
        $server = $site->server;
        $sitePath = "/home/{$site->username}/{$site->domain}";

        $this->sshService->executeCommand(
            $server,
            "cd {$sitePath} && git reset --hard {$commitHash}"
        );

        // Run deployment steps again
        $this->deploySite($site);

        return true;
    }
}
