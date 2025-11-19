<?php

namespace App\Http\Controllers;

use App\Models\KycSubmission;
use App\Models\PartnershipModel;
use App\Models\FinalOnboarding;
use App\Models\FinalOnboardingForm;
use App\Models\SystemSetting;
use App\Mail\FinalOnboardingNotification;
use App\Mail\PaymentSubmissionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Exception;

class FinalOnboardingController extends Controller
{
    /**
     * Display the final onboarding form
     *
     * @param string $token The onboarding token
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show($token)
    {
        try {
            // Find KYC submission by token
            $kycSubmission = KycSubmission::where('onboarding_token', $token)->first();

            if (!$kycSubmission) {
                abort(404, 'Invalid onboarding link. Please contact support.');
            }

            // Check if onboarding is already completed
            if ($kycSubmission->isOnboardingCompleted()) {
                return view('onboarding.already-completed', [
                    'kycSubmission' => $kycSubmission,
                ]);
            }

            // Update status to in_progress
            if ($kycSubmission->onboarding_status === 'pending') {
                $kycSubmission->update(['onboarding_status' => 'in_progress']);
            }

            // Get the default final onboarding form
            $onboardingForm = FinalOnboardingForm::getDefault();

            if (!$onboardingForm) {
                abort(404, 'No final onboarding form configured. Please contact support.');
            }

            // Get active partnership models
            $partnershipModels = PartnershipModel::active()->get();

            // Get system settings
            $signupFee = SystemSetting::get('signup_fee_amount', 5000);
            $solarPowerAmount = SystemSetting::get('solar_power_amount', 0);
            $bankName = SystemSetting::get('bank_name', '');
            $bankAccountNumber = SystemSetting::get('bank_account_number', '');
            $bankAccountName = SystemSetting::get('bank_account_name', '');
            $paystackPublicKey = SystemSetting::get('paystack_public_key', '');

            // Check if final onboarding already exists
            $finalOnboarding = $kycSubmission->finalOnboarding;

            Log::info('Final onboarding form viewed', [
                'kyc_submission_id' => $kycSubmission->id,
                'onboarding_token' => $token,
                'onboarding_form_id' => $onboardingForm->id,
                'ip_address' => request()->ip(),
            ]);

            return view('onboarding.form', [
                'kycSubmission' => $kycSubmission,
                'onboardingForm' => $onboardingForm,
                'partnershipModels' => $partnershipModels,
                'signupFee' => $signupFee,
                'solarPowerAmount' => $solarPowerAmount,
                'bankName' => $bankName,
                'bankAccountNumber' => $bankAccountNumber,
                'bankAccountName' => $bankAccountName,
                'paystackPublicKey' => $paystackPublicKey,
                'finalOnboarding' => $finalOnboarding,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying final onboarding form', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to load onboarding form');
        }
    }

    /**
     * Submit the partnership model selection and dynamic form data
     *
     * @param Request $request
     * @param string $token The onboarding token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitSelection(Request $request, $token)
    {
        try {
            // Find KYC submission by token
            $kycSubmission = KycSubmission::where('onboarding_token', $token)->first();

            if (!$kycSubmission) {
                abort(404, 'Invalid onboarding link.');
            }

            // Get the default final onboarding form
            $onboardingForm = FinalOnboardingForm::getDefault();

            if (!$onboardingForm) {
                abort(404, 'No final onboarding form configured.');
            }

            // Build validation rules for dynamic form fields
            $validationRules = $this->buildValidationRules($onboardingForm);

            // Add partnership and payment method validation
            $validationRules['partnership_model_id'] = ['required', 'exists:partnership_models,id'];
            $validationRules['payment_method'] = ['required', 'in:bank_transfer,paystack'];

            // Validate request
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Handle file uploads
            $formData = $this->handleFileUploads($request, $onboardingForm);

            // Get partnership model
            $partnershipModel = PartnershipModel::findOrFail($request->partnership_model_id);

            // Get signup fee
            $signupFee = SystemSetting::get('signup_fee_amount', 5000);

            // Handle solar power
            $solarPower = $request->input('solar_power') === 'yes';
            $solarPowerAmount = 0;

            if ($solarPower) {
                $solarPowerAmount = SystemSetting::get('solar_power_amount', 0);
            }

            // Calculate total amount
            $totalAmount = $signupFee + $partnershipModel->price + $solarPowerAmount;

            // Create or update final onboarding record
            $finalOnboarding = FinalOnboarding::updateOrCreate(
                ['kyc_submission_id' => $kycSubmission->id],
                [
                    'final_onboarding_form_id' => $onboardingForm->id,
                    'form_data' => $formData,
                    'partnership_model_id' => $partnershipModel->id,
                    'partnership_model_name' => $partnershipModel->name,
                    'partnership_model_price' => $partnershipModel->price,
                    'signup_fee_amount' => $signupFee,
                    'solar_power' => $solarPower,
                    'solar_power_amount' => $solarPowerAmount,
                    'total_amount' => $totalAmount,
                    'payment_method' => $request->payment_method,
                ]
            );

            Log::info('Final onboarding selection submitted', [
                'kyc_submission_id' => $kycSubmission->id,
                'final_onboarding_id' => $finalOnboarding->id,
                'final_onboarding_form_id' => $onboardingForm->id,
                'partnership_model_id' => $partnershipModel->id,
                'payment_method' => $request->payment_method,
            ]);

            // Send email notification to admin
            try {
                $notificationEmail = SystemSetting::get('onboarding_notification_email')
                    ?? SystemSetting::get('admin_notification_email');

                if ($notificationEmail) {
                    Mail::to($notificationEmail)->send(
                        new FinalOnboardingNotification($kycSubmission, $finalOnboarding)
                    );

                    Log::info('Final onboarding notification email sent', [
                        'final_onboarding_id' => $finalOnboarding->id,
                        'recipient' => $notificationEmail,
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Failed to send final onboarding notification email', [
                    'final_onboarding_id' => $finalOnboarding->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the request if email fails
            }

            // Redirect to payment page
            return redirect()->route('onboarding.payment', ['token' => $token]);
        } catch (Exception $e) {
            Log::error('Error submitting final onboarding selection', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'An error occurred while processing your selection. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Build validation rules from form fields
     *
     * @param FinalOnboardingForm $form
     * @return array
     */
    private function buildValidationRules(FinalOnboardingForm $form): array
    {
        $rules = [];

        foreach ($form->fields as $field) {
            $fieldRules = [];

            // Add required rule if field is required
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add field type specific validation
            switch ($field->field_type) {
                case 'email':
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:255';
                    break;

                case 'phone':
                    $fieldRules[] = 'regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,11}$/';
                    $fieldRules[] = 'max:20';
                    break;

                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:5120'; // 5MB
                    $fieldRules[] = 'mimes:pdf,jpg,jpeg,png';
                    break;

                case 'date':
                    $fieldRules[] = 'date';
                    break;

                case 'number':
                    $fieldRules[] = 'numeric';
                    break;

                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:5000';
                    break;

                case 'select':
                    if ($field->options && is_array($field->options)) {
                        $fieldRules[] = 'in:' . implode(',', array_keys($field->options));
                    }
                    break;
            }

            // Add custom validation rules
            if ($field->validation_rules && is_array($field->validation_rules)) {
                $fieldRules = array_merge($fieldRules, $field->validation_rules);
            }

            $rules[$field->field_name] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Handle file uploads for form fields
     *
     * @param Request $request
     * @param FinalOnboardingForm $form
     * @return array
     */
    private function handleFileUploads(Request $request, FinalOnboardingForm $form): array
    {
        $formData = [];

        foreach ($form->fields as $field) {
            $fieldName = $field->field_name;

            if ($field->field_type === 'file' && $request->hasFile($fieldName)) {
                $file = $request->file($fieldName);

                // Store file in private storage
                $path = $file->store('onboarding-documents', 'local');

                // Store file metadata
                $formData[$fieldName] = [
                    'original_name' => $file->getClientOriginalName(),
                    'filename' => basename($path),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            } else {
                // Store regular field value
                $formData[$fieldName] = $request->input($fieldName);
            }
        }

        return $formData;
    }

    /**
     * Display the payment page
     *
     * @param string $token The onboarding token
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function payment($token)
    {
        try {
            // Find KYC submission by token
            $kycSubmission = KycSubmission::where('onboarding_token', $token)
                ->with('finalOnboarding.partnershipModel')
                ->first();

            if (!$kycSubmission) {
                abort(404, 'Invalid onboarding link.');
            }

            // Check if selection was made
            if (!$kycSubmission->finalOnboarding) {
                return redirect()->route('onboarding.show', ['token' => $token])
                    ->with('error', 'Please select a partnership model first.');
            }

            $finalOnboarding = $kycSubmission->finalOnboarding;

            // Get payment details
            $bankName = SystemSetting::get('bank_name', '');
            $bankAccountNumber = SystemSetting::get('bank_account_number', '');
            $bankAccountName = SystemSetting::get('bank_account_name', '');
            $paystackPublicKey = SystemSetting::get('paystack_public_key', '');

            Log::info('Payment page viewed', [
                'kyc_submission_id' => $kycSubmission->id,
                'final_onboarding_id' => $finalOnboarding->id,
            ]);

            return view('onboarding.payment', [
                'kycSubmission' => $kycSubmission,
                'finalOnboarding' => $finalOnboarding,
                'bankName' => $bankName,
                'bankAccountNumber' => $bankAccountNumber,
                'bankAccountName' => $bankAccountName,
                'paystackPublicKey' => $paystackPublicKey,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying payment page', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to load payment page');
        }
    }

    /**
     * Process bank transfer payment
     *
     * @param Request $request
     * @param string $token The onboarding token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processBankTransfer(Request $request, $token)
    {
        try {
            // Find KYC submission by token
            $kycSubmission = KycSubmission::where('onboarding_token', $token)->first();

            if (!$kycSubmission || !$kycSubmission->finalOnboarding) {
                abort(404, 'Invalid request.');
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'payment_type' => ['required', 'in:signup_fee,model_fee,both'],
                'payment_reference' => ['nullable', 'string', 'max:255'],
                'payment_notes' => ['nullable', 'string', 'max:1000'],
                'payment_proof' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $finalOnboarding = $kycSubmission->finalOnboarding;

            // Handle payment proof file upload
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                $path = $file->store('payment-proofs', 'local');
                $finalOnboarding->payment_proof = $path;
            }

            // Update payment information based on type
            if ($request->payment_type === 'signup_fee' || $request->payment_type === 'both') {
                $finalOnboarding->signup_fee_paid = false; // Admin will verify
                $finalOnboarding->signup_fee_reference = $request->payment_reference;
            }

            if ($request->payment_type === 'model_fee' || $request->payment_type === 'both') {
                $finalOnboarding->model_fee_paid = false; // Admin will verify
                $finalOnboarding->model_fee_reference = $request->payment_reference;
            }

            $finalOnboarding->payment_notes = $request->payment_notes;
            $finalOnboarding->payment_status = 'pending';
            $finalOnboarding->save();

            Log::info('Bank transfer payment submitted', [
                'final_onboarding_id' => $finalOnboarding->id,
                'payment_type' => $request->payment_type,
                'payment_reference' => $request->payment_reference,
            ]);

            // Send email notification to admin
            try {
                $notificationEmail = SystemSetting::get('onboarding_notification_email')
                    ?? SystemSetting::get('admin_notification_email');

                if ($notificationEmail) {
                    Mail::to($notificationEmail)->send(
                        new PaymentSubmissionNotification($kycSubmission, $finalOnboarding)
                    );

                    Log::info('Payment submission notification email sent', [
                        'final_onboarding_id' => $finalOnboarding->id,
                        'recipient' => $notificationEmail,
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Failed to send payment submission notification email', [
                    'final_onboarding_id' => $finalOnboarding->id,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the request if email fails
            }

            // Redirect to confirmation page
            return redirect()->route('onboarding.confirmation', ['token' => $token])
                ->with('success', 'Payment information submitted successfully. Admin will verify your payment.');
        } catch (Exception $e) {
            Log::error('Error processing bank transfer payment', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'An error occurred while processing your payment. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Process Paystack payment callback
     *
     * @param Request $request
     * @param string $token The onboarding token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paystackCallback(Request $request, $token)
    {
        try {
            // Find KYC submission by token
            $kycSubmission = KycSubmission::where('onboarding_token', $token)->first();

            if (!$kycSubmission || !$kycSubmission->finalOnboarding) {
                abort(404, 'Invalid request.');
            }

            $reference = $request->query('reference');
            $paymentType = $request->query('payment_type');

            if (!$reference) {
                return redirect()->route('onboarding.payment', ['token' => $token])
                    ->with('error', 'Payment reference not found.');
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

            $finalOnboarding = $kycSubmission->finalOnboarding;

            // Update payment status based on type
            if ($paymentType === 'signup_fee' || $paymentType === 'both') {
                $finalOnboarding->signup_fee_paid = true;
                $finalOnboarding->signup_fee_reference = $reference;
                $finalOnboarding->signup_fee_paid_at = now();
            }

            if ($paymentType === 'model_fee' || $paymentType === 'both') {
                $finalOnboarding->model_fee_paid = true;
                $finalOnboarding->model_fee_reference = $reference;
                $finalOnboarding->model_fee_paid_at = now();
            }

            $finalOnboarding->paystack_response = $result;
            $finalOnboarding->updatePaymentStatus();

            // Mark onboarding as completed if fully paid
            if ($finalOnboarding->isFullyPaid()) {
                $kycSubmission->onboarding_status = 'completed';
                $kycSubmission->onboarding_completed_at = now();
                $kycSubmission->save();
            }

            Log::info('Paystack payment verified', [
                'final_onboarding_id' => $finalOnboarding->id,
                'payment_type' => $paymentType,
                'reference' => $reference,
            ]);

            return redirect()->route('onboarding.confirmation', ['token' => $token])
                ->with('success', 'Payment successful!');
        } catch (Exception $e) {
            Log::error('Error processing Paystack callback', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('onboarding.payment', ['token' => $token])
                ->with('error', 'Payment verification failed. Please contact support.');
        }
    }

    /**
     * Display payment confirmation page
     *
     * @param string $token The onboarding token
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function confirmation($token)
    {
        try {
            // Find KYC submission by token
            $kycSubmission = KycSubmission::where('onboarding_token', $token)
                ->with('finalOnboarding')
                ->first();

            if (!$kycSubmission || !$kycSubmission->finalOnboarding) {
                abort(404, 'Invalid request.');
            }

            Log::info('Payment confirmation page viewed', [
                'kyc_submission_id' => $kycSubmission->id,
            ]);

            return view('onboarding.confirmation', [
                'kycSubmission' => $kycSubmission,
                'finalOnboarding' => $kycSubmission->finalOnboarding,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying confirmation page', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to load confirmation page');
        }
    }

    /**
     * Download payment receipt
     *
     * @param string $token The onboarding token
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function downloadReceipt($token)
    {
        try {
            // Find KYC submission by token
            $kycSubmission = KycSubmission::where('onboarding_token', $token)
                ->with('finalOnboarding')
                ->first();

            if (!$kycSubmission || !$kycSubmission->finalOnboarding) {
                abort(404, 'Invalid request.');
            }

            $finalOnboarding = $kycSubmission->finalOnboarding;

            Log::info('Payment receipt downloaded', [
                'kyc_submission_id' => $kycSubmission->id,
                'final_onboarding_id' => $finalOnboarding->id,
            ]);

            return view('onboarding.receipt', [
                'kycSubmission' => $kycSubmission,
                'finalOnboarding' => $finalOnboarding,
            ]);
        } catch (Exception $e) {
            Log::error('Error generating receipt', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to generate receipt');
        }
    }
}
