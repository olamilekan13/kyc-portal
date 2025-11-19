<?php

namespace App\Http\Controllers;

use App\Models\KycForm;
use App\Models\KycFormField;
use App\Models\KycSubmission;
use App\Models\SystemSetting;
use App\Mail\KycSubmissionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

/**
 * Controller for public-facing KYC submission system
 *
 * Handles displaying KYC forms, validating submissions,
 * and processing file uploads for public users.
 */
class KycSubmissionController extends Controller
{
    /**
     * Display the default KYC form
     *
     * Accessed via: /kyc (without any slug or ID)
     * Shows the form marked as "default" in admin panel
     *
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function showDefault()
    {
        try {
            // Get the default form
            $kycForm = KycForm::getDefault();

            // Return 404 if no default form is set
            if (!$kycForm) {
                abort(404, 'No default KYC form has been set. Please contact the administrator.');
            }

            Log::info('Default KYC form viewed', [
                'form_id' => $kycForm->id,
                'form_name' => $kycForm->name,
                'form_slug' => $kycForm->slug,
                'ip_address' => request()->ip(),
            ]);

            // Return view with form data
            return view('kyc.form', [
                'form' => $kycForm,
                'fields' => $kycForm->fields,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying default KYC form', [
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to load default KYC form');
        }
    }

    /**
     * Display the KYC form
     *
     * Supports both slug and ID lookup for backwards compatibility.
     * Examples: /kyc/company-onboarding OR /kyc/7
     *
     * @param string $form The KYC form slug or ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show($form)
    {
        try {
            // Try to find form by slug first, then by ID
            $kycForm = KycForm::with('fields')
                ->where('status', true)
                ->where(function ($query) use ($form) {
                    $query->where('slug', $form)
                          ->orWhere('id', is_numeric($form) ? $form : null);
                })
                ->first();

            // Return 404 if form not found or inactive
            if (!$kycForm) {
                abort(404, 'KYC form not found or is no longer active');
            }

            Log::info('KYC form viewed', [
                'form_id' => $kycForm->id,
                'form_name' => $kycForm->name,
                'form_slug' => $kycForm->slug,
                'accessed_via' => $form,
                'ip_address' => request()->ip(),
            ]);

            // Return view with form data
            return view('kyc.form', [
                'form' => $kycForm,
                'fields' => $kycForm->fields,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying KYC form', [
                'form_identifier' => $form,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Unable to load KYC form');
        }
    }

    /**
     * Submit the KYC form
     *
     * Validates submission data based on dynamic form fields,
     * handles file uploads, and creates submission record.
     *
     * @param Request $request
     * @param string $form The KYC form slug or ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, $form)
    {
        try {
            // Load KYC form with fields (by slug or ID)
            $kycForm = KycForm::with('fields')
                ->where('status', true)
                ->where(function ($query) use ($form) {
                    $query->where('slug', $form)
                          ->orWhere('id', is_numeric($form) ? $form : null);
                })
                ->first();

            // Return 404 if form not found
            if (!$kycForm) {
                abort(404, 'KYC form not found or is no longer active');
            }

            // From here on, use $kycForm as $form for compatibility with existing code
            $form = $kycForm;

            Log::info('KYC form submission initiated', [
                'form_id' => $form->id,
                'form_name' => $form->name,
                'form_slug' => $form->slug,
                'ip_address' => $request->ip(),
            ]);

            // Build validation rules dynamically based on form fields
            $validationRules = $this->buildValidationRules($form->fields);

            // Validate request
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                Log::warning('KYC form validation failed', [
                    'form_id' => $form->id,
                    'errors' => $validator->errors()->toArray(),
                    'ip_address' => $request->ip(),
                ]);

                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $validatedData = $validator->validated();

            // Handle file uploads
            $submissionData = $this->handleFileUploads($request, $form->fields, $validatedData);

            // Create KYC submission with onboarding token
            $submission = $form->submissions()->create([
                'submission_data' => $submissionData,
                'status' => KycSubmission::STATUS_PENDING,
                'verification_status' => KycSubmission::VERIFICATION_NOT_VERIFIED,
                'onboarding_token' => KycSubmission::generateOnboardingToken(),
                'onboarding_status' => 'pending',
            ]);

            Log::info('KYC submission created successfully', [
                'form_id' => $form->id,
                'submission_id' => $submission->id,
                'onboarding_token' => $submission->onboarding_token,
                'ip_address' => $request->ip(),
            ]);

            // Send notification email to admin
            try {
                $notificationEmail = SystemSetting::get('kyc_notification_email', config('mail.from.address'));

                if ($notificationEmail) {
                    Mail::to($notificationEmail)->send(new KycSubmissionNotification($submission));

                    Log::info('KYC submission notification email sent', [
                        'submission_id' => $submission->id,
                        'recipient' => $notificationEmail,
                    ]);
                }
            } catch (Exception $e) {
                // Log error but don't fail the submission
                Log::error('Failed to send KYC submission notification email', [
                    'submission_id' => $submission->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Redirect to final onboarding page with token
            return redirect()->route('onboarding.show', ['token' => $submission->onboarding_token]);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (Exception $e) {
            Log::error('Error submitting KYC form', [
                'form_id' => $form->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip_address' => $request->ip(),
            ]);

            return back()
                ->withErrors(['error' => 'An error occurred while submitting your form. Please try again.'])
                ->withInput();
        }
    }

    /**
     * Display submission success page
     *
     * @param int $submissionId The submission ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function success($submissionId)
    {
        try {
            // Find submission
            $submission = KycSubmission::findOrFail($submissionId);

            Log::info('KYC submission success page viewed', [
                'submission_id' => $submissionId,
                'ip_address' => request()->ip(),
            ]);

            // Return success view with submission reference number
            return view('kyc.success', [
                'submission' => $submission,
                'referenceNumber' => $submission->id,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying KYC success page', [
                'submission_id' => $submissionId,
                'error' => $e->getMessage(),
            ]);

            abort(404, 'Submission not found');
        }
    }

    /**
     * Build validation rules dynamically based on form fields
     *
     * @param \Illuminate\Database\Eloquent\Collection $fields
     * @return array
     */
    protected function buildValidationRules($fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $fieldRules = [];

            // Add 'required' if field is required
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add type-specific validation rules
            switch ($field->field_type) {
                case 'email':
                    $fieldRules[] = 'email';
                    $fieldRules[] = 'max:255';
                    break;

                case 'phone':
                    // Phone validation regex (supports international formats)
                    $fieldRules[] = 'regex:/^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/';
                    $fieldRules[] = 'max:20';
                    break;

                case 'nin':
                    // NIN must be 11 digits
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'regex:/^\d{11}$/';

                    // Validate that NIN was verified (check hidden field)
                    if ($field->is_required) {
                        $rules[$field->field_name . '_verified'] = ['required', 'in:1'];
                    }
                    break;

                case 'liveness_selfie':
                    // Liveness selfie must be base64 encoded image
                    $fieldRules[] = 'string';

                    // Validate that liveness was verified (check hidden field)
                    if ($field->is_required) {
                        $rules[$field->field_name . '_verified'] = ['required', 'in:1'];
                    }
                    break;

                case 'file':
                    if ($field->is_required) {
                        $fieldRules[] = 'file';
                    }
                    $fieldRules[] = 'max:5120'; // 5MB max
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
                    // Validate against available options if provided
                    if (!empty($field->options) && is_array($field->options)) {
                        $fieldRules[] = 'in:' . implode(',', array_keys($field->options));
                    }
                    break;
            }

            // Merge custom validation rules from field configuration
            if (!empty($field->validation_rules) && is_array($field->validation_rules)) {
                foreach ($field->validation_rules as $customRule) {
                    if (!in_array($customRule, $fieldRules)) {
                        $fieldRules[] = $customRule;
                    }
                }
            }

            // Assign rules to field name
            $rules[$field->field_name] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Handle file uploads for file fields
     *
     * Stores files in private storage and replaces file objects
     * with file metadata in submission data.
     *
     * @param Request $request
     * @param \Illuminate\Database\Eloquent\Collection $fields
     * @param array $validatedData
     * @return array
     */
    protected function handleFileUploads(Request $request, $fields, array $validatedData): array
    {
        $submissionData = $validatedData;

        foreach ($fields as $field) {
            if ($field->field_type === 'file' && $request->hasFile($field->field_name)) {
                $file = $request->file($field->field_name);

                if ($file && $file->isValid()) {
                    try {
                        // Generate unique filename
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                        // Store file in private storage
                        $path = $file->storeAs(
                            'kyc-documents',
                            $filename,
                            'private'
                        );

                        // Replace file object with metadata
                        $submissionData[$field->field_name] = [
                            'original_name' => $file->getClientOriginalName(),
                            'filename' => $filename,
                            'path' => $path,
                            'size' => $file->getSize(),
                            'mime_type' => $file->getMimeType(),
                            'uploaded_at' => now()->toIso8601String(),
                        ];

                        Log::info('File uploaded successfully', [
                            'field_name' => $field->field_name,
                            'filename' => $filename,
                            'path' => $path,
                        ]);
                    } catch (Exception $e) {
                        Log::error('File upload failed', [
                            'field_name' => $field->field_name,
                            'error' => $e->getMessage(),
                        ]);

                        throw new Exception('Failed to upload file for ' . $field->field_label);
                    }
                }
            }
        }

        return $submissionData;
    }
}
