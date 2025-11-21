@extends('layouts.public')

@section('title', 'Renewal Confirmation')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
            @if($finalOnboarding->renewal_status === 'renewed')
                <!-- Success State -->
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">Partnership Renewed Successfully!</h1>
                <p class="text-gray-600 mb-8">Your partnership has been renewed. Thank you for your continued trust in us!</p>

                <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Renewal Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Partnership Model:</span>
                            <span class="font-semibold text-gray-900">{{ $finalOnboarding->partnership_model_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">New Start Date:</span>
                            <span class="font-semibold text-gray-900">{{ $finalOnboarding->partnership_start_date?->format('F j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">New Expiry Date:</span>
                            <span class="font-semibold text-gray-900">{{ $finalOnboarding->formatted_end_date }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Duration:</span>
                            <span class="font-semibold text-gray-900">
                                @if($finalOnboarding->duration_months == 12)
                                    1 Year
                                @elseif($finalOnboarding->duration_months == 1)
                                    1 Month
                                @else
                                    {{ $finalOnboarding->duration_months }} Months
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-8">
                    <p class="text-green-800 text-sm">
                        <strong>Note:</strong> You will receive renewal reminders before your partnership expires.
                        Keep your renewal link safe for future renewals.
                    </p>
                </div>

            @elseif($finalOnboarding->renewal_status === 'pending_renewal')
                <!-- Pending Verification State -->
                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Submitted</h1>
                <p class="text-gray-600 mb-8">Your renewal payment has been submitted and is awaiting verification.</p>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8 text-left">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">What's Next?</h2>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Our team will verify your payment within 24-48 hours</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                            </svg>
                            <span>You'll receive an email once your renewal is confirmed</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Your partnership will be extended after verification</span>
                        </li>
                    </ul>
                </div>

            @else
                <!-- Default State -->
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">Renewal Status</h1>
                <p class="text-gray-600 mb-8">
                    Current Status: <span class="font-semibold capitalize">{{ str_replace('_', ' ', $finalOnboarding->renewal_status) }}</span>
                </p>

                <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Partnership Details</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Partnership Model:</span>
                            <span class="font-semibold text-gray-900">{{ $finalOnboarding->partnership_model_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Expiry Date:</span>
                            <span class="font-semibold text-gray-900">{{ $finalOnboarding->formatted_end_date ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Return Home
            </a>
        </div>
    </div>
</div>
@endsection
