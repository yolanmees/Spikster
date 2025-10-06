<?php

use Illuminate\Support\Facades\Route;
use Modules\WordPress\Http\Controllers\WordPressController;

Route::middleware(['auth', 'verified'])->prefix('wordpress')->name('wordpress.')->group(function () {
    Route::get('/', [WordPressController::class, 'index'])->name('index');
    Route::get('/create', [WordPressController::class, 'create'])->name('create');
    Route::post('/', [WordPressController::class, 'store'])->name('store');
    Route::get('/{id}', [WordPressController::class, 'show'])->name('show');
    Route::delete('/{id}', [WordPressController::class, 'destroy'])->name('destroy');
    
    // WP-CLI Actions
    Route::post('/{id}/sync-themes', [WordPressController::class, 'syncThemes'])->name('sync-themes');
    Route::post('/{id}/sync-plugins', [WordPressController::class, 'syncPlugins'])->name('sync-plugins');
    Route::post('/{id}/check-updates', [WordPressController::class, 'checkUpdates'])->name('check-updates');
});
