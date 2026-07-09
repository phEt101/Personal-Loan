<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Home\Http\Controllers\HomeController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home.index');
});

// Consent dashboard report route
