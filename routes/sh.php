<?php

use App\Http\Controllers\ShellController;
use Illuminate\Support\Facades\Route;

// Server bootstrap script. Consumed by the server admin on a fresh VPS, so it
// cannot require a panel login. Protected with a signed, expiring URL instead:
// the panel API returns a temporary signed link (24h TTL) when a server is
// created. Requests without a valid signature get 403.
Route::get('/setup/{server_id}', [ShellController::class, 'setup'])
    ->name('sh.setup')
    ->middleware('signed');
