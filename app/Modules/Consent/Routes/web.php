<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Consent\Http\Controllers\ConsentController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/consent', [ConsentController::class, 'index'])->name('consent.index');
    Route::post('/consent/save-step', [ConsentController::class, 'saveStep'])->name('consent.save-step');
    Route::delete('/consent/{consent}', [ConsentController::class, 'destroy'])->name('consent.destroy');

    Route::get('/consent/postcodes/options', [ConsentController::class, 'postCodeOptions'])->name('consent.postcodes.options');

    Route::get('/consent/modals/form', [ConsentController::class, 'modalConsentForm'])->name('consent.modals.form');
    Route::get('/consent/modals/view', [ConsentController::class, 'modalConsentView'])->name('consent.modals.view');

    Route::get('/consent/{consent}/data', [ConsentController::class, 'data'])->name('consent.data');
    Route::get('/consent/{consent}/income-documents/{document}', [ConsentController::class, 'downloadIncomeDocument'])->name('consent.income-documents.download');
    Route::get('/consent/{consent}/income-documents/{document}/zip-contents', [ConsentController::class, 'listZipContents'])->name('consent.income-documents.zip-contents');
    Route::get('/consent/{consent}/income-documents/{document}/zip-file', [ConsentController::class, 'streamZipEntry'])->name('consent.income-documents.zip-file');
    Route::delete('/consent/{consent}/income-documents/{document}', [ConsentController::class, 'destroyIncomeDocument'])->name('consent.income-documents.destroy');
});
