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

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'encrypted',
    ];

    public function databases()
    {
        return $this->belongsToMany(Database::class, 'database_user_links', 'database_user_id', 'database_id');
    }
}
