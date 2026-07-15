<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Consent\Http\Controllers\ConsentController;
use App\Modules\Consent\Http\Controllers\ConsentFormController;
use App\Modules\Consent\Http\Controllers\ConsentDocumentController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/consent', [ConsentController::class, 'index'])->name('consent.index');
    Route::post('/consent/save-step', [ConsentFormController::class, 'saveStep'])->name('consent.save-step');
    Route::delete('/consent/{consent}', [ConsentController::class, 'destroy'])->name('consent.destroy');

    Route::get('/consent/postcodes/options', [ConsentController::class, 'postCodeOptions'])->name('consent.postcodes.options');

    Route::get('/consent/modals/form', [ConsentController::class, 'modalConsentForm'])->name('consent.modals.form');
    Route::get('/consent/modals/view', [ConsentController::class, 'modalConsentView'])->name('consent.modals.view');

    Route::get('/consent/{consent}/data', [ConsentController::class, 'data'])->name('consent.data');
    Route::get('/consent/{consent}/income-documents/{document}', [ConsentDocumentController::class, 'downloadIncomeDocument'])->name('consent.income-documents.download');
    Route::get('/consent/{consent}/income-documents/{document}/zip-contents', [ConsentDocumentController::class, 'listZipContents'])->name('consent.income-documents.zip-contents');
    Route::get('/consent/{consent}/income-documents/{document}/zip-file', [ConsentDocumentController::class, 'streamZipEntry'])->name('consent.income-documents.zip-file');
    Route::delete('/consent/{consent}/income-documents/{document}', [ConsentDocumentController::class, 'destroyIncomeDocument'])->name('consent.income-documents.destroy');
});
