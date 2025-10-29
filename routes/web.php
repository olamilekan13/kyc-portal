<?php

use App\Http\Controllers\Api\LivenessVerificationController;
use App\Http\Controllers\Api\NinVerificationController;
use App\Http\Controllers\KycSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// API Routes for AJAX requests
Route::prefix('api')->name('api.')->group(function () {
    // NIN Verification endpoint (rate limited: 5 per minute)
    Route::post('/nin/verify', [NinVerificationController::class, 'verify'])
        ->name('nin.verify');

    // Liveness Selfie Verification endpoint (rate limited: 3 per minute)
    Route::post('/liveness/verify', [LivenessVerificationController::class, 'verify'])
        ->name('liveness.verify');
});

// Public KYC Submission Routes
Route::prefix('kyc')->name('kyc.')->group(function () {
    // Show default KYC form when accessing /kyc directly
    Route::get('/', [KycSubmissionController::class, 'showDefault'])
        ->name('default');

    // Success page (must come before wildcard route)
    Route::get('/success/{submissionId}', [KycSubmissionController::class, 'success'])
        ->name('success');

    // Show KYC form (by slug or ID)
    // Examples: /kyc/company-onboarding OR /kyc/7
    Route::get('/{form}', [KycSubmissionController::class, 'show'])
        ->name('show');

    // Submit KYC form with rate limiting (10 submissions per hour per IP)
    Route::post('/{form}/submit', [KycSubmissionController::class, 'submit'])
        ->middleware('throttle:10,60')
        ->name('submit');
});
