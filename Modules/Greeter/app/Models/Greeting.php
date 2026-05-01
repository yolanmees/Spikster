<?php

namespace Modules\Greeter\Models;

use Illuminate\Database\Eloquent\Model;

class Greeting extends Model
{
    protected $fillable = ['name', 'message'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
