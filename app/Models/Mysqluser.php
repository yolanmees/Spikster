<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated Use App\Models\DatabaseUser instead.
 *             Legacy model kept for backward compatibility.
 *             Will be removed in a future release.
 */
class Mysqluser extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
