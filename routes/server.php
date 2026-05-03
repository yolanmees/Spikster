<?php

use App\Http\Controllers\LogManagerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    Route::get('/servers', function () {
        return view('server.list');
    })->name('server.list');

    Route::get('/servers/create', function () {
        return view('server.create');
    })->name('server.create');

    Route::get('/servers/{server_id}', function ($server_id) {
        return redirect()->route('server.edit.section', ['server_id' => $server_id, 'section' => 'overview']);
    })->name('server.edit');

    Route::get('/servers/{server_id}/manage/{section}', function ($server_id, $section) {
        $allowed = ['overview', 'monitor', 'information', 'security', 'tools'];
        abort_unless(in_array($section, $allowed), 404);

        return view('server.edit', compact('server_id', 'section'));
    })->name('server.edit.section');

    Route::get('/servers/{server_id}/fail2ban', function ($server_id) {
        $fail2banInstalled = false;
        try {
            $daemon = app(\App\Services\DaemonService::class);
            $result = $daemon->status('fail2ban');
            $fail2banInstalled = str_contains($result, 'active') || str_contains($result, 'running');
        } catch (\Throwable) {}
        return view('server.fail2ban', compact('server_id', 'fail2banInstalled'));
    })->name('server.fail2ban');

    Route::get('/servers/{server_id}/packages', function ($server_id) {
        return view('server.packages-installed', compact('server_id'));
    })->name('server.packages-installed');

    Route::get('/servers/{server_id}/cron', function ($server_id) {
        return view('server.cron', compact('server_id'));
    })->name('server.cron');

    Route::get('/servers/{server_id}/logs', [LogManagerController::class, 'index'])->name('logs.index');
    Route::get('/servers/{server_id}/logs/{log}', [LogManagerController::class, 'show'])->name('logs.show');
    Route::get('/servers/{server_id}/logs/{log}/download', [LogManagerController::class, 'download'])->name('logs.download');
    Route::delete('/servers/{server_id}/logs/{log}', [LogManagerController::class, 'delete'])->name('logs.delete');

});
