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
 * API Controller for Liveness Selfie Verification
 *
 * Handles liveness detection and face matching with NIN photo
 * for KYC verification purposes.
 */
class LivenessVerificationController extends Controller
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
     * Verify liveness selfie and optionally match with NIN photo
     *
     * This endpoint is called via AJAX from the KYC form when users
     * capture their selfie. It performs liveness detection and face matching.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            // Rate limiting: 3 attempts per minute per IP (more strict due to processing cost)
            $key = 'liveness-verify:' . $request->ip();

            if (RateLimiter::tooManyAttempts($key, 3)) {
                $seconds = RateLimiter::availableIn($key);

                Log::warning('Liveness verification rate limit exceeded', [
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
                'selfie' => [
                    'required',
                    'string',
                ],
                'nin_photo' => [
                    'nullable',
                    'string',
                ],
            ], [
                'selfie.required' => 'Selfie image is required',
            ]);

            RateLimiter::hit($key, 60); // Increment rate limiter

            Log::info('Liveness verification request received', [
                'has_nin_photo' => !empty($validated['nin_photo']),
                'ip' => $request->ip(),
            ]);

            // Extract base64 image data
            $selfieBase64 = $validated['selfie'];
            $ninPhoto = $validated['nin_photo'] ?? null;

            // Call YouVerify API for liveness verification
            $result = $this->youVerifyService->verifyLiveness($selfieBase64, $ninPhoto);

            // If verification successful
            if ($result['success'] && $result['verified']) {
                Log::info('Liveness verification successful', [
                    'is_live' => $result['data']['isLive'] ?? false,
                    'face_match' => $result['data']['faceMatch'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'verified' => true,
                    'message' => 'Liveness verified successfully',
                    'data' => [
                        'is_live' => $result['data']['isLive'] ?? true,
                        'face_match_score' => $result['data']['faceMatch'] ?? null,
                        'confidence' => $result['data']['confidence'] ?? null,
                    ],
                ], 200);
            }

            // Verification failed
            Log::warning('Liveness verification failed', [
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => $result['message'] ?? 'Liveness verification failed',
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
            Log::error('Liveness verification API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'verified' => false,
                'message' => 'An error occurred during liveness verification. Please try again.',
            ], 500);
        }
    }
}
