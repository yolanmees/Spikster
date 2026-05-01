<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the user's 2FA settings.
     */
    public function twoFactorSettings()
    {
        return $this->hasOne(User2FASetting::class);
    }

    /**
     * Get the user's backup codes.
     */
    public function backupCodes()
    {
        return $this->hasMany(BackupCode::class);
    }

    /**
     * Get the user's trusted devices.
     */
    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class);
    }

    /**
     * Get the user's 2FA audit logs.
     */
    public function twoFactorAuditLogs()
    {
        return $this->hasMany(TwoFactorAuditLog::class);
    }

    /**
     * Get the user's site access grants.
     */
    public function siteAccess()
    {
        return $this->hasMany(UserSiteAccess::class);
    }

    /**
     * Get sites the user has access to.
     */
    public function accessibleSites()
    {
        return $this->belongsToMany(Site::class, 'user_site_access')
            ->withPivot('access_level', 'granted_by', 'granted_at', 'expires_at')
            ->wherePivot(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Get permission audit logs for this user.
     */
    public function permissionAuditLogs()
    {
        return $this->hasMany(PermissionAuditLog::class);
    }
}
