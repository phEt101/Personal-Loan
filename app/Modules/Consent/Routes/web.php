<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Consent\Http\Controllers\ConsentController;

Route::get('/consent', [ConsentController::class, 'index'])->name('consent.index');
