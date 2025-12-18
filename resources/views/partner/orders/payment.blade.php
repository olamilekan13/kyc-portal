@extends('layouts.public')

@section('title', 'Order Payment - ' . $order->order_number)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('partner.orders.show', $order) }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Order Details
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Complete Payment</h1>
            <p class="mt-1 text-sm text-gray-600">Order Number: <span class="font-medium">{{ $order->order_number }}</span></p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Order Summary -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Partnership Model:</span>
                    <span class="font-medium text-gray-900">{{ $order->partnership_model_name }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Partnership Fee:</span>
                    <span class="font-medium text-gray-900">₦{{ number_format($order->partnership_model_price, 2) }}</span>
                </div>
                @if($order->solar_power)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Solar Power Package:</span>
                        <span class="font-medium text-gray-900">₦{{ number_format($order->solar_power_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm text-green-600">
                    <span class="font-medium">Signup Fee:</span>
                    <span class="font-medium">₦0.00 (Waived)</span>
                </div>
                <div class="border-t border-gray-200 pt-3 mt-3">
                    <div class="flex justify-between">
                        <span class="text-base font-semibold text-gray-900">Total Amount:</span>
                        <span class="text-2xl font-bold text-blue-600">₦{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Selection -->
        <div class="bg-white shadow-md rounded-lg p-6" x-data="{ paymentMethod: 'paystack' }">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Select Payment Method</h2>

            <div class="space-y-4">
                <!-- Paystack Online Payment Option -->
                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none"
                       :class="paymentMethod === 'paystack' ? 'border-blue-500 ring-2 ring-blue-500' : 'border-gray-300'">
                    <input type="radio"
                           value="paystack"
                           class="sr-only"
                           x-model="paymentMethod">
                    <div class="flex flex-1">
                        <div class="flex flex-col flex-1">
                            <span class="block text-sm font-medium text-gray-900">Pay Online (Paystack)</span>
                            <span class="mt-1 flex items-center text-sm text-gray-500">Pay securely with card or bank transfer</span>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-blue-600"
                         :class="paymentMethod === 'paystack' ? 'block' : 'hidden'"
                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </label>

                <!-- Bank Transfer Option -->
                <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none"
                       :class="paymentMethod === 'bank_transfer' ? 'border-blue-500 ring-2 ring-blue-500' : 'border-gray-300'">
                    <input type="radio"
                           value="bank_transfer"
                           class="sr-only"
                           x-model="paymentMethod">
                    <div class="flex flex-1">
                        <div class="flex flex-col flex-1">
                            <span class="block text-sm font-medium text-gray-900">Bank Transfer</span>
                            <span class="mt-1 flex items-center text-sm text-gray-500">Transfer to our bank account and upload proof</span>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-blue-600"
                         :class="paymentMethod === 'bank_transfer' ? 'block' : 'hidden'"
                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </label>
            </div>

            <!-- Bank Transfer Form -->
            <div x-show="paymentMethod === 'bank_transfer'" x-cloak class="mt-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="text-sm font-medium text-blue-900 mb-3">Bank Account Details</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-700">Bank Name:</span>
                            <span class="font-medium text-blue-900">{{ $bankName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Account Number:</span>
                            <span class="font-medium text-blue-900">{{ $bankAccountNumber }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Account Name:</span>
                            <span class="font-medium text-blue-900">{{ $bankAccountName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Amount:</span>
                            <span class="font-medium text-blue-900">₦{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <form action="{{ route('partner.orders.payment.bank-transfer', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Upload Payment Proof (Optional)
                        </label>
                        <input type="file"
                               name="payment_proof"
                               accept="image/*,.pdf"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-500">Accepted formats: JPG, PNG, PDF (Max 2MB)</p>
                        @error('payment_proof')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Notes (Optional)
                        </label>
                        <textarea name="payment_notes"
                                  rows="3"
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Add any additional information about your payment..."></textarea>
                        @error('payment_notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Submit Payment Proof
                    </button>
                </form>
            </div>

            <!-- Paystack Payment Section -->
            <div x-show="paymentMethod === 'paystack'" x-cloak class="mt-6">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-900">Secure Payment</h3>
                            <p class="mt-1 text-sm text-green-700">Your payment is processed securely through Paystack. You can pay with your debit card, credit card, or bank transfer.</p>
                        </div>
                    </div>
                </div>

                <form id="paystack-form">
                    <button type="button"
                            onclick="payWithPaystack()"
                            class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Pay ₦{{ number_format($order->total_amount, 2) }} Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
function payWithPaystack() {
    const handler = PaystackPop.setup({
        key: '{{ $paystackPublicKey }}',
        email: '{{ $partner->email }}',
        amount: {{ $order->total_amount * 100 }}, // Amount in kobo
        currency: 'NGN',
        ref: '{{ $order->order_token }}',
        metadata: {
            custom_fields: [
                {
                    display_name: 'Order Number',
                    variable_name: 'order_number',
                    value: '{{ $order->order_number }}'
                },
                {
                    display_name: 'Partner Name',
                    variable_name: 'partner_name',
                    value: '{{ $partner->full_name }}'
                }
            ]
        },
        callback: function(response) {
            // Verify payment
            window.location.href = '{{ route("partner.orders.payment.verify", $order) }}?reference=' + response.reference;
        },
        onClose: function() {
            alert('Payment window closed. You can try again when ready.');
        }
    });
    handler.openIframe();
}
</script>
@endsection
