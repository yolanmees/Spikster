<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogManagerController;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified' ])->group(function () {

    Route::get('/servers', function () {
        return view('server.list');
    })->name('server.list');

    Route::get('/servers/{server_id}', function ($server_id) {
        return view('server.edit', compact('server_id'));
    })->name('server.edit');

    Route::get('/servers/{server_id}/fail2ban', function ($server_id) {
        return view('server.fail2ban', compact('server_id'));
    })->name('server.fail2ban');

    Route::get('/servers/{server_id}/packages', function ($server_id) {
        return view('server.packages-installed', compact('server_id'));
    })->name('server.packages-installed');

    Route::get('/servers/{server_id}/logs', [LogManagerController::class, 'index'])->name('logs');
    Route::get('/servers/{server_id}/logs/{log}', [LogManagerController::class, 'show'])->name('logs.show');

});