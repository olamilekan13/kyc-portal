@extends('layouts.public')

@section('title', 'Payment - Final Onboarding')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 mb-6 border border-gray-100">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Complete Payment</h1>
                    <p class="text-gray-600 leading-relaxed">Make your payment to complete the onboarding process.</p>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="mt-6 flex items-center justify-between">
                <div class="flex items-center flex-1">
                    <div class="flex items-center text-green-600 relative flex-1">
                        <div class="rounded-full h-10 w-10 bg-green-500 border-2 border-green-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-green-600">KYC Submitted</div>
                    </div>
                    <div class="flex-auto border-t-2 border-green-500"></div>
                </div>

                <div class="flex items-center flex-1">
                    <div class="flex items-center text-green-600 relative flex-1">
                        <div class="rounded-full h-10 w-10 bg-green-500 border-2 border-green-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-green-600">Partnership Selected</div>
                    </div>
                    <div class="flex-auto border-t-2 border-blue-500"></div>
                </div>

                <div class="flex items-center">
                    <div class="flex items-center text-blue-600 relative">
                        <div class="rounded-full h-10 w-10 bg-blue-500 border-2 border-blue-500 flex items-center justify-center">
                            <span class="text-white font-bold">3</span>
                        </div>
                        <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-blue-600">Payment</div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="ml-3 text-sm text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="ml-3 text-sm text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Payment Summary -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Payment Summary</h2>

            <div class="space-y-4 mb-6">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <div>
                        <p class="font-semibold text-gray-900">Partnership Model</p>
                        <p class="text-sm text-gray-600">{{ $finalOnboarding->partnership_model_name }}</p>
                    </div>
                    <p class="text-lg font-bold text-gray-900">₦{{ number_format($finalOnboarding->partnership_model_price, 2) }}</p>
                </div>

                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <div>
                        <p class="font-semibold text-gray-900">Signup Fee</p>
                        <p class="text-sm text-gray-600">Compulsory, Non-refundable</p>
                    </div>
                    <p class="text-lg font-bold text-gray-900">₦{{ number_format($finalOnboarding->signup_fee_amount, 2) }}</p>
                </div>

                @if($finalOnboarding->solar_power)
                <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                    <div>
                        <p class="font-semibold text-gray-900">Solar Power</p>
                        <div class="flex items-center mt-2 space-x-3">
                            <img src="{{ asset(\App\Models\SystemSetting::get('solar_power_image', 'images/solar_power.jpg')) }}" alt="Solar Power" class="w-16 h-16 object-cover rounded-lg shadow-sm">
                            <p class="text-sm text-gray-600">Clean energy solution</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold text-gray-900">₦{{ number_format($finalOnboarding->solar_power_amount, 2) }}</p>
                </div>
                @endif

                <div class="flex justify-between items-center pt-3">
                    <p class="text-xl font-bold text-gray-900">Total Amount</p>
                    <p class="text-3xl font-bold text-blue-600">₦{{ number_format($finalOnboarding->total_amount, 2) }}</p>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg mb-6">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-900">Payment Status: <span class="uppercase">{{ $finalOnboarding->payment_status }}</span></p>
                        <div class="mt-2 text-xs text-yellow-800 space-y-1">
                            <p>Signup Fee: {{ $finalOnboarding->signup_fee_paid ? 'Paid ✓' : 'Pending' }}</p>
                            <p>Partnership Fee: {{ $finalOnboarding->model_fee_paid ? 'Paid ✓' : 'Pending' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('onboarding.show', ['token' => $kycSubmission->onboarding_token]) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Partnership Selection
            </a>
        </div>

        <!-- Payment Methods -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8" x-data="{ activeTab: '{{ $finalOnboarding->payment_method === 'paystack' ? 'online' : 'bank' }}' }">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Select Payment Method</h2>

            <!-- Tabs -->
            <div class="flex border-b border-gray-200 mb-6">
                <button
                    @click="activeTab = 'bank'"
                    :class="activeTab === 'bank' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    Bank Transfer
                </button>
                <button
                    @click="activeTab = 'online'"
                    :class="activeTab === 'online' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="py-4 px-6 border-b-2 font-medium text-sm transition-colors"
                >
                    <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Pay Online
                </button>
            </div>

            <!-- Bank Transfer Panel -->
            <div x-show="activeTab === 'bank'" x-cloak>
                <div class="mb-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Bank Account Details</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Bank Name</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $bankName }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Account Number</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $bankAccountNumber }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Account Name</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $bankAccountName }}</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('onboarding.bank-transfer', ['token' => $kycSubmission->onboarding_token]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="payment_type" value="both">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Reference/Transaction ID</label>
                            <input type="text" name="payment_reference" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter your transaction reference" value="{{ old('payment_reference') }}">
                            <p class="mt-1 text-xs text-gray-500">Enter the reference from your bank transaction (optional)</p>
                            @error('payment_reference')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Proof of Payment</label>
                            <input type="file" name="payment_proof" accept="image/*,.pdf" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-xs text-gray-500">Upload a screenshot or receipt of your payment (JPG, PNG, or PDF - Max 5MB)</p>
                            @error('payment_proof')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Notes</label>
                            <textarea name="payment_notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Any additional information...">{{ old('payment_notes') }}</textarea>
                        </div>

                        <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                            Submit Payment Information
                        </button>
                    </div>
                </form>
            </div>

            <!-- Online Payment Panel -->
            <div x-show="activeTab === 'online'" x-cloak>
                <div class="mb-6 p-5 bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-xl">
                    <div class="flex items-start">
                        <svg class="h-6 w-6 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-900">Secure Online Payment</h3>
                            <p class="mt-1 text-sm text-green-800">Pay instantly with your debit card, bank account, or mobile money via Paystack</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <button
                        onclick="payWithPaystack('both', {{ $finalOnboarding->total_amount }})"
                        class="w-full px-6 py-4 bg-gradient-to-r from-green-600 to-blue-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 text-left"
                    >
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-lg font-bold">Pay Total Amount</p>
                                <p class="text-sm opacity-90">Signup Fee + Partnership Fee</p>
                            </div>
                            <p class="text-2xl font-bold">₦{{ number_format($finalOnboarding->total_amount, 2) }}</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    function payWithPaystack(paymentType, amount) {
        const handler = PaystackPop.setup({
            key: '{{ $paystackPublicKey }}',
            email: '{{ $kycSubmission->submission_data['email'] ?? 'customer@example.com' }}',
            amount: amount * 100, // Amount in kobo
            currency: 'NGN',
            ref: 'ONB-' + Math.floor((Math.random() * 1000000000) + 1),
            metadata: {
                payment_type: paymentType,
                kyc_submission_id: {{ $kycSubmission->id }},
                final_onboarding_id: {{ $finalOnboarding->id }}
            },
            callback: function(response) {
                window.location = '{{ route('onboarding.paystack-callback', ['token' => $kycSubmission->onboarding_token]) }}?reference=' + response.reference + '&payment_type=' + paymentType;
            },
            onClose: function() {
                alert('Payment cancelled');
            }
        });
        handler.openIframe();
    }
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
