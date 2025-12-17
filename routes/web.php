<?php

use App\Http\Controllers\Api\LivenessVerificationController;
use App\Http\Controllers\Api\NinVerificationController;
use App\Http\Controllers\KycSubmissionController;
use App\Http\Controllers\FinalOnboardingController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\Partner\AuthController;
use App\Http\Controllers\Partner\DashboardController;
use App\Models\HomePageSetting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $settings = HomePageSetting::getActiveSetting();
    return view('home', compact('settings'));
});

// API Routes for AJAX requests
Route::prefix('api')->name('api.')->group(function () {
    // NIN Verification endpoint
    Route::post('/nin/verify', [NinVerificationController::class, 'verify'])
        ->name('nin.verify');

    // Liveness Selfie Verification endpoint
    Route::post('/liveness/verify', [LivenessVerificationController::class, 'verify'])
        ->name('liveness.verify');
});

// Public KYC Submission Routes
Route::prefix('kyc')->name('kyc.')->group(function () {
    // Show default KYC form when accessing /kyc directly
    Route::get('/', [KycSubmissionController::class, 'showDefault'])
        ->name('default');

    // Account created page (must come before wildcard route)
    Route::get('/account-created', [KycSubmissionController::class, 'accountCreated'])
        ->name('account-created');

    // Success page (must come before wildcard route)
    Route::get('/success/{submissionId}', [KycSubmissionController::class, 'success'])
        ->name('success');

    // Show KYC form (by slug or ID)
    // Examples: /kyc/company-onboarding OR /kyc/7
    Route::get('/{form}', [KycSubmissionController::class, 'show'])
        ->name('show');

    // Submit KYC form
    Route::post('/{form}/submit', [KycSubmissionController::class, 'submit'])
        ->name('submit');
});

// Final Onboarding Routes
Route::prefix('onboarding')->name('onboarding.')->group(function () {
    // Show onboarding form (partnership selection)
    Route::get('/{token}', [FinalOnboardingController::class, 'show'])
        ->name('show');

    // Submit partnership model selection
    Route::post('/{token}/submit', [FinalOnboardingController::class, 'submitSelection'])
        ->name('submit');

    // Payment page
    Route::get('/{token}/payment', [FinalOnboardingController::class, 'payment'])
        ->name('payment');

    // Process bank transfer payment
    Route::post('/{token}/payment/bank-transfer', [FinalOnboardingController::class, 'processBankTransfer'])
        ->name('bank-transfer');

    // Paystack payment callback
    Route::get('/{token}/payment/paystack/callback', [FinalOnboardingController::class, 'paystackCallback'])
        ->name('paystack-callback');

    // Payment confirmation page
    Route::get('/{token}/confirmation', [FinalOnboardingController::class, 'confirmation'])
        ->name('confirmation');

    // Download payment receipt
    Route::get('/{token}/receipt', [FinalOnboardingController::class, 'downloadReceipt'])
        ->name('receipt');
});

// Partnership Renewal Routes
Route::prefix('renew')->name('renewal.')->group(function () {
    // Show renewal page
    Route::get('/{token}', [RenewalController::class, 'show'])
        ->name('show');

    // Submit renewal selection
    Route::post('/{token}/submit', [RenewalController::class, 'submitSelection'])
        ->name('submit');

    // Renewal payment page
    Route::get('/{token}/payment', [RenewalController::class, 'payment'])
        ->name('payment');

    // Process bank transfer payment for renewal
    Route::post('/{token}/payment/bank-transfer', [RenewalController::class, 'processBankTransfer'])
        ->name('bank-transfer');

    // Paystack payment callback for renewal
    Route::get('/{token}/payment/paystack/callback', [RenewalController::class, 'paystackCallback'])
        ->name('paystack-callback');

    // Renewal confirmation page
    Route::get('/{token}/confirmation', [RenewalController::class, 'confirmation'])
        ->name('confirmation');
});

// Partner Authentication Routes
Route::prefix('partner')->name('partner.')->group(function () {
    // Guest routes (only accessible when not logged in)
    Route::middleware('guest:partner')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    // Protected routes (require authentication)
    Route::middleware('auth:partner')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/continue-onboarding', [DashboardController::class, 'continueOnboarding'])->name('continue-onboarding');
        Route::get('/kyc-details', [DashboardController::class, 'viewKycSubmission'])->name('kyc-details');
        Route::get('/partnership-details', [DashboardController::class, 'viewPartnership'])->name('partnership-details');
        Route::get('/make-payment', [DashboardController::class, 'makePayment'])->name('make-payment');
        Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('change-password');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password.update');
        Route::get('/transactions', [DashboardController::class, 'transactionHistory'])->name('transactions');
        Route::get('/activity', [DashboardController::class, 'activityLog'])->name('activity');
    });
});
