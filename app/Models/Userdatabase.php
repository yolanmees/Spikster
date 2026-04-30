<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdatabase extends Model
{
    use HasFactory;

    protected $fillable = [
        'mysqluser_id',
        'database_name',
        'server_id',
    ];

    protected $casts = [
        'mysqluser_id' => 'integer',
        'server_id' => 'integer',
    ];

    public function mysqluser()
    {
        return $this->belongsTo(Mysqluser::class, 'mysqluser_id', 'id');
    }
}
