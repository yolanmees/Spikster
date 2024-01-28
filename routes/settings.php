<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified' ])->group(function () {

    Route::get('/settings', function () {
        return view('settings.settings');
    })->name('settings.general');

    Route::get('/settings/users', function () {
        return view('settings.users');
    })->name('settings.users');

});