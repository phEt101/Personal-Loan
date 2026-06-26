<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Consent\Http\Controllers\ConsentController;

Route::middleware('web')->group(function () {
    Route::get('/consent', [ConsentController::class, 'index'])->name('consent.index');
    Route::get('/consent/create', [ConsentController::class, 'create'])->name('consent.create');
    Route::post('/consent', [ConsentController::class, 'store'])->name('consent.store');
});
