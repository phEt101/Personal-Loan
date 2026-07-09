<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Home\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::get('/locale/{locale}', function (string $locale) {
	abort_unless(in_array($locale, ['th', 'en'], true), 404);

	session(['locale' => $locale]);
	app()->setLocale($locale);

	return redirect()->back()->withCookie(cookie('locale', $locale, 60 * 24 * 365));
})->name('locale.switch');

// Consent dashboard report route
