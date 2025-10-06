<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class User2FASetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'is_enabled',
        'secret_key',
        'enabled_at',
        'last_verified_at',
        'recovery_email',
        'email_2fa_enabled',
        'failed_attempts',
        'locked_until',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'email_2fa_enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'locked_until' => 'datetime',
        'failed_attempts' => 'integer',
    ];

    protected $hidden = [
        'secret_key',
    ];

    /**
     * Get the user that owns the 2FA settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the decrypted secret key.
     */
    public function getDecryptedSecretKeyAttribute(): ?string
    {
        if ($this->secret_key === null) {
            return null;
        }

        try {
            return Crypt::decryptString($this->secret_key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set the secret key with encryption.
     */
    public function setSecretKeyAttribute(?string $value): void
    {
        $this->attributes['secret_key'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Check if the account is currently locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Increment failed attempts and lock if necessary.
     */
    public function incrementFailedAttempts(): void
    {
        $this->increment('failed_attempts');

        if ($this->failed_attempts >= 5) {
            $this->update([
                'locked_until' => now()->addMinutes(30),
            ]);
        }
    }

    /**
     * Reset failed attempts after successful verification.
     */
    public function resetFailedAttempts(): void
    {
        $this->update([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_verified_at' => now(),
        ]);
    }
}
