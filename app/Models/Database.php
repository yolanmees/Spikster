<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Database extends Model
{
    use HasFactory;

    protected $fillable = [
        'database_name',
    ];

    public function users()
    {
        return $this->belongsToMany(DatabaseUser::class, 'database_user_links', 'database_id', 'database_user_id');
    }
}