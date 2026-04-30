<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetupToken extends Model
{
    protected $fillable = ['token', 'used', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime', 'used' => 'boolean'];

    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }
}
