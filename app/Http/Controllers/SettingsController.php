<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class SettingsController extends Controller
{
    //

    public function users()
    {
        $users = User::all();
        return view('settings.users', compact('users'));
    }
}
