<?php
/**
 * Spikster Fail2ban API
 *
 * This file should be placed on the server at: /var/www/html/spikster-api/fail2ban.php
 * It handles all Fail2ban operations locally on the server.
 */

// Security: Only allow requests from Spikster panel
$allowedIps = ['127.0.0.1', '::1']; // Add your panel IP here during deployment
// Uncomment to enable IP whitelisting:
// if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIps)) {
//     http_response_code(403);
//     die(json_encode(['success' => false, 'error' => 'Forbidden']));
// }

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$serverId = $_GET['server_id'] ?? $_POST['server_id'] ?? '';

// Validate server_id (basic security check)
if (empty($serverId)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing server_id']));
}

/**
 * Execute a command with sudo
 */
function execSudo(string $command): array
{
    $output = [];
    $returnCode = 0;
    exec("sudo {$command} 2>&1", $output, $returnCode);

    return [
        'output' => $output,
        'success' => $returnCode === 0,
        'code' => $returnCode,
    ];
}

/**
 * Get all banned IPs from Fail2ban
 */
function getBannedIps(): array
{
    $ips = [];

    // Get list of all jails
    $jailsResult = execSudo('fail2ban-client status');
    if (!$jailsResult['success']) {
        return [];
    }

    // Parse jail list
    $jailList = [];
    foreach ($jailsResult['output'] as $line) {
        if (preg_match('/Jail list:\s+(.+)/', $line, $matches)) {
            $jailList = array_map('trim', explode(',', $matches[1]));
            break;
        }
    }

    // Get banned IPs from each jail
    foreach ($jailList as $jail) {
        $statusResult = execSudo("fail2ban-client status {$jail}");
        if (!$statusResult['success']) {
            continue;
        }

        foreach ($statusResult['output'] as $line) {
            if (preg_match('/Banned IP list:\s+(.+)/', $line, $matches)) {
                $bannedList = array_map('trim', explode(' ', trim($matches[1])));
                foreach ($bannedList as $ip) {
                    if (!empty($ip)) {
                        $ips[] = [
                            'ip' => $ip,
                            'jail' => $jail,
                            'banned_at' => date('Y-m-d H:i:s'), // Approximate
                        ];
                    }
                }
            }
        }
    }

    return $ips;
}

/**
 * Get all Fail2ban jails
 */
function getJails(): array
{
    $jails = [];

    $result = execSudo('fail2ban-client status');
    if (!$result['success']) {
        return [];
    }

    $jailList = [];
    foreach ($result['output'] as $line) {
        if (preg_match('/Jail list:\s+(.+)/', $line, $matches)) {
            $jailList = array_map('trim', explode(',', $matches[1]));
            break;
        }
    }

    foreach ($jailList as $jailName) {
        $statusResult = execSudo("fail2ban-client status {$jailName}");
        if (!$statusResult['success']) {
            continue;
        }

        $currentBanned = 0;
        $totalBanned = 0;

        foreach ($statusResult['output'] as $line) {
            if (preg_match('/Currently banned:\s+(\d+)/', $line, $matches)) {
                $currentBanned = (int)$matches[1];
            }
            if (preg_match('/Total banned:\s+(\d+)/', $line, $matches)) {
                $totalBanned = (int)$matches[1];
            }
        }

        $jails[] = [
            'name' => $jailName,
            'enabled' => true,
            'current_banned' => $currentBanned,
            'total_banned' => $totalBanned,
        ];
    }

    return $jails;
}

/**
 * Get jail status
 */
function getJailStatus(string $jail): array
{
    $result = execSudo("fail2ban-client status {$jail}");
    if (!$result['success']) {
        return [];
    }

    $status = [
        'name' => $jail,
        'filter' => '',
        'actions' => [],
        'current_banned' => 0,
        'total_banned' => 0,
        'current_failed' => 0,
        'total_failed' => 0,
    ];

    foreach ($result['output'] as $line) {
        if (preg_match('/Filter\s+:\s+(.+)/', $line, $matches)) {
            $status['filter'] = trim($matches[1]);
        }
        if (preg_match('/Actions\s+:\s+(.+)/', $line, $matches)) {
            $status['actions'] = array_map('trim', explode(',', $matches[1]));
        }
        if (preg_match('/Currently banned:\s+(\d+)/', $line, $matches)) {
            $status['current_banned'] = (int)$matches[1];
        }
        if (preg_match('/Total banned:\s+(\d+)/', $line, $matches)) {
            $status['total_banned'] = (int)$matches[1];
        }
        if (preg_match('/Currently failed:\s+(\d+)/', $line, $matches)) {
            $status['current_failed'] = (int)$matches[1];
        }
        if (preg_match('/Total failed:\s+(\d+)/', $line, $matches)) {
            $status['total_failed'] = (int)$matches[1];
        }
    }

    return $status;
}

/**
 * Ban an IP in a jail
 */
function banIp(string $ip, string $jail = 'sshd'): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    $result = execSudo("fail2ban-client set {$jail} banip {$ip}");
    return $result['success'];
}

/**
 * Unban an IP from a jail or all jails
 */
function unbanIp(string $ip, ?string $jail = null): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    if ($jail) {
        // Unban from specific jail
        $result = execSudo("fail2ban-client set {$jail} unbanip {$ip}");
        return $result['success'];
    } else {
        // Unban from all jails
        $jails = getJails();
        $success = true;
        foreach ($jails as $j) {
            $result = execSudo("fail2ban-client set {$j['name']} unbanip {$ip}");
            if (!$result['success']) {
                $success = false;
            }
        }
        return $success;
    }
}

/**
 * Get Fail2ban service status
 */
function getServiceStatus(): array
{
    $pingResult = execSudo('fail2ban-client ping');
    $running = $pingResult['success'] && in_array('Server replied: pong', $pingResult['output']);

    $versionResult = execSudo('fail2ban-client version');
    $version = '';
    if ($versionResult['success'] && !empty($versionResult['output'])) {
        $version = trim($versionResult['output'][0]);
    }

    return [
        'running' => $running,
        'version' => $version,
    ];
}

/**
 * Restart Fail2ban service
 */
function restartService(): bool
{
    $result = execSudo('systemctl restart fail2ban');
    return $result['success'];
}

/**
 * Get Fail2ban logs
 */
function getLogs(int $lines = 100): array
{
    $result = execSudo("tail -n {$lines} /var/log/fail2ban.log");
    if (!$result['success']) {
        return [];
    }

    return array_map(function($line) {
        return ['message' => $line, 'timestamp' => date('Y-m-d H:i:s')];
    }, $result['output']);
}

/**
 * Whitelist an IP (add to ignoreip in jail.local)
 */
function whitelistIp(string $ip): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    // Read current jail.local
    $jailLocalPath = '/etc/fail2ban/jail.local';
    if (!file_exists($jailLocalPath)) {
        // Create from jail.conf if doesn't exist
        $result = execSudo("cp /etc/fail2ban/jail.conf {$jailLocalPath}");
        if (!$result['success']) {
            return false;
        }
    }

    $content = file_get_contents($jailLocalPath);

    // Check if IP already whitelisted
    if (strpos($content, $ip) !== false) {
        return true; // Already whitelisted
    }

    // Add IP to ignoreip in [DEFAULT] section
    if (preg_match('/\[DEFAULT\].*?ignoreip\s*=\s*([^\n]+)/s', $content, $matches)) {
        $currentIps = trim($matches[1]);
        $newIps = $currentIps . ' ' . $ip;
        $content = preg_replace('/(ignoreip\s*=\s*)([^\n]+)/', '$1' . $newIps, $content, 1);
    } else {
        // Add ignoreip line to [DEFAULT] section
        $content = preg_replace('/(\[DEFAULT\])/', "$1\nignoreip = 127.0.0.1/8 ::1 {$ip}", $content, 1);
    }

    // Write back
    $tempFile = tempnam(sys_get_temp_dir(), 'fail2ban_');
    file_put_contents($tempFile, $content);
    $result = execSudo("mv {$tempFile} {$jailLocalPath}");

    if ($result['success']) {
        // Reload Fail2ban to apply changes
        execSudo('fail2ban-client reload');
        return true;
    }

    return false;
}

/**
 * Get whitelisted IPs
 */
function getWhitelistedIps(): array
{
    $jailLocalPath = '/etc/fail2ban/jail.local';
    if (!file_exists($jailLocalPath)) {
        return [];
    }

    $content = file_get_contents($jailLocalPath);

    if (preg_match('/\[DEFAULT\].*?ignoreip\s*=\s*([^\n]+)/s', $content, $matches)) {
        $ips = preg_split('/\s+/', trim($matches[1]));
        return array_filter($ips, function($ip) {
            return filter_var($ip, FILTER_VALIDATE_IP);
        });
    }

    return [];
}

/**
 * Add a new jail
 */
function addJail(array $jailConfig): bool
{
    $name = $jailConfig['name'] ?? '';
    $port = $jailConfig['port'] ?? '';
    $logpath = $jailConfig['logpath'] ?? '';
    $maxretry = $jailConfig['maxretry'] ?? 5;
    $bantime = $jailConfig['bantime'] ?? 3600;
    $findtime = $jailConfig['findtime'] ?? 600;

    if (empty($name) || empty($port) || empty($logpath)) {
        return false;
    }

    $jailLocalPath = '/etc/fail2ban/jail.local';

    $jailDefinition = "\n\n[{$name}]\n";
    $jailDefinition .= "enabled = true\n";
    $jailDefinition .= "port = {$port}\n";
    $jailDefinition .= "logpath = {$logpath}\n";
    $jailDefinition .= "maxretry = {$maxretry}\n";
    $jailDefinition .= "bantime = {$bantime}\n";
    $jailDefinition .= "findtime = {$findtime}\n";

    // Append to jail.local
    $tempFile = tempnam(sys_get_temp_dir(), 'fail2ban_');
    file_put_contents($tempFile, $jailDefinition);
    $result = execSudo("cat {$tempFile} >> {$jailLocalPath}");
    unlink($tempFile);

    if ($result['success']) {
        // Reload Fail2ban
        execSudo('fail2ban-client reload');
        return true;
    }

    return false;
}

// Handle the request
try {
    switch ($action) {
        case 'get-banned-ips':
            $ips = getBannedIps();
            echo json_encode(['success' => true, 'ips' => $ips]);
            break;

        case 'get-jails':
            $jails = getJails();
            echo json_encode(['success' => true, 'jails' => $jails]);
            break;

        case 'get-jail-status':
            $jail = $_GET['jail'] ?? $_POST['jail'] ?? '';
            if (empty($jail)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing jail parameter']);
                break;
            }
            $status = getJailStatus($jail);
            echo json_encode(['success' => true, 'status' => $status]);
            break;

        case 'ban-ip':
            $ip = $_POST['ip'] ?? '';
            $jail = $_POST['jail'] ?? 'sshd';
            if (empty($ip)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing IP parameter']);
                break;
            }
            $success = banIp($ip, $jail);
            echo json_encode(['success' => $success]);
            break;

        case 'unban-ip':
            $ip = $_POST['ip'] ?? '';
            $jail = $_POST['jail'] ?? null;
            if (empty($ip)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing IP parameter']);
                break;
            }
            $success = unbanIp($ip, $jail);
            echo json_encode(['success' => $success]);
            break;

        case 'get-service-status':
            $status = getServiceStatus();
            echo json_encode(['success' => true, 'status' => $status]);
            break;

        case 'restart-service':
            $success = restartService();
            echo json_encode(['success' => $success]);
            break;

        case 'get-logs':
            $lines = (int)($_GET['lines'] ?? $_POST['lines'] ?? 100);
            $logs = getLogs($lines);
            echo json_encode(['success' => true, 'logs' => $logs]);
            break;

        case 'whitelist-ip':
            $ip = $_POST['ip'] ?? '';
            if (empty($ip)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing IP parameter']);
                break;
            }
            $success = whitelistIp($ip);
            echo json_encode(['success' => $success]);
            break;

        case 'get-whitelist':
            $whitelist = getWhitelistedIps();
            echo json_encode(['success' => true, 'whitelist' => $whitelist]);
            break;

        case 'add-jail':
            $jailConfig = $_POST['jail_config'] ?? [];
            if (empty($jailConfig)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Missing jail configuration']);
                break;
            }
            $success = addJail($jailConfig);
            echo json_encode(['success' => $success]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
