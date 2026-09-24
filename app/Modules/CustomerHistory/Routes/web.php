<?php

use App\Modules\CustomerHistory\Http\Controllers\CustomerHistoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/customer-history', [CustomerHistoryController::class, 'index'])->name('customer-history.index');
});