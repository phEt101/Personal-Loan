<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Home\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index']);

// Consent dashboard report route
