<?php

use Illuminate\Support\Facades\Route;
use Modules\WordPress\Http\Controllers\Api\WordPressApiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.wordpress.')->group(function () {
    // WordPress Installations
    Route::get('wordpress', [WordPressApiController::class, 'index'])->name('index');
    Route::post('wordpress', [WordPressApiController::class, 'store'])->name('store');
    Route::get('wordpress/{id}', [WordPressApiController::class, 'show'])->name('show');
    Route::delete('wordpress/{id}', [WordPressApiController::class, 'destroy'])->name('destroy');

    // WordPress Info
    Route::get('wordpress/{id}/info', [WordPressApiController::class, 'getInfo'])->name('info');

    // Theme Management
    Route::get('wordpress/{id}/themes', [WordPressApiController::class, 'listThemes'])->name('themes.list');
    Route::post('wordpress/{id}/themes/sync', [WordPressApiController::class, 'syncThemes'])->name('themes.sync');
    Route::post('wordpress/{id}/themes/{slug}/activate', [WordPressApiController::class, 'activateTheme'])->name('themes.activate');
    Route::post('wordpress/{id}/themes/{slug}/install', [WordPressApiController::class, 'installTheme'])->name('themes.install');
    Route::post('wordpress/{id}/themes/{slug}/update', [WordPressApiController::class, 'updateTheme'])->name('themes.update');
    Route::delete('wordpress/{id}/themes/{slug}', [WordPressApiController::class, 'deleteTheme'])->name('themes.delete');

    // WordPress.org Theme Browse
    Route::get('wordpress/browse/themes', [WordPressApiController::class, 'browseWpOrgThemes'])->name('browse.themes');
    Route::get('wordpress/browse/themes/{slug}', [WordPressApiController::class, 'getWpOrgTheme'])->name('browse.themes.show');

    // Plugin Management
    Route::get('wordpress/{id}/plugins', [WordPressApiController::class, 'listPlugins'])->name('plugins.list');
    Route::post('wordpress/{id}/plugins/sync', [WordPressApiController::class, 'syncPlugins'])->name('plugins.sync');
    Route::post('wordpress/{id}/plugins/{slug}/activate', [WordPressApiController::class, 'activatePlugin'])->name('plugins.activate');
    Route::post('wordpress/{id}/plugins/{slug}/deactivate', [WordPressApiController::class, 'deactivatePlugin'])->name('plugins.deactivate');
    Route::post('wordpress/{id}/plugins/{slug}/install', [WordPressApiController::class, 'installPlugin'])->name('plugins.install');
    Route::post('wordpress/{id}/plugins/{slug}/update', [WordPressApiController::class, 'updatePlugin'])->name('plugins.update');
    Route::delete('wordpress/{id}/plugins/{slug}', [WordPressApiController::class, 'deletePlugin'])->name('plugins.delete');

    // WordPress.org Plugin Browse
    Route::get('wordpress/browse/plugins', [WordPressApiController::class, 'browseWpOrgPlugins'])->name('browse.plugins');
    Route::get('wordpress/browse/plugins/{slug}', [WordPressApiController::class, 'getWpOrgPlugin'])->name('browse.plugins.show');

    // Update Management
    Route::get('wordpress/{id}/updates', [WordPressApiController::class, 'checkUpdates'])->name('updates.check');
    Route::post('wordpress/{id}/updates/core', [WordPressApiController::class, 'updateCore'])->name('updates.core');
    Route::post('wordpress/{id}/updates/all', [WordPressApiController::class, 'updateAll'])->name('updates.all');
});
