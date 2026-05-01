<?php

use Illuminate\Support\Facades\Route;
use Modules\Greeter\Http\Controllers\GreeterController;

Route::get('/', [GreeterController::class, 'index'])->name('index')->middleware('can:greeter.view');
