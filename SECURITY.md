# Security Features Documentation

## Overview

Spikster includes comprehensive security features to protect your VPS control panel against common threats and vulnerabilities.

## Security Layers

### 1. Authentication & Authorization

#### JWT Authentication

-   Access tokens expire after configured time (default: 15 minutes)
-   Refresh tokens expire after configured time (default: 7 days)
-   Tokens are signed with HS256 algorithm
-   Failed login attempts are logged

#### Rate Limiting

Multiple rate limiters protect different endpoints:

-   **API**: 60 requests per minute
-   **Login**: 5 requests per minute
-   **Sensitive operations**: 10 requests per minute
-   **SSH operations**: 30 requests per minute

#### Authorization Policies

-   `ServerPolicy`: Controls server creation, viewing, and deletion
    -   Requires email verification for server creation
    -   Prevents deletion of servers with active sites
-   `SitePolicy`: Controls site operations
    -   Protects panel sites from modification
    -   Requires permissions for SSL management and deployment

### 2. Input Validation & Sanitization

#### Form Request Validation

Dedicated request classes with comprehensive validation:

-   `StoreServerRequest`: Server creation validation (IP, port, password strength)
-   `StoreSiteRequest`: Site creation validation (domain regex, PHP version)
-   `LoginRequest`: Login credentials validation

#### SecurityHelper Utility

Provides sanitization methods:

-   `sanitizeInput()`: XSS prevention
-   `sanitizeSql()`: SQL injection prevention
-   `sanitizeDomain()`: Domain name sanitization
-   `sanitizeFilename()`: File path sanitization
-   `sanitizeSshCommand()`: SSH command injection prevention
-   `isValidIp()`: IP address validation
-   `containsSuspiciousPatterns()`: Malicious pattern detection

### 3. Security Headers

The `SecurityHeaders` middleware adds protective HTTP headers:

```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'
```

### 4. Audit Logging

#### AuditLog Model

Tracks all security-relevant events:

-   User authentication (login, logout, failed attempts)
-   CRUD operations on critical models
-   SSH command executions
-   Permission denials
-   Suspicious activity

#### Logged Events

-   `login`: Successful authentication
-   `login_failed`: Failed login attempts
-   `logout`: User logout
-   `create`: Model creation
-   `update`: Model updates
-   `delete`: Model deletion
-   `security`: Security-related events
-   `ssh_command`: SSH executions
-   `permission_denied`: Authorization failures
-   `suspicious_activity`: Detected threats

#### Severity Levels

-   **info**: Normal operations
-   **warning**: Failed attempts, permission denials
-   **critical**: Security threats, suspicious activity

#### AuditService Methods

```php
// Authentication logging
AuditService::logLogin($userId);
AuditService::logFailedLogin($email);
AuditService::logLogout();

// CRUD logging
AuditService::logCreate($model, $description);
AuditService::logUpdate($model, $oldValues, $description);
AuditService::logDelete($model, $description);

// Security logging
AuditService::logSecurityEvent($description, $model, $severity);
AuditService::logSshCommand($command, $server, $result);
AuditService::logPermissionDenied($action, $model);
AuditService::logSuspiciousActivity($description, $model, $metadata);

// Query methods
AuditService::getRecentLogsForUser($userId, $limit);
AuditService::getRecentLogsForModel($model, $limit);
AuditService::getCriticalEvents($limit);
```

### 5. Threat Detection

#### LogSecurityEvents Middleware

Automatically detects and logs:

-   SQL injection attempts
-   XSS attacks
-   Path traversal attempts
-   Command injection attempts
-   Permission denials (403 responses)

Suspicious patterns include:

-   SQL keywords: `union`, `select`, `insert`, `update`, `delete`, `drop`
-   XSS vectors: `<script`, `javascript:`, `onerror=`, `onload=`
-   Path traversal: `../`, `..\`
-   Command injection: `&&`, `||`, `;`, backticks

### 6. CSRF Protection

CSRF protection is enabled by default for all web routes via `VerifyCsrfToken` middleware.

### 7. Configuration

All security settings are centralized in `config/security.php`:

```php
return [
    // Password requirements
    'password' => [
        'min_length' => 12,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
    ],

    // Session security
    'session' => [
        'lifetime' => 120, // minutes
        'idle_timeout' => 30, // minutes
        'secure_cookies' => true,
        'http_only' => true,
    ],

    // JWT settings
    'jwt' => [
        'access_token_lifetime' => 900, // 15 minutes
        'refresh_token_lifetime' => 604800, // 7 days
    ],

    // SSH security
    'ssh' => [
        'allowed_commands' => ['ls', 'cd', 'cat', 'tail', 'grep', 'php'],
        'blocked_patterns' => ['rm -rf', 'dd if=', 'mkfs', ':(){:|:&};:'],
        'command_timeout' => 30, // seconds
    ],

    // File upload restrictions
    'upload' => [
        'max_size' => 10485760, // 10MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip'],
        'scan_for_viruses' => false,
    ],

    // IP whitelist/blacklist
    'ip_filtering' => [
        'enabled' => false,
        'whitelist' => [],
        'blacklist' => [],
    ],

    // Audit logging
    'audit' => [
        'enabled' => true,
        'retention_days' => 90,
        'log_queries' => false,
    ],

    // Brute force protection
    'brute_force' => [
        'max_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'track_ip' => true,
    ],

    // Two-factor authentication
    '2fa' => [
        'enabled' => false,
        'required_for_admins' => true,
    ],

    // Content Security Policy
    'csp' => [
        'default-src' => "'self'",
        'script-src' => "'self' 'unsafe-inline' 'unsafe-eval'",
        'style-src' => "'self' 'unsafe-inline'",
        'img-src' => "'self' data: https:",
        'font-src' => "'self' data:",
    ],

    // Security headers
    'headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ],
];
```

## Maintenance

### Audit Log Cleanup

Old audit logs are automatically cleaned up weekly:

```bash
php artisan audit:cleanup --days=90
```

Scheduled in `app/Console/Kernel.php`:

```php
$schedule->command('audit:cleanup')->weekly()->sundays()->at('02:00');
```

### Manual Cleanup

```bash
# Clean logs older than 30 days
php artisan audit:cleanup --days=30

# Clean logs older than 180 days
php artisan audit:cleanup --days=180
```

## Testing

Security features are tested in `tests/Feature/SecurityTest.php`:

```bash
php artisan test --filter SecurityTest
```

Tests include:

-   Security headers presence
-   CSRF protection
-   Rate limiting
-   Audit logging for various events
-   Suspicious activity detection
-   Audit log cleanup

## Best Practices

1. **Enable HTTPS**: Always use HTTPS in production
2. **Strong Passwords**: Enforce strong password requirements in config
3. **Regular Audits**: Review audit logs regularly for suspicious activity
4. **Update Dependencies**: Keep all packages up to date
5. **Monitor Rate Limits**: Adjust rate limits based on usage patterns
6. **IP Filtering**: Enable IP whitelisting for admin access
7. **2FA**: Enable two-factor authentication for admin accounts
8. **Backup Audit Logs**: Include audit logs in backup strategy
9. **Log Rotation**: Clean up old logs to prevent database bloat
10. **Security Reviews**: Regularly review and update security configurations

## Monitoring

### View Critical Events

```php
use App\Services\AuditService;

// Get last 100 critical events
$criticalEvents = AuditService::getCriticalEvents(100);

// Get recent logs for a user
$userLogs = AuditService::getRecentLogsForUser($userId, 50);

// Get recent logs for a server
$serverLogs = AuditService::getRecentLogsForModel($server, 50);
```

### Database Queries

```sql
-- Failed login attempts in last 24 hours
SELECT * FROM audit_logs
WHERE event_type = 'login_failed'
AND created_at > NOW() - INTERVAL 24 HOUR;

-- Suspicious activity
SELECT * FROM audit_logs
WHERE event_type = 'suspicious_activity'
ORDER BY created_at DESC
LIMIT 50;

-- User activity summary
SELECT user_id, event_type, COUNT(*) as count
FROM audit_logs
GROUP BY user_id, event_type;
```

## Compliance

These security features help meet compliance requirements:

-   **GDPR**: Audit trails for data access
-   **PCI DSS**: Strong authentication and logging
-   **HIPAA**: Access controls and audit logs
-   **SOC 2**: Security monitoring and incident response

## Emergency Response

If you detect a security breach:

1. Check critical events: `AuditService::getCriticalEvents()`
2. Review failed login attempts
3. Check for suspicious activity patterns
4. Disable compromised accounts
5. Rotate JWT secrets in `.env`
6. Review and update IP blacklist
7. Force password resets if necessary
8. Update security configurations

## Support

For security questions or to report vulnerabilities, contact the development team.
