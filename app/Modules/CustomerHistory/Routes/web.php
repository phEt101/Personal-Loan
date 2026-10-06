<?php

use App\Modules\CustomerHistory\Http\Controllers\CustomerHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/customer-history', [CustomerHistoryController::class, 'index'])->name('customer-history.index');
    Route::post('/customer-history', [CustomerHistoryController::class, 'store'])->name('customer-history.store');
    Route::put('/customer-history/{customerNo}', [CustomerHistoryController::class, 'update'])->name('customer-history.update');
    Route::get('/customer-history/locations/districts', [CustomerHistoryController::class, 'districts'])
        ->name('customer-history.locations.districts');
    Route::get('/customer-history/locations/sub-districts', [CustomerHistoryController::class, 'subDistricts'])
        ->name('customer-history.locations.sub-districts');
    Route::get('/customer-history/identity-card/check', [CustomerHistoryController::class, 'checkIdentityCard'])
        ->name('customer-history.identity-card.check');
    Route::get('/customer-history/attachments/{attachment}/download', [CustomerHistoryController::class, 'downloadAttachment'])
        ->name('customer-history.attachments.download');
    Route::get('/customer-history/{customerNo}/attachments/download-all', [CustomerHistoryController::class, 'downloadAllAttachments'])
        ->name('customer-history.attachments.download-all');
    Route::patch('/customer-history/{customerNo}/hmeter-transfer', [CustomerHistoryController::class, 'confirmHmeterTransfer'])
        ->name('customer-history.hmeter-transfer');
    Route::get('/customer-history/{customerNo}', [CustomerHistoryController::class, 'show'])
        ->name('customer-history.show');
});
