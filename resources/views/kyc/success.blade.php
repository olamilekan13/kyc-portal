@extends('layouts.public')

@section('title', 'Submission Successful - KYC Portal')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <!-- Success Icon -->
        <div class="flex justify-center mb-6">
            <div class="rounded-full bg-green-100 p-4">
                <svg class="h-16 w-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Success Message -->
        <h2 class="text-3xl font-bold text-gray-900 mb-4">
            Submission Successful!
        </h2>

        <p class="text-gray-600 mb-6">
            Thank you for submitting your KYC application. Your information has been received and is being reviewed.
        </p>

        <!-- Reference Number -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <p class="text-sm text-gray-600 mb-2">Your Reference Number</p>
            <div class="flex items-center justify-center">
                <span class="text-3xl font-bold text-blue-600 font-mono">
                    #{{ str_pad($referenceNumber, 6, '0', STR_PAD_LEFT) }}
                </span>
                <button
                    onclick="copyToClipboard('{{ $referenceNumber }}')"
                    class="ml-3 p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-100 rounded transition"
                    title="Copy reference number"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                Please save this reference number for future inquiries
            </p>
        </div>

        <!-- Information Box -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-left mb-6">
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                What happens next?
            </h3>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                    <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Your submission will be verified through our verification system</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Our team will review your information within 2-3 business days</span>
                </li>
                <li class="flex items-start">
                    <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>You will receive an email notification once your application is processed</span>
                </li>
            </ul>
        </div>

        <!-- Submission Details -->
        <div class="text-left space-y-2 text-sm text-gray-600 border-t border-gray-200 pt-6">
            <div class="flex justify-between">
                <span class="font-medium">Submission ID:</span>
                <span class="font-mono">{{ $submission->id }}</span>
            </div>
            <div class="flex justify-between">
                <span class="font-medium">Status:</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    {{ ucwords(str_replace('_', ' ', $submission->status)) }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="font-medium">Submitted On:</span>
                <span>{{ $submission->created_at->format('F d, Y \a\t H:i A') }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a
                href="/"
                class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Return to Home
            </a>
            <button
                onclick="window.print()"
                class="inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-lg text-white bg-blue-600 hover:bg-blue-700 font-medium transition"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Confirmation
            </button>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-500">
            Questions? Contact us at
            <a href="mailto:support@kycportal.com" class="text-blue-600 hover:text-blue-700 underline">
                support@kycportal.com
            </a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    // Create temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);

    // Show feedback (you can enhance this with a toast notification)
    alert('Reference number copied to clipboard!');
}

// Print styles
const style = document.createElement('style');
style.textContent = `
    @media print {
        header, footer, button, .no-print { display: none !important; }
        body { background: white; }
    }
`;
document.head.appendChild(style);
</script>
@endpush
