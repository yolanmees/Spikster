<?php

namespace App\Services;

class DrRunbookService
{
    public function getRunbooks(): array
    {
        return [
            'server_failure' => [
                'title' => 'Server Failure',
                'severity' => 'P1',
                'steps' => [
                    'Verify server is down via ping and SSH',
                    'Check provider dashboard for outages',
                    'If provider issue, wait for resolution',
                    'If isolated failure, attempt recovery via provider console',
                    'If unrecoverable, provision new server from backup',
                    'Update DNS to point to recovery server',
                    'Verify all services are operational',
                ],
                'estimated_rt' => '30min - 4hrs',
            ],
            'database_corruption' => [
                'title' => 'Database Corruption',
                'severity' => 'P1',
                'steps' => [
                    'Stop the application to prevent further corruption',
                    'Take a filesystem-level backup of /var/lib/mysql',
                    'Attempt mysqlcheck --auto-repair',
                    'If repair fails, restore from latest backup',
                    'Verify data integrity after restore',
                    'Start application and monitor',
                ],
                'estimated_rt' => '15min - 2hrs',
            ],
            'security_breach' => [
                'title' => 'Security Breach',
                'severity' => 'P1',
                'steps' => [
                    'Isolate affected server from network (remove from LB, update firewall)',
                    'Take forensic snapshot of server',
                    'Rotate all credentials (passwords, API keys, SSH keys)',
                    'Analyze logs for entry point',
                    'Patch vulnerability',
                    'Restore from clean backup',
                    'Notify affected users if data was compromised',
                ],
                'estimated_rt' => '1hr - 8hrs',
            ],
            'ssl_certificate_expiry' => [
                'title' => 'SSL Certificate Expiry',
                'severity' => 'P2',
                'steps' => [
                    'Verify certificate expiry via SslCertificateService',
                    'Run CertificateRenewalService::renewCertificate',
                    'Verify new cert via openssl s_client',
                    'Check all associated domains and aliases',
                    'Update monitoring alert thresholds',
                ],
                'estimated_rt' => '5min - 30min',
            ],
            'disk_space_critical' => [
                'title' => 'Disk Space Critical',
                'severity' => 'P2',
                'steps' => [
                    'Identify largest directories: du -sh /* | sort -rh | head -20',
                    'Clean package cache: apt-get clean',
                    'Rotate and compress logs: logrotate -f',
                    'Remove old backups via retention policy',
                    'Clear old metrics via ServerMetric::cleanupOldMetrics',
                    'If still critical, add additional storage',
                ],
                'estimated_rt' => '15min - 1hr',
            ],
            'email_service_down' => [
                'title' => 'Email Service Down',
                'severity' => 'P2',
                'steps' => [
                    'Check Postfix: systemctl status postfix',
                    'Check Dovecot: systemctl status dovecot',
                    'Review mail log: tail -100 /var/log/mail.log',
                    'Verify disk space for mail spool',
                    'Restart services: systemctl restart postfix dovecot',
                    'Flush queue: postqueue -f',
                ],
                'estimated_rt' => '10min - 1hr',
            ],
            'panel_unreachable' => [
                'title' => 'Panel Unreachable',
                'severity' => 'P1',
                'steps' => [
                    'Check Docker container status: docker ps',
                    'Check container logs: docker logs spikster-app --tail 100',
                    'Verify nginx is running: systemctl status nginx',
                    'Restart app container: docker-compose restart app',
                    'Clear cache: php artisan optimize:clear',
                    'Check database connectivity: php artisan db:monitor',
                ],
                'estimated_rt' => '10min - 30min',
            ],
        ];
    }

    public function getP1P2P3Criteria(): array
    {
        return [
            'P1 - Critical' => [
                'description' => 'Complete service outage affecting all users',
                'response_time' => '15 minutes',
                'resolution_time' => '4 hours',
                'examples' => ['Panel down', 'All sites down', 'Data loss', 'Security breach'],
            ],
            'P2 - High' => [
                'description' => 'Partial outage or degradation affecting some users',
                'response_time' => '1 hour',
                'resolution_time' => '8 hours',
                'examples' => ['Email down', 'SSL expiry', 'High resource usage', 'Single server down'],
            ],
            'P3 - Medium' => [
                'description' => 'Non-critical issue with workaround available',
                'response_time' => '4 hours',
                'resolution_time' => '48 hours',
                'examples' => ['Minor UI bug', 'Feature not working for edge case', 'Documentation error'],
            ],
        ];
    }

    public function getIncidentResponseProcess(): array
    {
        return [
            'detect' => [
                'Monitor alerting system',
                'User reports issue',
                'Automated health check fails',
            ],
            'triage' => [
                'Determine severity (P1/P2/P3)',
                'Assign incident owner',
                'Create incident ticket',
            ],
            'respond' => [
                'Follow relevant runbook',
                'Update status page',
                'Communicate to stakeholders',
            ],
            'resolve' => [
                'Apply fix',
                'Verify resolution',
                'Update status page to resolved',
            ],
            'postmortem' => [
                'Root cause analysis',
                'Preventative measures',
                'Update runbooks if needed',
            ],
        ];
    }

    public function getStatusPageData(): array
    {
        return [
            'panel' => $this->checkEndpoint(config('app.url').'/api/health'),
            'services' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'queue' => $this->checkQueue(),
            ],
            'uptime' => [
                'panel_uptime' => $this->getPanelUptime(),
            ],
        ];
    }

    protected function checkEndpoint(string $url): array
    {
        $start = microtime(true);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $latency = (microtime(true) - $start) * 1000;
        curl_close($ch);

        return [
            'status' => $httpCode >= 200 && $httpCode < 500 ? 'up' : 'down',
            'latency_ms' => round($latency, 0),
            'http_code' => $httpCode,
            'last_checked' => now()->toIso8601String(),
        ];
    }

    protected function checkDatabase(): array
    {
        try {
            \DB::select('SELECT 1');
            return ['status' => 'up', 'latency_ms' => 0];
        } catch (\Throwable) {
            return ['status' => 'down', 'error' => 'Connection failed'];
        }
    }

    protected function checkCache(): array
    {
        try {
            \Cache::get('health-check');
            return ['status' => 'up'];
        } catch (\Throwable) {
            return ['status' => 'down', 'error' => 'Cache unavailable'];
        }
    }

    protected function checkQueue(): array
    {
        try {
            $failed = \DB::table('failed_jobs')->count();
            return ['status' => 'up', 'failed_jobs' => $failed];
        } catch (\Throwable) {
            return ['status' => 'down', 'error' => 'Queue unavailable'];
        }
    }

    protected function getPanelUptime(): string
    {
        $uptime = @file_get_contents('/proc/uptime');
        if ($uptime) {
            $seconds = (int) floor((float) explode(' ', $uptime)[0]);
            $days = floor($seconds / 86400);
            return "{$days}d";
        }
        return 'N/A';
    }
}
