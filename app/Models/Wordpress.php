<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wordpress extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'username',
        'password',
        'site_id',
        'database_id',
        'database_user_id',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function database()
    {
        return $this->belongsTo(Database::class);
    }
}
