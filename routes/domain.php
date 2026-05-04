<?php

use App\Http\Controllers\DomainController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Domain list
    Route::get('/domains', [DomainController::class, 'index'])->name('domain.list');

    // Create domain
    Route::get('/domains/create', [DomainController::class, 'create'])->name('domain.create');
    Route::post('/domains', [DomainController::class, 'store'])->name('domain.store');

    // View domain with DNS records
    Route::get('/domains/{domain_id}', [DomainController::class, 'show'])->name('domain.show');

    // Update / delete domain
    Route::put('/domains/{domain_id}', [DomainController::class, 'update'])->name('domain.update');
    Route::delete('/domains/{domain_id}', [DomainController::class, 'destroy'])->name('domain.destroy');

    // DNS record management
    Route::get('/domains/{domain_id}/dns/new', [DomainController::class, 'newDnsRecord'])->name('domain.dns.new');
    Route::post('/domains/{domain_id}/dns/create', [DomainController::class, 'createDnsRecord'])->name('domain.dns.create');
    Route::get('/domains/{domain_id}/dns/{dns_id}/edit', [DomainController::class, 'editDnsRecord'])->name('domain.dns.edit');
    Route::put('/domains/{domain_id}/dns/{dns_id}', [DomainController::class, 'updateDnsRecord'])->name('domain.dns.update');
    Route::delete('/domains/{domain_id}/dns/{dns_id}/delete', [DomainController::class, 'deleteDnsRecord'])->name('domain.dns.delete');
});
