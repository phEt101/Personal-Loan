<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Consent\Http\Controllers\ConsentController;

Route::middleware('web')->group(function () {
    Route::get('/consent', [ConsentController::class, 'index'])->name('consent.index');
    Route::post('/consent', [ConsentController::class, 'store'])->name('consent.store');
    Route::put('/consent/{consent}', [ConsentController::class, 'update'])->name('consent.update');
    Route::delete('/consent/{consent}', [ConsentController::class, 'destroy'])->name('consent.destroy');

    Route::get('/consent/postcodes/provinces', [ConsentController::class, 'postCodeProvinces'])->name('consent.postcodes.provinces');
    Route::get('/consent/postcodes/cities', [ConsentController::class, 'postCodeCities'])->name('consent.postcodes.cities');
    Route::get('/consent/postcodes/districts', [ConsentController::class, 'postCodeDistricts'])->name('consent.postcodes.districts');
    Route::get('/consent/postcodes/postcodes', [ConsentController::class, 'postCodePostCodes'])->name('consent.postcodes.postcodes');
    Route::get('/consent/postcodes/options', [ConsentController::class, 'postCodeOptions'])->name('consent.postcodes.options');

    Route::get('/consent/modals/form', [ConsentController::class, 'modalConsentForm'])->name('consent.modals.form');
    Route::get('/consent/modals/view', [ConsentController::class, 'modalConsentView'])->name('consent.modals.view');

    Route::get('/consent/{consent}/data', [ConsentController::class, 'data'])->name('consent.data');
});
