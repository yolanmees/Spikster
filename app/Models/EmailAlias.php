<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailAlias extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email_account_id',
        'alias',
    ];

    /**
     * Get the email account that owns the alias.
     */
    public function emailAccount(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class);
    }

    /**
     * Get the alias username (part before @).
     */
    public function getUsernameAttribute(): string
    {
        return explode('@', $this->alias)[0];
    }

    /**
     * Get the alias domain (part after @).
     */
    public function getDomainAttribute(): string
    {
        return explode('@', $this->alias)[1];
    }
}
