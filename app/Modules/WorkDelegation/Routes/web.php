<?php

use App\Modules\WorkDelegation\Http\Controllers\WorkDelegationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'internal'])->group(function () {
    Route::get('/work-delegations', [WorkDelegationController::class, 'index'])->name('work-delegations.index');
    Route::get('/work-delegations/create', [WorkDelegationController::class, 'create'])->name('work-delegations.create');
    Route::post('/work-delegations', [WorkDelegationController::class, 'store'])->name('work-delegations.store');
    Route::get('/work-delegations/{workDelegation}/edit', [WorkDelegationController::class, 'edit'])->name('work-delegations.edit');
    Route::put('/work-delegations/{workDelegation}', [WorkDelegationController::class, 'update'])->name('work-delegations.update');
    Route::patch('/work-delegations/{workDelegation}/cancel', [WorkDelegationController::class, 'cancel'])->name('work-delegations.cancel');
});
