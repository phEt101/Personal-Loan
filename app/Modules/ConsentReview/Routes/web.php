<?php

use App\Modules\ConsentReview\Http\Controllers\ConsentReviewController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/consent-review', [ConsentReviewController::class, 'index'])->name('consentreview.index');
    Route::get('/consent-review/{consent}/data', [ConsentReviewController::class, 'data'])->name('consentreview.data');
    Route::post('/consent-review/{consent}/approve', [ConsentReviewController::class, 'approve'])->name('consentreview.approve');
    Route::get('/consent-review/modals/view', [ConsentReviewController::class, 'modalView'])->name('consentreview.modals.view');
});
