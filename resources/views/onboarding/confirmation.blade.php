@extends('layouts.public')

@section('title', 'Onboarding Complete')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Success Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-blue-600 px-8 py-10 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    @if($finalOnboarding->isFullyPaid())
                        Payment Successful!
                    @else
                        Payment Information Received
                    @endif
                </h1>
                <p class="text-blue-100">
                    @if($finalOnboarding->isFullyPaid())
                        Your onboarding is now complete
                    @else
                        We've received your payment information
                    @endif
                </p>
            </div>

            <!-- Content -->
            <div class="px-8 py-8">
                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                        <p class="text-sm text-green-800">{{ session('success') }}</p>
                    </div>
                @endif

                <!-- Payment Status -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Payment Status</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 {{ $finalOnboarding->signup_fee_paid ? 'bg-green-100' : 'bg-yellow-100' }} rounded-full flex items-center justify-center mr-3">
                                    @if($finalOnboarding->signup_fee_paid)
                                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Signup Fee</p>
                                    <p class="text-sm text-gray-600">₦{{ number_format($finalOnboarding->signup_fee_amount, 2) }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $finalOnboarding->signup_fee_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $finalOnboarding->signup_fee_paid ? 'PAID' : 'PENDING' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <div class="w-10 h-10 {{ $finalOnboarding->model_fee_paid ? 'bg-green-100' : 'bg-yellow-100' }} rounded-full flex items-center justify-center mr-3">
                                    @if($finalOnboarding->model_fee_paid)
                                        <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Partnership Fee</p>
                                    <p class="text-sm text-gray-600">{{ $finalOnboarding->partnership_model_name }} - ₦{{ number_format($finalOnboarding->partnership_model_price, 2) }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $finalOnboarding->model_fee_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $finalOnboarding->model_fee_paid ? 'PAID' : 'PENDING' }}
                            </span>
                        </div>

                        @if($finalOnboarding->solar_power)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-yellow-50 rounded-lg border border-green-200">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Solar Power Package</p>
                                    <p class="text-sm text-gray-600">Clean energy solution - ₦{{ number_format($finalOnboarding->solar_power_amount, 2) }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                INCLUDED
                            </span>
                        </div>
                        @endif

                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border-2 border-blue-300">
                            <div>
                                <p class="font-semibold text-gray-900">Total Amount</p>
                                <p class="text-sm text-gray-600">All fees combined</p>
                            </div>
                            <p class="text-2xl font-bold text-blue-600">₦{{ number_format($finalOnboarding->total_amount, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Reference Information -->
                <div class="mb-8 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Reference Information</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <p class="text-sm text-gray-600">Onboarding Token:</p>
                            <p class="text-sm font-mono font-semibold text-gray-900">{{ $kycSubmission->onboarding_token }}</p>
                        </div>
                        @if($finalOnboarding->signup_fee_reference)
                            <div class="flex justify-between">
                                <p class="text-sm text-gray-600">Signup Fee Reference:</p>
                                <p class="text-sm font-mono font-semibold text-gray-900">{{ $finalOnboarding->signup_fee_reference }}</p>
                            </div>
                        @endif
                        @if($finalOnboarding->model_fee_reference)
                            <div class="flex justify-between">
                                <p class="text-sm text-gray-600">Partnership Fee Reference:</p>
                                <p class="text-sm font-mono font-semibold text-gray-900">{{ $finalOnboarding->model_fee_reference }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">What Happens Next?</h3>
                    <div class="space-y-3">
                        @if($finalOnboarding->isFullyPaid())
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center mt-0.5">
                                    <span class="text-green-600 text-xs font-bold">✓</span>
                                </div>
                                <p class="ml-3 text-sm text-gray-700">Your payment has been confirmed and your onboarding is complete.</p>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                                    <span class="text-blue-600 text-xs font-bold">2</span>
                                </div>
                                <p class="ml-3 text-sm text-gray-700">Our team will review your KYC submission and contact you shortly.</p>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center mt-0.5">
                                    <span class="text-purple-600 text-xs font-bold">3</span>
                                </div>
                                <p class="ml-3 text-sm text-gray-700">You will receive an email confirmation with further instructions.</p>
                            </div>
                        @else
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center mt-0.5">
                                    <span class="text-yellow-600 text-xs font-bold">1</span>
                                </div>
                                <p class="ml-3 text-sm text-gray-700">We've received your payment information and are awaiting verification.</p>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center mt-0.5">
                                    <span class="text-yellow-600 text-xs font-bold">2</span>
                                </div>
                                <p class="ml-3 text-sm text-gray-700">Our admin team will verify your payment within 24 hours.</p>
                            </div>
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center mt-0.5">
                                    <span class="text-yellow-600 text-xs font-bold">3</span>
                                </div>
                                <p class="ml-3 text-sm text-gray-700">You will receive a confirmation email once your payment is verified.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="border-t border-gray-200 pt-6 space-y-3">
                    @if($finalOnboarding->payment_method === 'bank_transfer' || $finalOnboarding->isFullyPaid())
                        <a href="{{ route('onboarding.receipt', ['token' => $kycSubmission->onboarding_token]) }}" target="_blank" class="w-full inline-block text-center px-6 py-3 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Receipt
                        </a>
                    @endif

                    <a href="{{ url('/') }}" class="w-full inline-block text-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Go to Homepage
                    </a>

                    <p class="text-center text-sm text-gray-600 pt-3">
                        Keep your reference number for future correspondence: <br>
                        <span class="font-mono font-semibold text-gray-900">{{ $kycSubmission->onboarding_token }}</span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
