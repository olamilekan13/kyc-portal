@extends('layouts.public')

@section('title', 'Renew Partnership')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 mb-6 border border-gray-100">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Renew Your Partnership</h1>
                    <p class="text-gray-600 leading-relaxed">Select a partnership model to continue enjoying our services.</p>
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

        <!-- Current Partnership Info -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Current Partnership Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Partner Name</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $finalOnboarding->partner_name }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Current Plan</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $finalOnboarding->partnership_model_name }}</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Status</p>
                    @if($finalOnboarding->isExpired())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            Expired
                        </span>
                    @elseif($finalOnboarding->isExpiringSoon())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Expiring Soon ({{ $finalOnboarding->days_until_expiry }} days left)
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    @endif
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">Expiry Date</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $finalOnboarding->formatted_end_date ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Renewal Form -->
        <form action="{{ route('renewal.submit', ['token' => $finalOnboarding->renewal_token]) }}" method="POST">
            @csrf

            <!-- Partnership Models Selection -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Select Partnership Model</h2>

                @error('partnership_model_id')
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                        {{ $message }}
                    </div>
                @enderror

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($partnershipModels as $model)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="partnership_model_id" value="{{ $model->id }}"
                                class="peer sr-only" {{ $model->id == $finalOnboarding->partnership_model_id ? 'checked' : '' }}>
                            <div class="p-4 border-2 border-gray-200 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="font-bold text-gray-900">{{ $model->name }}</h3>
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded-full peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white hidden peer-checked:block" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-2xl font-bold text-blue-600 mb-1">{{ $model->formatted_price }}</p>
                                <p class="text-sm text-gray-600 mb-2">{{ $model->formatted_duration }}</p>
                                @if($model->description)
                                    <div class="text-xs text-gray-500 line-clamp-2">{!! strip_tags($model->description) !!}</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Payment Method Selection -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Payment Method</h2>

                @error('payment_method')
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                        {{ $message }}
                    </div>
                @enderror

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="bank_transfer" class="peer sr-only" checked>
                        <div class="p-4 border-2 border-gray-200 rounded-xl transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300">
                            <div class="flex items-center space-x-3">
                                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">Bank Transfer</p>
                                    <p class="text-sm text-gray-600">Pay via direct bank transfer</p>
                                </div>
                            </div>
                        </div>
                    </label>

                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="paystack" class="peer sr-only">
                        <div class="p-4 border-2 border-gray-200 rounded-xl transition-all peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300">
                            <div class="flex items-center space-x-3">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="font-semibold text-gray-900">Pay Online (Paystack)</p>
                                    <p class="text-sm text-gray-600">Card, Bank, or Mobile Money</p>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200">
                    Continue to Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
