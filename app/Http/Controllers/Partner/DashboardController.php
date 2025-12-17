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

        return redirect()->route('payment.show', ['token' => $kycSubmission->onboarding_token]);
    }
}
