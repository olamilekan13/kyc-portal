<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\YouVerifyService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Exception;

/**
 * API Controller for NIN Verification
 *
 * Handles real-time NIN verification requests from the KYC form
 * and returns verification data including auto-population fields.
 */
class NinVerificationController extends Controller
{
    /**
     * YouVerify service instance
     *
     * @var YouVerifyService
     */
    protected YouVerifyService $youVerifyService;

    /**
     * Initialize controller with dependencies
     *
     * @param YouVerifyService $youVerifyService
     */
    public function __construct(YouVerifyService $youVerifyService)
    {
        $this->youVerifyService = $youVerifyService;
    }

    /**
     * Verify NIN and return identity data
     *
     * This endpoint is called via AJAX from the KYC form when users
     * enter their NIN. It verifies the NIN and returns data for
     * auto-population of other form fields.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            // Rate limiting: 5 attempts per minute per IP
            $key = 'nin-verify:' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);

                Log::warning('NIN verification rate limit exceeded', [
                    'ip' => $request->ip(),
                    'available_in' => $seconds,
                ]);

                return response()->json([
                    'success' => false,
                    'verified' => false,
                    'message' => 'Too many verification attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).',
                ], 429);
            }

            // Validate request
            $validated = $request->validate([
                'nin' => [
                    'required',
                    'string',
                    'regex:/^\d{11}$/',
                ],
            ], [
                'nin.required' => 'NIN is required',
                'nin.regex' => 'NIN must be exactly 11 digits',
            ]);

            RateLimiter::hit($key, 60); // Increment rate limiter

            Log::info('NIN verification request received', [
                'nin' => substr($validated['nin'], 0, 3) . '****' . substr($validated['nin'], -2),
                'ip' => $request->ip(),
            ]);

            // Call YouVerify API
            $result = $this->youVerifyService->verifyNIN($validated['nin']);

            // If verification successful, extract and format data for auto-population
            if ($result['success'] && $result['verified']) {
                $ninData = $result['data'] ?? [];

                // Map YouVerify response to form fields
                // Note: Adjust these field names based on actual YouVerify API response
                $autoPopulateData = $this->extractAutoPopulateData($ninData);

                Log::info('NIN verification successful', [
                    'nin' => substr($validated['nin'], 0, 3) . '****' . substr($validated['nin'], -2),
                    'has_photo' => isset($ninData['photo']),
                ]);

                return response()->json([
                    'success' => true,
                    'verified' => true,
                    'message' => 'NIN verified successfully',
                    'data' => $autoPopulateData,
                ], 200);
            }

            // Verification failed
            Log::warning('NIN verification failed', [
                'nin' => substr($validated['nin'], 0, 3) . '****' . substr($validated['nin'], -2),
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => $result['message'] ?? 'NIN verification failed',
                'error' => $result['error'] ?? null,
            ], 422);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('NIN verification API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'An error occurred during verification. Please try again.',
            ], 500);
        }
    }

    /**
     * Extract and map YouVerify NIN data to form fields
     *
     * Maps the YouVerify API response to standardized field names
     * that can be used to auto-populate the KYC form.
     *
     * @param array $ninData Raw data from YouVerify API
     * @return array Formatted data for auto-population
     */
    protected function extractAutoPopulateData(array $ninData): array
    {
        // Map YouVerify NIN response structure to form fields
        // Based on YouVerify API docs: data contains firstName, lastName, middleName, dateOfBirth, mobile, address object, image, etc.

        // Handle address object (contains town, lga, state, addressLine)
        $address = $ninData['address'] ?? [];
        $fullAddress = $address['addressLine'] ?? ($ninData['residentialAddress'] ?? null);

        return [
            'first_name' => $ninData['firstName'] ?? null,
            'last_name' => $ninData['lastName'] ?? null,
            'middle_name' => $ninData['middleName'] ?? null,
            'date_of_birth' => $ninData['dateOfBirth'] ?? null,
            'phone_number' => $ninData['mobile'] ?? ($ninData['phoneNumber'] ?? null),
            'email' => $ninData['email'] ?? null,
            'gender' => $ninData['gender'] ?? null,
            'address' => $fullAddress,
            'state' => $address['state'] ?? ($ninData['state'] ?? null),
            'lga' => $address['lga'] ?? ($ninData['lga'] ?? null),
            'photo' => $ninData['image'] ?? ($ninData['photo'] ?? null), // Base64 image from NIN
            'nin_verified' => true,
            'nin_verification_date' => now()->toIso8601String(),
        ];
    }
}
