<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\FinalOnboarding;
use App\Models\PartnershipModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $partner = Auth::guard('partner')->user();
        $partner->update(['last_accessed_at' => now()]);

        $kycSubmission = $partner->kycSubmission;
        $finalOnboarding = $partner->finalOnboarding;

        Log::info('Partner dashboard accessed', [
            'partner_id' => $partner->id,
            'email' => $partner->email,
        ]);

        return view('partner.dashboard', [
            'partner' => $partner,
            'kycSubmission' => $kycSubmission,
            'finalOnboarding' => $finalOnboarding,
        ]);
    }

    public function continueOnboarding()
    {
        $partner = Auth::guard('partner')->user();
        $kycSubmission = $partner->kycSubmission;

        if (!$kycSubmission) {
            return redirect()->route('partner.dashboard')->with('error', 'No KYC submission found.');
        }

        if ($partner->onboarding_form_completed) {
            return redirect()->route('partner.dashboard')->with('info', 'You have already completed the onboarding form.');
        }

        Log::info('Partner continuing onboarding', [
            'partner_id' => $partner->id,
            'submission_id' => $kycSubmission->id,
        ]);

        return redirect()->route('onboarding.show', ['token' => $kycSubmission->onboarding_token]);
    }

    public function viewKycSubmission()
    {
        $partner = Auth::guard('partner')->user();
        $kycSubmission = $partner->kycSubmission;

        if (!$kycSubmission) {
            return redirect()->route('partner.dashboard')->with('error', 'No KYC submission found.');
        }

        return view('partner.kyc-details', [
            'partner' => $partner,
            'kycSubmission' => $kycSubmission,
        ]);
    }

    public function viewPartnership()
    {
        $partner = Auth::guard('partner')->user();
        $finalOnboarding = $partner->finalOnboarding;

        if (!$finalOnboarding) {
            return redirect()->route('partner.dashboard')->with('error', 'No partnership found. Please complete the onboarding form first.');
        }

        return view('partner.partnership-details', [
            'partner' => $partner,
            'finalOnboarding' => $finalOnboarding,
        ]);
    }

    public function makePayment()
    {
        $partner = Auth::guard('partner')->user();
        $kycSubmission = $partner->kycSubmission;

        if (!$kycSubmission) {
            return redirect()->route('partner.dashboard')->with('error', 'No KYC submission found.');
        }

        if (!$partner->onboarding_form_completed) {
            return redirect()->route('partner.dashboard')->with('error', 'Please complete the onboarding form before making payment.');
        }

        if ($partner->payment_completed) {
            return redirect()->route('partner.dashboard')->with('info', 'Your payment has already been completed.');
        }

        Log::info('Partner redirected to payment', [
            'partner_id' => $partner->id,
            'submission_id' => $kycSubmission->id,
        ]);

        return redirect()->route('onboarding.payment', ['token' => $kycSubmission->onboarding_token]);
    }

    public function transactionHistory()
    {
        $partner = Auth::guard('partner')->user();
        $finalOnboarding = $partner->finalOnboarding;

        if (!$finalOnboarding) {
            return redirect()->route('partner.dashboard')->with('error', 'No partnership found.');
        }

        // Build transaction history
        $transactions = [];

        // Signup Fee Transaction
        if ($finalOnboarding->signup_fee_paid) {
            $transactions[] = [
                'date' => $finalOnboarding->signup_fee_paid_at ?? $finalOnboarding->created_at,
                'type' => 'Signup Fee',
                'amount' => $finalOnboarding->signup_fee_amount,
                'reference' => $finalOnboarding->signup_fee_reference ?? 'N/A',
                'status' => 'Completed',
                'method' => $finalOnboarding->payment_method ?? 'N/A',
            ];
        }

        // Partnership Fee Transaction
        if ($finalOnboarding->model_fee_paid) {
            $transactions[] = [
                'date' => $finalOnboarding->model_fee_paid_at ?? $finalOnboarding->created_at,
                'type' => 'Partnership Fee',
                'amount' => $finalOnboarding->partnership_model_price,
                'reference' => $finalOnboarding->model_fee_reference ?? 'N/A',
                'status' => 'Completed',
                'method' => $finalOnboarding->payment_method ?? 'N/A',
            ];
        }

        // Solar Power Transaction
        if ($finalOnboarding->solar_power && $finalOnboarding->solar_power_amount > 0) {
            $transactions[] = [
                'date' => $finalOnboarding->created_at,
                'type' => 'Solar Power Package',
                'amount' => $finalOnboarding->solar_power_amount,
                'reference' => $finalOnboarding->model_fee_reference ?? 'N/A',
                'status' => $finalOnboarding->model_fee_paid ? 'Completed' : 'Pending',
                'method' => $finalOnboarding->payment_method ?? 'N/A',
            ];
        }

        // Sort by date descending
        usort($transactions, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        Log::info('Partner transaction history accessed', [
            'partner_id' => $partner->id,
            'transaction_count' => count($transactions),
        ]);

        return view('partner.transaction-history', [
            'partner' => $partner,
            'finalOnboarding' => $finalOnboarding,
            'transactions' => $transactions,
        ]);
    }

    public function activityLog()
    {
        $partner = Auth::guard('partner')->user();

        // Build activity log
        $activities = [];

        // Account Created
        $activities[] = [
            'date' => $partner->created_at,
            'action' => 'Account Created',
            'description' => 'Partner account was created',
            'icon' => 'user-plus',
            'color' => 'green',
        ];

        // KYC Form Completed
        if ($partner->kyc_form_completed) {
            $activities[] = [
                'date' => $partner->kycSubmission->created_at ?? $partner->created_at,
                'action' => 'KYC Form Submitted',
                'description' => 'KYC verification form submitted successfully',
                'icon' => 'document-check',
                'color' => 'blue',
            ];
        }

        // Onboarding Form Completed
        if ($partner->onboarding_form_completed && $partner->finalOnboarding) {
            $activities[] = [
                'date' => $partner->finalOnboarding->created_at,
                'action' => 'Partnership Selected',
                'description' => 'Selected ' . ($partner->finalOnboarding->partnership_model_name ?? 'partnership model'),
                'icon' => 'briefcase',
                'color' => 'purple',
            ];
        }

        // Payment Completed
        if ($partner->payment_completed && $partner->finalOnboarding) {
            $activities[] = [
                'date' => $partner->finalOnboarding->signup_fee_paid_at ?? $partner->finalOnboarding->updated_at,
                'action' => 'Payment Completed',
                'description' => 'All payments completed successfully',
                'icon' => 'currency-dollar',
                'color' => 'green',
            ];
        }

        // Last Login
        if ($partner->last_accessed_at) {
            $activities[] = [
                'date' => $partner->last_accessed_at,
                'action' => 'Dashboard Accessed',
                'description' => 'Last login to partner dashboard',
                'icon' => 'login',
                'color' => 'gray',
            ];
        }

        // Sort by date descending
        usort($activities, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        Log::info('Partner activity log accessed', [
            'partner_id' => $partner->id,
            'activity_count' => count($activities),
        ]);

        return view('partner.activity-log', [
            'partner' => $partner,
            'activities' => $activities,
        ]);
    }
}
