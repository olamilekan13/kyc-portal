<?php

use App\Http\Controllers\KycSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public KYC Submission Routes
Route::prefix('kyc')->name('kyc.')->group(function () {
    // Show KYC form
    Route::get('/{formId}', [KycSubmissionController::class, 'show'])
        ->name('show');

    // Submit KYC form with rate limiting (10 submissions per hour per IP)
    Route::post('/{formId}/submit', [KycSubmissionController::class, 'submit'])
        ->middleware('throttle:10,60')
        ->name('submit');

    // Success page
    Route::get('/success/{submissionId}', [KycSubmissionController::class, 'success'])
        ->name('success');
});
