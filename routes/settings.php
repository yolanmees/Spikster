<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    Route::get('/settings', function () {
        return view('settings.settings');
    })->name('settings.general');

    // User Management Routes
    Route::get('/settings/users', function () {
        return view('settings.users-management');
    })->name('settings.users');

    // Role Management Routes
    Route::get('/settings/roles', function () {
        return view('settings.roles-management');
    })->name('settings.roles');

    // Legacy routes (kept for backward compatibility)
    Route::delete('/settings/user/{userId}/delete', [SettingsController::class, 'users'])->name('settings.users.delete');

});
