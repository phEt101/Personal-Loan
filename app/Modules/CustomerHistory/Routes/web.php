<?php

use App\Modules\CustomerHistory\Http\Controllers\CustomerController;
use App\Modules\CustomerHistory\Http\Controllers\CustomerHistoryController;
use App\Modules\CustomerHistory\Http\Controllers\CustomerHmeterTransferController;
use App\Modules\CustomerHistory\Http\Controllers\CustomerReadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/customer-history', [CustomerHistoryController::class, 'index'])->name('customer-history.index');
    Route::post('/customer-history', [CustomerController::class, 'store'])->name('customer-history.store');
    Route::put('/customer-history/{customerNo}', [CustomerController::class, 'update'])->name('customer-history.update');
    Route::get('/customer-history/locations/districts', [CustomerReadController::class, 'districts'])
        ->name('customer-history.locations.districts');
    Route::get('/customer-history/locations/sub-districts', [CustomerReadController::class, 'subDistricts'])
        ->name('customer-history.locations.sub-districts');
    Route::get('/customer-history/identity-card/check', [CustomerReadController::class, 'checkIdentityCard'])
        ->name('customer-history.identity-card.check');
    Route::get('/customer-history/attachments/{attachment}/preview', [CustomerReadController::class, 'previewAttachment'])
        ->name('customer-history.attachments.preview');
    Route::get('/customer-history/{customerNo}/attachments/download-all', [CustomerReadController::class, 'downloadAllAttachments'])
        ->name('customer-history.attachments.download-all');
    Route::patch('/customer-history/{customerNo}/hmeter-transfer', [CustomerHmeterTransferController::class, 'confirm'])
        ->name('customer-history.hmeter-transfer');
    Route::get('/customer-history/{customerNo}', [CustomerReadController::class, 'show'])
        ->name('customer-history.show');
});
