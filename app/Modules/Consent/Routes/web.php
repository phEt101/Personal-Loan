<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Consent\Http\Controllers\ConsentController;

Route::middleware('web')->group(function () {
    Route::get('/consent', [ConsentController::class, 'index'])->name('consent.index');
    Route::post('/consent/save-step', [ConsentController::class, 'saveStep'])->name('consent.save-step');
    Route::delete('/consent/{consent}', [ConsentController::class, 'destroy'])->name('consent.destroy');

    Route::get('/consent/postcodes/options', [ConsentController::class, 'postCodeOptions'])->name('consent.postcodes.options');

    Route::get('/consent/modals/form', [ConsentController::class, 'modalConsentForm'])->name('consent.modals.form');
    Route::get('/consent/modals/view', [ConsentController::class, 'modalConsentView'])->name('consent.modals.view');

    Route::get('/consent/{consent}/data', [ConsentController::class, 'data'])->name('consent.data');
    Route::get('/consent/{consent}/income-documents/{document}', [ConsentController::class, 'downloadIncomeDocument'])->name('consent.income-documents.download');
    Route::delete('/consent/{consent}/income-documents/{document}', [ConsentController::class, 'destroyIncomeDocument'])->name('consent.income-documents.destroy');
});
