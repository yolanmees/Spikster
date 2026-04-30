<?php

use App\Http\Controllers\ShellController;
use Illuminate\Support\Facades\Route;

Route::get('/setup/{server_id}', [ShellController::class, 'setup']);
Route::get('/deploy/{site_id}', [ShellController::class, 'deploy']);
Route::get('/servers/rootreset', [ShellController::class, 'serversrootreset']);
