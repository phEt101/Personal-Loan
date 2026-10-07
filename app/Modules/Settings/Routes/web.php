<?php

use App\Modules\Settings\Http\Controllers\ResponsibilityGroupController;
use App\Modules\Settings\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin'])->prefix('settings')->name('settings.')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::resource('responsibility-groups', ResponsibilityGroupController::class)
        ->except(['show', 'destroy']);
});
