<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    Route::get('/settings', function () {
        abort_unless(auth()->user()?->can('settings.view'), 403);

        return redirect()->route('settings.section', ['section' => 'general']);
    })->name('settings.general');

    Route::get('/settings/{section}', function (string $section) {
        $allowed = ['general', 'users', 'roles'];
        abort_unless(in_array($section, $allowed), 404);

        $requiredPermission = [
            'general' => 'settings.view',
            'users' => 'user.view',
            'roles' => 'role.view',
        ][$section];

        abort_unless(auth()->user()?->can($requiredPermission), 403);

        return view('settings.manage', compact('section'));
    })->name('settings.section');

    // Legacy named route aliases (kept for backward compatibility)
    Route::get('/settings/users', function () {
        return redirect()->route('settings.section', ['section' => 'users']);
    })->name('settings.users');

    Route::get('/settings/roles', function () {
        return redirect()->route('settings.section', ['section' => 'roles']);
    })->name('settings.roles');

    Route::delete('/settings/user/{userId}/delete', [SettingsController::class, 'users'])
        ->middleware('can:user.delete')
        ->name('settings.users.delete');

});
