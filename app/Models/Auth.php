<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * @deprecated Use App\Models\User with Sanctum authentication instead.
 *             This model exists only for legacy Cipi API compatibility.
 *             Will be removed in a future release.
 */
class Auth extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'apikey',
        'jwt',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'jwt',
    ];

    public static function attempt($username, $password)
    {
        $user = self::where('username', $username)->first();

        if ($user) {
            if (Hash::check($password, $user->password)) {
                return $user;
            }
        }
    }

    public static function check($username, $jwt)
    {
        return self::where('username', $username)
            ->where('jwt', $jwt)
            ->first();
    }
}
