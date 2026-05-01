<?php

namespace App\Http\Controllers;

use App\Models\SetupToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetupController extends Controller
{
    /**
     * Show setup form if token is valid.
     */
    public function show(string $token)
    {
        $setup = SetupToken::where('token', $token)->first();

        if (! $setup || ! $setup->isValid()) {
            abort(404);
        }

        return view('setup.index', compact('token'));
    }

    /**
     * Handle setup form submission.
     */
    public function complete(Request $request, string $token)
    {
        $setup = SetupToken::where('token', $token)->first();

        if (! $setup || ! $setup->isValid()) {
            abort(404);
        }

        $request->validate([
            'name' => 'required|min:2|max:64',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Update the admin user
        $user = User::where('email', 'administrator@localhost')->first();
        if ($user) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
        }

        // Invalidate token — one time use
        $setup->update(['used' => true]);

        return redirect('/login')->with('status', 'Setup complete! You can now log in.');
    }

    /**
     * Generate a new setup token (called from artisan / go.sh).
     */
    public static function generateToken(): string
    {
        // Invalidate any previous unused tokens
        SetupToken::where('used', false)->delete();

        $token = Str::random(48);

        SetupToken::create([
            'token' => $token,
            'used' => false,
            'expires_at' => now()->addHours(24),
        ]);

        return $token;
    }
}
