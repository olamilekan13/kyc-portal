<?php

namespace App\Services;

use App\Models\KycSubmission;
use App\Models\VerificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Exception;

/**
 * YouVerify API Integration Service
 *
 * Handles identity verification and document verification through YouVerify API.
 * Creates audit logs and updates submission records with verification results.
 *
 * @see https://developer.youverify.co/docs
 */
class YouVerifyService
{
    /**
     * YouVerify API key for authentication
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * YouVerify API base URL
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * Initialize the YouVerify service with configuration
     *
     * @throws \RuntimeException If configuration is missing or invalid
     */
    public function __construct()
    {
        $this->apiKey = config('services.youverify.api_key');
        $this->baseUrl = config('services.youverify.base_url');

        // Validate configuration
        if (empty($this->apiKey)) {
            throw new \RuntimeException('YouVerify API key is not configured. Please set YOUVERIFY_API_KEY in your .env file.');
        }

        if (empty($this->baseUrl)) {
            throw new \RuntimeException('YouVerify base URL is not configured. Please set YOUVERIFY_BASE_URL in your .env file.');
        }
    }

    /**
     * Verify identity information through YouVerify API
     *
     * Extracts identity data from the submission, sends verification request,
     * logs the interaction, and updates the submission record.
     *
     * @param KycSubmission $submission The KYC submission to verify
     * @return array{success: bool, verified: bool, data?: array, error?: string, message?: string}
     *
     * @throws Exception If verification process encounters an error
     */
    public function verifyIdentity(KycSubmission $submission): array
    {
        try {
            // Extract identity data from submission
            $identityData = $this->extractIdentityData($submission->submission_data);

            // Validate extracted data
            if (!$this->validateIdentityData($identityData)) {
                return $this->handleValidationError($submission, $identityData);
            }

            // Build API payload
            $payload = [
                'firstName' => $identityData['firstName'],
                'lastName' => $identityData['lastName'],
                'dateOfBirth' => $identityData['dateOfBirth'],
                'identificationType' => $identityData['identificationType'],
                'identificationNumber' => $identityData['identificationNumber'],
                'phoneNumber' => $identityData['phoneNumber'],
                'email' => $identityData['email'],
            ];

            // Log the verification request
            Log::info('YouVerify identity verification initiated', [
                'submission_id' => $submission->id,
                'identification_type' => $identityData['identificationType'],
            ]);

            // Make API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->post($this->baseUrl . '/v2/identities/verify', $payload);

            // Handle response
            return $this->handleVerificationResponse($submission, $payload, $response);
        } catch (RequestException $e) {
            return $this->handleRequestException($submission, $e, 'identity');
        } catch (Exception $e) {
            return $this->handleGeneralException($submission, $e, 'identity');
        }
    }

    /**
     * Verify NIN (National Identification Number) through YouVerify API
     *
     * Verifies a Nigerian National Identification Number and returns
     * the associated identity information including photo, DOB, etc.
     *
     * @param string $nin The 11-digit National Identification Number
     * @return array{success: bool, verified: bool, data?: array, error?: string, message?: string}
     *
     * @throws Exception If NIN verification encounters an error
     */
    public function verifyNIN(string $nin): array
    {
        try {
            // Validate NIN format (11 digits)
            if (!preg_match('/^\d{11}$/', $nin)) {
                Log::warning('YouVerify NIN verification failed: Invalid format', [
                    'nin' => $nin,
                ]);

                return [
                    'success' => false,
                    'verified' => false,
                    'error' => 'Invalid NIN format',
                    'message' => 'NIN must be exactly 11 digits',
                ];
            }

            // Build API payload
            $payload = [
                'id' => $nin,
                'isSubjectConsent' => true, // Required for data protection compliance
            ];

            // Log the verification request
            Log::info('YouVerify NIN verification initiated', [
                'nin' => substr($nin, 0, 3) . '****' . substr($nin, -2), // Masked for privacy
            ]);

            // Make API request to YouVerify NIN endpoint
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->post($this->baseUrl . '/v2/identities/nin', $payload);

            // Handle response
            $responseData = $response->json();
            $statusCode = $response->status();

            if ($response->successful() && isset($responseData['success']) && $responseData['success'] === true) {
                Log::info('YouVerify NIN verification successful', [
                    'nin' => substr($nin, 0, 3) . '****' . substr($nin, -2),
                    'response_status' => $statusCode,
                ]);

                return [
                    'success' => true,
                    'verified' => true,
                    'data' => $responseData['data'] ?? $responseData,
                    'message' => 'NIN verified successfully',
                ];
            }

            // Verification failed
            Log::warning('YouVerify NIN verification failed', [
                'nin' => substr($nin, 0, 3) . '****' . substr($nin, -2),
                'status_code' => $statusCode,
                'response' => $responseData,
            ]);

            return [
                'success' => false,
                'verified' => false,
                'data' => $responseData,
                'error' => $responseData['message'] ?? 'NIN verification failed',
                'message' => 'The NIN could not be verified. Please check and try again.',
            ];
        } catch (RequestException $e) {
            Log::error('YouVerify NIN verification HTTP error', [
                'nin' => substr($nin, 0, 3) . '****' . substr($nin, -2),
                'error' => $e->getMessage(),
                'response' => $e->response?->json(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => 'API request failed: ' . $e->getMessage(),
                'message' => 'Failed to connect to verification service. Please try again later.',
            ];
        } catch (Exception $e) {
            Log::error('YouVerify NIN verification error', [
                'nin' => substr($nin, 0, 3) . '****' . substr($nin, -2),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => $e->getMessage(),
                'message' => 'An unexpected error occurred during NIN verification.',
            ];
        }
    }

    /**
     * Verify liveness selfie and match with NIN photo
     *
     * Performs liveness detection on a selfie image and optionally
     * compares it with the photo from NIN verification.
     *
     * @param string $selfieBase64 Base64 encoded selfie image
     * @param string|null $ninPhoto Base64 or URL of NIN photo for face matching
     * @return array{success: bool, verified: bool, data?: array, error?: string, message?: string}
     *
     * @throws Exception If liveness verification encounters an error
     */
    public function verifyLiveness(string $selfieBase64, ?string $ninPhoto = null): array
    {
        try {
            // Validate base64 image
            if (!$this->isValidBase64Image($selfieBase64)) {
                Log::warning('YouVerify liveness verification failed: Invalid image format');

                return [
                    'success' => false,
                    'verified' => false,
                    'error' => 'Invalid image format',
                    'message' => 'The selfie image format is invalid. Please try again.',
                ];
            }

            // Build API payload
            $payload = [
                'image' => $selfieBase64,
                'isSubjectConsent' => true,
            ];

            // Add reference photo for face matching if provided
            if ($ninPhoto) {
                $payload['referenceImage'] = $ninPhoto;
            }

            // Log the verification request
            Log::info('YouVerify liveness verification initiated', [
                'has_reference_image' => !empty($ninPhoto),
            ]);

            // Make API request to YouVerify liveness endpoint
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(60) // Longer timeout for image processing
                ->post($this->baseUrl . '/v2/identities/faces/liveness', $payload);

            // Handle response
            $responseData = $response->json();
            $statusCode = $response->status();

            if ($response->successful() && isset($responseData['success']) && $responseData['success'] === true) {
                $data = $responseData['data'] ?? [];
                $isLive = $data['isLive'] ?? false;
                $faceMatch = $data['faceMatch'] ?? null;

                Log::info('YouVerify liveness verification successful', [
                    'is_live' => $isLive,
                    'face_match_score' => $faceMatch,
                    'response_status' => $statusCode,
                ]);

                return [
                    'success' => true,
                    'verified' => $isLive && ($ninPhoto ? ($faceMatch >= 0.7) : true), // 70% match threshold
                    'data' => $data,
                    'message' => 'Liveness verification successful',
                ];
            }

            // Verification failed
            Log::warning('YouVerify liveness verification failed', [
                'status_code' => $statusCode,
                'response' => $responseData,
            ]);

            return [
                'success' => false,
                'verified' => false,
                'data' => $responseData,
                'error' => $responseData['message'] ?? 'Liveness verification failed',
                'message' => 'The liveness check failed. Please ensure you are in a well-lit area and try again.',
            ];
        } catch (RequestException $e) {
            Log::error('YouVerify liveness verification HTTP error', [
                'error' => $e->getMessage(),
                'response' => $e->response?->json(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => 'API request failed: ' . $e->getMessage(),
                'message' => 'Failed to connect to verification service. Please try again later.',
            ];
        } catch (Exception $e) {
            Log::error('YouVerify liveness verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => $e->getMessage(),
                'message' => 'An unexpected error occurred during liveness verification.',
            ];
        }
    }

    /**
     * Validate base64 encoded image
     *
     * @param string $base64String The base64 string to validate
     * @return bool True if valid base64 image
     */
    protected function isValidBase64Image(string $base64String): bool
    {
        // Remove data URI scheme if present
        $base64String = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);

        // Check if it's valid base64
        if (!base64_decode($base64String, true)) {
            return false;
        }

        // Decode and check if it's a valid image
        $imageData = base64_decode($base64String);
        $imageInfo = @getimagesizefromstring($imageData);

        return $imageInfo !== false;
    }

    /**
     * Verify document through YouVerify API
     *
     * Uploads and verifies a document (e.g., ID card, passport, driver's license)
     * through the YouVerify document verification endpoint.
     *
     * @param string $documentPath The full path to the document file
     * @param string $documentType The type of document (e.g., 'passport', 'drivers_license', 'national_id')
     * @return array{success: bool, verified: bool, data?: array, error?: string, message?: string}
     *
     * @throws Exception If document verification encounters an error
     */
    public function verifyDocument(string $documentPath, string $documentType): array
    {
        try {
            // Validate document exists
            if (!file_exists($documentPath)) {
                Log::error('YouVerify document verification failed: File not found', [
                    'document_path' => $documentPath,
                ]);

                return [
                    'success' => false,
                    'verified' => false,
                    'error' => 'Document file not found at the specified path',
                    'message' => 'The document file could not be located.',
                ];
            }

            // Validate document type
            $validTypes = ['passport', 'drivers_license', 'national_id', 'voter_card', 'nin_slip'];
            if (!in_array($documentType, $validTypes)) {
                Log::warning('YouVerify document verification: Invalid document type', [
                    'document_type' => $documentType,
                    'valid_types' => $validTypes,
                ]);

                return [
                    'success' => false,
                    'verified' => false,
                    'error' => 'Invalid document type',
                    'message' => 'Document type must be one of: ' . implode(', ', $validTypes),
                ];
            }

            // Prepare multipart form data
            $payload = [
                'documentType' => $documentType,
            ];

            // Log the verification request
            Log::info('YouVerify document verification initiated', [
                'document_type' => $documentType,
                'document_path' => $documentPath,
            ]);

            // Make API request with file upload
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])
                ->timeout(60) // Longer timeout for file uploads
                ->attach('document', file_get_contents($documentPath), basename($documentPath))
                ->post($this->baseUrl . '/v2/documents/verify', $payload);

            // Handle response
            $responseData = $response->json();
            $statusCode = $response->status();

            if ($response->successful() && isset($responseData['success']) && $responseData['success'] === true) {
                Log::info('YouVerify document verification successful', [
                    'document_type' => $documentType,
                    'response' => $responseData,
                ]);

                return [
                    'success' => true,
                    'verified' => $responseData['data']['verified'] ?? true,
                    'data' => $responseData,
                    'message' => 'Document verified successfully',
                ];
            }

            // Verification failed
            Log::warning('YouVerify document verification failed', [
                'document_type' => $documentType,
                'status_code' => $statusCode,
                'response' => $responseData,
            ]);

            return [
                'success' => false,
                'verified' => false,
                'data' => $responseData,
                'error' => $responseData['message'] ?? 'Document verification failed',
                'message' => 'The document could not be verified. Please check the document and try again.',
            ];
        } catch (RequestException $e) {
            Log::error('YouVerify document verification HTTP error', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
                'response' => $e->response?->json(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => 'API request failed: ' . $e->getMessage(),
                'message' => 'Failed to connect to verification service. Please try again later.',
            ];
        } catch (Exception $e) {
            Log::error('YouVerify document verification error', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'verified' => false,
                'error' => $e->getMessage(),
                'message' => 'An unexpected error occurred during document verification.',
            ];
        }
    }

    /**
     * Extract identity data from submission data
     *
     * Maps various possible field names to standardized identity data structure.
     *
     * @param array $submissionData The raw submission data from the KYC form
     * @return array The extracted and normalized identity data
     */
    protected function extractIdentityData(array $submissionData): array
    {
        return [
            'firstName' => $submissionData['first_name']
                ?? $submissionData['firstName']
                ?? $submissionData['given_name']
                ?? '',
            'lastName' => $submissionData['last_name']
                ?? $submissionData['lastName']
                ?? $submissionData['surname']
                ?? $submissionData['family_name']
                ?? '',
            'dateOfBirth' => $submissionData['date_of_birth']
                ?? $submissionData['dateOfBirth']
                ?? $submissionData['dob']
                ?? $submissionData['birth_date']
                ?? '',
            'identificationType' => $submissionData['identification_type']
                ?? $submissionData['identificationType']
                ?? $submissionData['id_type']
                ?? $submissionData['document_type']
                ?? 'national_id',
            'identificationNumber' => $submissionData['identification_number']
                ?? $submissionData['identificationNumber']
                ?? $submissionData['id_number']
                ?? $submissionData['document_number']
                ?? '',
            'phoneNumber' => $submissionData['phone_number']
                ?? $submissionData['phoneNumber']
                ?? $submissionData['phone']
                ?? $submissionData['mobile']
                ?? $submissionData['contact_number']
                ?? '',
            'email' => $submissionData['email']
                ?? $submissionData['email_address']
                ?? '',
        ];
    }

    /**
     * Validate that required identity data fields are present
     *
     * @param array $identityData The extracted identity data
     * @return bool True if all required fields are present and valid
     */
    protected function validateIdentityData(array $identityData): bool
    {
        $requiredFields = ['firstName', 'lastName', 'identificationNumber'];

        foreach ($requiredFields as $field) {
            if (empty($identityData[$field])) {
                Log::warning('YouVerify validation failed: Missing required field', [
                    'field' => $field,
                    'identity_data' => $identityData,
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Handle validation errors by creating a log and returning error response
     *
     * @param KycSubmission $submission The submission record
     * @param array $identityData The invalid identity data
     * @return array Error response array
     */
    protected function handleValidationError(KycSubmission $submission, array $identityData): array
    {
        $missingFields = [];
        foreach (['firstName', 'lastName', 'identificationNumber'] as $field) {
            if (empty($identityData[$field])) {
                $missingFields[] = $field;
            }
        }

        $errorMessage = 'Missing required fields: ' . implode(', ', $missingFields);

        // Create verification log for failed validation
        $submission->verificationLogs()->create([
            'verification_provider' => 'YouVerify',
            'request_payload' => $identityData,
            'response_payload' => [
                'error' => $errorMessage,
                'validation_failed' => true,
            ],
            'status' => 'validation_failed',
        ]);

        // Update submission
        $submission->update([
            'verification_status' => KycSubmission::VERIFICATION_FAILED,
            'verification_response' => [
                'error' => $errorMessage,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        Log::error('YouVerify identity verification validation failed', [
            'submission_id' => $submission->id,
            'missing_fields' => $missingFields,
        ]);

        return [
            'success' => false,
            'verified' => false,
            'error' => $errorMessage,
            'message' => 'Submission data is incomplete. Required fields are missing.',
        ];
    }

    /**
     * Handle verification API response
     *
     * Processes the response from YouVerify, creates verification log,
     * and updates the submission record accordingly.
     *
     * @param KycSubmission $submission The submission record
     * @param array $payload The request payload sent to YouVerify
     * @param \Illuminate\Http\Client\Response $response The API response
     * @return array Result array with success status and data
     */
    protected function handleVerificationResponse(KycSubmission $submission, array $payload, $response): array
    {
        $responseData = $response->json();
        $statusCode = $response->status();

        // Determine verification result
        $isVerified = false;
        $verificationStatus = KycSubmission::VERIFICATION_FAILED;
        $logStatus = 'failed';

        if ($response->successful() && isset($responseData['success']) && $responseData['success'] === true) {
            $isVerified = $responseData['data']['verified'] ?? true;
            $verificationStatus = $isVerified
                ? KycSubmission::VERIFICATION_VERIFIED
                : KycSubmission::VERIFICATION_FAILED;
            $logStatus = $isVerified ? 'success' : 'failed';
        }

        // Create verification log
        $submission->verificationLogs()->create([
            'verification_provider' => 'YouVerify',
            'request_payload' => $payload,
            'response_payload' => $responseData,
            'status' => $logStatus,
        ]);

        // Update submission record
        $submission->update([
            'verification_status' => $verificationStatus,
            'verification_response' => [
                'verified' => $isVerified,
                'data' => $responseData,
                'timestamp' => now()->toIso8601String(),
            ],
            'status' => $isVerified
                ? KycSubmission::STATUS_VERIFIED
                : $submission->status, // Keep current status if verification failed
        ]);

        // Log result
        if ($isVerified) {
            Log::info('YouVerify identity verification successful', [
                'submission_id' => $submission->id,
                'response' => $responseData,
            ]);
        } else {
            Log::warning('YouVerify identity verification failed', [
                'submission_id' => $submission->id,
                'status_code' => $statusCode,
                'response' => $responseData,
            ]);
        }

        return [
            'success' => $response->successful(),
            'verified' => $isVerified,
            'data' => $responseData,
            'message' => $isVerified
                ? 'Identity verified successfully'
                : 'Identity verification failed',
        ];
    }

    /**
     * Handle HTTP request exceptions
     *
     * @param KycSubmission $submission The submission record
     * @param RequestException $e The request exception
     * @param string $verificationType Type of verification ('identity' or 'document')
     * @return array Error response array
     */
    protected function handleRequestException(KycSubmission $submission, RequestException $e, string $verificationType): array
    {
        $responseData = $e->response?->json() ?? [];
        $errorMessage = $responseData['message'] ?? $e->getMessage();

        // Create verification log for the error
        $submission->verificationLogs()->create([
            'verification_provider' => 'YouVerify',
            'request_payload' => [],
            'response_payload' => [
                'error' => $errorMessage,
                'status_code' => $e->response?->status(),
                'response' => $responseData,
            ],
            'status' => 'error',
        ]);

        // Update submission
        $submission->update([
            'verification_status' => KycSubmission::VERIFICATION_FAILED,
            'verification_response' => [
                'error' => $errorMessage,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        Log::error("YouVerify {$verificationType} verification HTTP error", [
            'submission_id' => $submission->id,
            'error' => $errorMessage,
            'status_code' => $e->response?->status(),
            'response' => $responseData,
        ]);

        return [
            'success' => false,
            'verified' => false,
            'error' => 'API request failed: ' . $errorMessage,
            'message' => 'Failed to connect to verification service. Please try again later.',
        ];
    }

    /**
     * Handle general exceptions
     *
     * @param KycSubmission $submission The submission record
     * @param Exception $e The exception
     * @param string $verificationType Type of verification ('identity' or 'document')
     * @return array Error response array
     */
    protected function handleGeneralException(KycSubmission $submission, Exception $e, string $verificationType): array
    {
        // Create verification log for the error
        $submission->verificationLogs()->create([
            'verification_provider' => 'YouVerify',
            'request_payload' => [],
            'response_payload' => [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ],
            'status' => 'error',
        ]);

        // Update submission
        $submission->update([
            'verification_status' => KycSubmission::VERIFICATION_FAILED,
            'verification_response' => [
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        Log::error("YouVerify {$verificationType} verification error", [
            'submission_id' => $submission->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'verified' => false,
            'error' => $e->getMessage(),
            'message' => 'An unexpected error occurred during verification. Please try again.',
        ];
    }
}
