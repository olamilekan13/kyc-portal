<?php

namespace App\Http\Controllers;

use App\Models\KycForm;
use App\Models\KycFormField;
use App\Models\KycSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     * Display the KYC form
     *
     * @param int $formId The KYC form ID
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function show($formId)
    {
        try {
            // Find active KYC form with fields relationship
            $form = KycForm::with('fields')
                ->where('id', $formId)
                ->where('status', true)
                ->first();

            // Return 404 if form not found or inactive
            if (!$form) {
                abort(404, 'KYC form not found or is no longer active');
            }

            Log::info('KYC form viewed', [
                'form_id' => $formId,
                'form_name' => $form->name,
                'ip_address' => request()->ip(),
            ]);

            // Return view with form data
            return view('kyc.form', [
                'form' => $form,
                'fields' => $form->fields,
            ]);
        } catch (Exception $e) {
            Log::error('Error displaying KYC form', [
                'form_id' => $formId,
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
     * @param int $formId The KYC form ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request, $formId)
    {
        try {
            // Load KYC form with fields
            $form = KycForm::with('fields')
                ->where('id', $formId)
                ->where('status', true)
                ->first();

            if (!$form) {
                return back()
                    ->withErrors(['form' => 'KYC form not found or is no longer active'])
                    ->withInput();
            }

            Log::info('KYC form submission initiated', [
                'form_id' => $formId,
                'form_name' => $form->name,
                'ip_address' => $request->ip(),
            ]);

            // Build validation rules dynamically based on form fields
            $validationRules = $this->buildValidationRules($form->fields);

            // Validate request
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                Log::warning('KYC form validation failed', [
                    'form_id' => $formId,
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

            // Create KYC submission
            $submission = $form->submissions()->create([
                'submission_data' => $submissionData,
                'status' => KycSubmission::STATUS_PENDING,
                'verification_status' => KycSubmission::VERIFICATION_NOT_VERIFIED,
            ]);

            Log::info('KYC submission created successfully', [
                'form_id' => $formId,
                'submission_id' => $submission->id,
                'ip_address' => $request->ip(),
            ]);

            // Redirect to success page
            return redirect()->route('kyc.success', ['submissionId' => $submission->id]);
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (Exception $e) {
            Log::error('Error submitting KYC form', [
                'form_id' => $formId,
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
                    $fieldRules[] = 'size:11';

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
