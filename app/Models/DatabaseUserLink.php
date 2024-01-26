<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatabaseUserLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'database_user_id',
        'database_id',
    ];

    public function user()
    {
        return $this->belongsTo(DatabaseUser::class, 'database_user_id');
    }

    public function database()
    {
        return $this->belongsTo(Database::class, 'database_id');
    }
}
