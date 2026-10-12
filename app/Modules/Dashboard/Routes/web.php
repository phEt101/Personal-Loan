<?php

use App\Modules\Dashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'dashboard'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard.index');
});
