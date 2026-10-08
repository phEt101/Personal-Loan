<?php

use App\Modules\CustomerHistory\Http\Controllers\CustomerController;
use App\Modules\CustomerHistory\Http\Controllers\CustomerHistoryController;
use App\Modules\CustomerHistory\Http\Controllers\WorkDelegationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/customer-history', [CustomerHistoryController::class, 'index'])->name('customer-history.index');
    Route::post('/customer-history', [CustomerController::class, 'store'])->name('customer-history.store');
    Route::put('/customer-history/{customerNo}', [CustomerController::class, 'update'])->name('customer-history.update');
    Route::get('/customer-history/locations/districts', [CustomerController::class, 'districts'])
        ->name('customer-history.locations.districts');
    Route::get('/customer-history/locations/sub-districts', [CustomerController::class, 'subDistricts'])
        ->name('customer-history.locations.sub-districts');
    Route::get('/customer-history/identity-card/check', [CustomerController::class, 'checkIdentityCard'])
        ->name('customer-history.identity-card.check');
    Route::get('/customer-history/attachments/{attachment}/preview', [CustomerController::class, 'previewAttachment'])
        ->name('customer-history.attachments.preview');
    Route::get('/customer-history/{customerNo}/attachments/download-all', [CustomerController::class, 'downloadAllAttachments'])
        ->name('customer-history.attachments.download-all');
    Route::patch('/customer-history/{customerNo}/hmeter-transfer', [CustomerController::class, 'confirmHmeterTransfer'])
        ->name('customer-history.hmeter-transfer');
    Route::get('/customer-history/{customerNo}', [CustomerController::class, 'show'])
        ->name('customer-history.show');
});

Route::middleware(['web', 'auth', 'internal'])->group(function () {
    Route::get('/work-delegations', [WorkDelegationController::class, 'index'])->name('work-delegations.index');
    Route::get('/work-delegations/create', [WorkDelegationController::class, 'create'])->name('work-delegations.create');
    Route::post('/work-delegations', [WorkDelegationController::class, 'store'])->name('work-delegations.store');
    Route::get('/work-delegations/{workDelegation}/edit', [WorkDelegationController::class, 'edit'])->name('work-delegations.edit');
    Route::put('/work-delegations/{workDelegation}', [WorkDelegationController::class, 'update'])->name('work-delegations.update');
    Route::patch('/work-delegations/{workDelegation}/cancel', [WorkDelegationController::class, 'cancel'])->name('work-delegations.cancel');
});
