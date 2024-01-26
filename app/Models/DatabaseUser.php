<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
    ];

    public function databases()
    {
        return $this->belongsThrough(Database::class, DatabaseUserLink::class);
    }
}