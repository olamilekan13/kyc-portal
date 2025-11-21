<?php

namespace App\Http\Controllers;

use App\Models\FinalOnboarding;
use App\Models\PartnershipModel;
use App\Models\SystemSetting;
use App\Mail\RenewalSuccessNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Exception;

class RenewalController extends Controller
{
    /**
     * Display the renewal page
     *
     * @param string $token The renewal token
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show($token)
    {
        try {
            $finalOnboarding = FinalOnboarding::where('renewal_token', $token)
                ->with(['kycSubmission', 'partnershipModel'])
                ->first();

            if (!$finalOnboarding) {
                abort(404, 'Invalid renewal link. Please contact support.');
            }

            // Get active partnership models
            $partnershipModels = PartnershipModel::active()->get();

            // Get system settings
            $bankName = SystemSetting::get('bank_name', '');
            $bankAccountNumber = SystemSetting::get('bank_account_number', '');
            $bankAccountName = SystemSetting::get('bank_account_name', '');
            $paystackPublicKey = SystemSetting::get('paystack_public_key', '');

            Log::info('Renewal page viewed', [
                'final_onboarding_id' => $finalOnboarding->id,
                'renewal_token' => $token,
                'ip_address' => request()->ip(),
            ]);

            return view('renewal.show', [
                'finalOnboarding' => $finalOnboarding,
                'partnershipModels' => $partnershipModels,
                'bankName' => $bankName,
                'bankAccountNumber' => $bankAccountNumber,
                'bankAccountName' => $bankAccountName,
                'paystackPublicKey' => $paystackPublicKey,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying renewal page', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to load renewal page');
        }
    }

    /**
     * Submit renewal selection
     *
     * @param Request $request
     * @param string $token The renewal token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitSelection(Request $request, $token)
    {
        try {
            $finalOnboarding = FinalOnboarding::where('renewal_token', $token)->first();

            if (!$finalOnboarding) {
                abort(404, 'Invalid renewal link.');
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'partnership_model_id' => ['required', 'exists:partnership_models,id'],
                'payment_method' => ['required', 'in:bank_transfer,paystack'],
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get partnership model
            $partnershipModel = PartnershipModel::findOrFail($request->partnership_model_id);

            // Store renewal selection in session (temporary until payment)
            session([
                'renewal_' . $token => [
                    'partnership_model_id' => $partnershipModel->id,
                    'partnership_model_name' => $partnershipModel->name,
                    'partnership_model_price' => $partnershipModel->price,
                    'duration_months' => $partnershipModel->duration_months,
                    'payment_method' => $request->payment_method,
                ]
            ]);

            Log::info('Renewal selection submitted', [
                'final_onboarding_id' => $finalOnboarding->id,
                'partnership_model_id' => $partnershipModel->id,
                'payment_method' => $request->payment_method,
            ]);

            // Redirect to payment page
            return redirect()->route('renewal.payment', ['token' => $token]);
        } catch (Exception $e) {
            Log::error('Error submitting renewal selection', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'An error occurred while processing your selection. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display the renewal payment page
     *
     * @param string $token The renewal token
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function payment($token)
    {
        try {
            $finalOnboarding = FinalOnboarding::where('renewal_token', $token)
                ->with(['kycSubmission', 'partnershipModel'])
                ->first();

            if (!$finalOnboarding) {
                abort(404, 'Invalid renewal link.');
            }

            // Get renewal selection from session
            $renewalData = session('renewal_' . $token);

            if (!$renewalData) {
                return redirect()->route('renewal.show', ['token' => $token])
                    ->with('error', 'Please select a partnership model first.');
            }

            // Get payment details
            $bankName = SystemSetting::get('bank_name', '');
            $bankAccountNumber = SystemSetting::get('bank_account_number', '');
            $bankAccountName = SystemSetting::get('bank_account_name', '');
            $paystackPublicKey = SystemSetting::get('paystack_public_key', '');

            Log::info('Renewal payment page viewed', [
                'final_onboarding_id' => $finalOnboarding->id,
            ]);

            return view('renewal.payment', [
                'finalOnboarding' => $finalOnboarding,
                'renewalData' => $renewalData,
                'bankName' => $bankName,
                'bankAccountNumber' => $bankAccountNumber,
                'bankAccountName' => $bankAccountName,
                'paystackPublicKey' => $paystackPublicKey,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying renewal payment page', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to load payment page');
        }
    }

    /**
     * Process bank transfer payment for renewal
     *
     * @param Request $request
     * @param string $token The renewal token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processBankTransfer(Request $request, $token)
    {
        try {
            $finalOnboarding = FinalOnboarding::where('renewal_token', $token)->first();

            if (!$finalOnboarding) {
                abort(404, 'Invalid request.');
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'payment_reference' => ['nullable', 'string', 'max:255'],
                'payment_notes' => ['nullable', 'string', 'max:1000'],
                'payment_proof' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Get renewal data from session
            $renewalData = session('renewal_' . $token);

            if (!$renewalData) {
                return redirect()->route('renewal.show', ['token' => $token])
                    ->with('error', 'Session expired. Please select a partnership model again.');
            }

            // Handle payment proof file upload
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $paymentProofPath = $file->store('renewal-payment-proofs', 'local');
            }

            // Mark as pending renewal (admin needs to verify)
            $finalOnboarding->update([
                'renewal_status' => 'pending_renewal',
                'payment_notes' => $request->payment_notes,
            ]);

            // Store renewal request details for admin verification
            // We'll create a simple approach: store in payment_notes with renewal info
            $renewalInfo = "RENEWAL REQUEST:\n";
            $renewalInfo .= "New Partnership: {$renewalData['partnership_model_name']}\n";
            $renewalInfo .= "Duration: {$renewalData['duration_months']} months\n";
            $renewalInfo .= "Amount: ₦" . number_format($renewalData['partnership_model_price'], 2) . "\n";
            $renewalInfo .= "Payment Reference: " . ($request->payment_reference ?? 'N/A') . "\n";
            $renewalInfo .= "User Notes: " . ($request->payment_notes ?? 'N/A');

            $finalOnboarding->payment_notes = $renewalInfo;
            if ($paymentProofPath) {
                $finalOnboarding->payment_proof = $paymentProofPath;
            }
            $finalOnboarding->save();

            Log::info('Renewal bank transfer submitted', [
                'final_onboarding_id' => $finalOnboarding->id,
                'payment_reference' => $request->payment_reference,
            ]);

            // Send notification to admin
            try {
                $notificationEmail = SystemSetting::get('onboarding_notification_email')
                    ?? SystemSetting::get('admin_notification_email');

                if ($notificationEmail) {
                    // You can create a specific mail class for this
                    Mail::raw(
                        "A renewal payment has been submitted and requires verification.\n\n" .
                        "Partner: {$finalOnboarding->partner_name}\n" .
                        "Email: {$finalOnboarding->partner_email}\n\n" .
                        $renewalInfo . "\n\n" .
                        "Please verify in the admin panel.",
                        function ($message) use ($notificationEmail) {
                            $message->to($notificationEmail)
                                ->subject('Partnership Renewal Payment Submitted');
                        }
                    );
                }
            } catch (Exception $e) {
                Log::error('Failed to send renewal notification email', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Clear session
            session()->forget('renewal_' . $token);

            return redirect()->route('renewal.confirmation', ['token' => $token])
                ->with('success', 'Renewal payment submitted successfully. Admin will verify your payment.');
        } catch (Exception $e) {
            Log::error('Error processing renewal bank transfer', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'An error occurred while processing your payment. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Process Paystack payment callback for renewal
     *
     * @param Request $request
     * @param string $token The renewal token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paystackCallback(Request $request, $token)
    {
        try {
            $finalOnboarding = FinalOnboarding::where('renewal_token', $token)
                ->with('partnershipModel')
                ->first();

            if (!$finalOnboarding) {
                abort(404, 'Invalid request.');
            }

            $reference = $request->query('reference');

            if (!$reference) {
                return redirect()->route('renewal.payment', ['token' => $token])
                    ->with('error', 'Payment reference not found.');
            }

            // Get renewal data from session
            $renewalData = session('renewal_' . $token);

            if (!$renewalData) {
                return redirect()->route('renewal.show', ['token' => $token])
                    ->with('error', 'Session expired. Please try again.');
            }

            // Verify payment with Paystack
            $paystackSecretKey = SystemSetting::get('paystack_secret_key', '');

            if (!$paystackSecretKey) {
                throw new Exception('Paystack secret key not configured');
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$paystackSecretKey}",
                    "Cache-Control: no-cache",
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                throw new Exception('Curl error: ' . $err);
            }

            $result = json_decode($response, true);

            if (!$result['status']) {
                throw new Exception('Payment verification failed');
            }

            // Payment successful - extend partnership
            $newPartnershipModel = PartnershipModel::find($renewalData['partnership_model_id']);
            $durationMonths = $newPartnershipModel->duration_months ?? 12;

            // Calculate new end date (from current end date or today, whichever is later)
            $startDate = now();
            if ($finalOnboarding->partnership_end_date && $finalOnboarding->partnership_end_date->isFuture()) {
                $startDate = $finalOnboarding->partnership_end_date;
            }

            $finalOnboarding->update([
                'partnership_model_id' => $newPartnershipModel->id,
                'partnership_model_name' => $newPartnershipModel->name,
                'partnership_model_price' => $newPartnershipModel->price,
                'partnership_start_date' => now()->toDateString(),
                'partnership_end_date' => $startDate->copy()->addMonths($durationMonths)->toDateString(),
                'renewal_status' => 'renewed',
                'renewal_token' => FinalOnboarding::generateRenewalToken(), // Generate new token for next renewal
                'reminder_sent_at' => null,
                'reminder_count' => 0,
                'duration_months' => $durationMonths,
                'model_fee_reference' => $reference,
                'model_fee_paid_at' => now(),
            ]);

            Log::info('Renewal Paystack payment verified', [
                'final_onboarding_id' => $finalOnboarding->id,
                'reference' => $reference,
                'new_end_date' => $finalOnboarding->partnership_end_date,
            ]);

            // Clear session
            session()->forget('renewal_' . $token);

            // Note: The old token is no longer valid, redirect with success message
            return redirect()->route('renewal.confirmation', ['token' => $finalOnboarding->renewal_token])
                ->with('success', 'Payment successful! Your partnership has been renewed.');
        } catch (Exception $e) {
            Log::error('Error processing renewal Paystack callback', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('renewal.payment', ['token' => $token])
                ->with('error', 'Payment verification failed. Please contact support.');
        }
    }

    /**
     * Display renewal confirmation page
     *
     * @param string $token The renewal token
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function confirmation($token)
    {
        try {
            $finalOnboarding = FinalOnboarding::where('renewal_token', $token)
                ->with(['kycSubmission', 'partnershipModel'])
                ->first();

            if (!$finalOnboarding) {
                abort(404, 'Invalid request.');
            }

            Log::info('Renewal confirmation page viewed', [
                'final_onboarding_id' => $finalOnboarding->id,
            ]);

            return view('renewal.confirmation', [
                'finalOnboarding' => $finalOnboarding,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying renewal confirmation page', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to load confirmation page');
        }
    }
}
