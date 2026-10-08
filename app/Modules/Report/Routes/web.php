<?php

use App\Modules\Report\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'internal'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});
