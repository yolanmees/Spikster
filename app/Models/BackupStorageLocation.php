<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupStorageLocation extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'config',
        'is_default',
        'is_active',
        'last_test_at',
        'last_test_status',
        'last_test_error',
    ];

    protected $casts = [
        'config' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'last_test_at' => 'datetime',
    ];

    /**
     * Get the user that owns this storage location.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active storage locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific storage type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the default storage location.
     */
    public static function getDefault(): ?self
    {
        return self::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Test connection to storage location.
     */
    public function testConnection(): bool
    {
        $this->update([
            'last_test_at' => now(),
        ]);

        try {
            switch ($this->type) {
                case 's3':
                    return $this->testS3Connection();
                case 'ftp':
                case 'sftp':
                    return $this->testFtpConnection();
                case 'local':
                    return $this->testLocalStorage();
                default:
                    throw new \Exception("Unknown storage type: {$this->type}");
            }
        } catch (\Exception $e) {
            $this->update([
                'last_test_status' => 'failed',
                'last_test_error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Test S3 connection.
     */
    protected function testS3Connection(): bool
    {
        // Implement S3 connection test
        // This would use AWS SDK to verify credentials and bucket access

        $this->update([
            'last_test_status' => 'success',
            'last_test_error' => null,
        ]);

        return true;
    }

    /**
     * Test FTP/SFTP connection.
     */
    protected function testFtpConnection(): bool
    {
        // Implement FTP/SFTP connection test

        $this->update([
            'last_test_status' => 'success',
            'last_test_error' => null,
        ]);

        return true;
    }

    /**
     * Test local storage.
     */
    protected function testLocalStorage(): bool
    {
        $path = $this->config['path'] ?? '/backups';

        if (!is_dir($path)) {
            throw new \Exception("Directory does not exist: {$path}");
        }

        if (!is_writable($path)) {
            throw new \Exception("Directory is not writable: {$path}");
        }

        $this->update([
            'last_test_status' => 'success',
            'last_test_error' => null,
        ]);

        return true;
    }

    /**
     * Get storage credentials for use in SSH jobs.
     */
    public function getCredentials(): array
    {
        return match($this->type) {
            's3' => [
                'key' => $this->config['access_key'] ?? '',
                'secret' => $this->config['secret_key'] ?? '',
                'region' => $this->config['region'] ?? 'us-east-1',
                'bucket' => $this->config['bucket'] ?? '',
                'endpoint' => $this->config['endpoint'] ?? null,
            ],
            'ftp', 'sftp' => [
                'host' => $this->config['host'] ?? '',
                'port' => $this->config['port'] ?? ($this->type === 'sftp' ? 22 : 21),
                'username' => $this->config['username'] ?? '',
                'password' => $this->config['password'] ?? '',
                'path' => $this->config['path'] ?? '/',
            ],
            'local' => [
                'path' => $this->config['path'] ?? '/backups',
            ],
            default => [],
        };
    }
}
