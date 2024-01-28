<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified' ])->group(function () {

    Route::get('/settings', function () {
        return view('settings.settings');
    })->name('settings.general');

    Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users');
    Route::delete('/settings/user/{userId}/delete', [SettingsController::class, 'users'])->name('settings.users.delete');

});