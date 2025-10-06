<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BackupCode extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'code',
        'code_hash',
        'is_used',
        'used_at',
        'used_ip',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'used_at' => 'datetime',
    ];

    protected $hidden = [
        'code_hash',
    ];

    /**
     * Get the user that owns the backup code.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new backup code in format ABCD-1234.
     */
    public static function generateCode(): string
    {
        return strtoupper(Str::random(4)) . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Hash the code using Bcrypt.
     */
    public static function hashCode(string $code): string
    {
        return Hash::make($code);
    }

    /**
     * Verify a code against the hash.
     */
    public function verifyCode(string $code): bool
    {
        return Hash::check($code, $this->code_hash);
    }

    /**
     * Mark the code as used.
     */
    public function markAsUsed(string $ipAddress): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
            'used_ip' => $ipAddress,
        ]);
    }

    /**
     * Scope to only unused codes.
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }

    /**
     * Scope to only used codes.
     */
    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }
}
