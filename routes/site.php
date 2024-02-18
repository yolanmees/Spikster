<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\Site\WordPressController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\DnsRecordsController;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified' ])->group(function () {

    Route::get('/sites', function () {
        return view('site.list');
    })->name('site.list');

    Route::get('/sites/{site_id}', function ($site_id) {
        return view('site.edit', compact('site_id'));
    })->name('site.edit');

      //database
    Route::get('/site/{site_id}/database', [DatabaseController::class, 'viewdatabase'])->name('site.database');
    Route::post('/site/{site_id}/database/create/database', [DatabaseController::class,'createdatabase'])->name('site.database.create.database');
    Route::post('/site/{site_id}/database/create/user', [DatabaseController::class,'createuser'])->name('site.database.create.user');
    Route::post('/site/{site_id}/database/create/link', [DatabaseController::class,'linkdatabaseuser'])->name('site.database.create.link');
    Route::delete('/site/{site_id}/database/delete/database', [DatabaseController::class,'deleteDatabase'])->name('site.database.delete.database');
    Route::delete('/site/{site_id}/database/delete/user', [DatabaseController::class,'deleteUser'])->name('site.database.delete.user');
    Route::delete('/site/{site_id}/database/delete/link', [DatabaseController::class,'deleteLink'])->name('site.database.delete.link');

    //wordpress
    Route::get('/site/{site_id}/wordpress', [WordPressController::class, 'index'])->name('site.wordpress');
    Route::post('/site/{site_id}/wordpress/create', [WordPressController::class, 'create'])->name('site.wordpress.create');
    Route::delete('/site/{site_id}/wordpress/delete', [WordPressController::class, 'delete'])->name('site.wordpress.delete');

    //dns
    Route::get('/site/{site_id}/dns', [DnsRecordsController::class, 'index'])->name('site.dns');
    Route::get('/site/{site_id}/dns/new', [DnsRecordsController::class, 'new'])->name('site.dns.new');
    Route::post('/site/{site_id}/dns/create', [DnsRecordsController::class, 'create'])->name('site.dns.create');
    Route::get('/site/{site_id}/dns/{dns_id}', [DnsRecordsController::class, 'edit'])->name('site.dns.edit');
    Route::put('/site/{site_id}/dns/{dns_id}', [DnsRecordsController::class, 'update'])->name('site.dns.update');
    Route::delete('/site/{site_id}/dns/{dns_id}/delete', [DnsRecordsController::class, 'delete'])->name('site.dns.delete');

    // pdf after creation
    Route::get('/pdf/{site_id}/{token}', [SiteController::class, 'pdf']);
});