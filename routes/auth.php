<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/', [AuthController::class, 'login'])->middleware('throttle:login');
Route::get('/', [AuthController::class, 'refresh'])->middleware('throttle:10,1');
Route::patch('/', [AuthController::class, 'update'])->middleware('throttle:sensitive');
Route::delete('/', [AuthController::class, 'logout']);
