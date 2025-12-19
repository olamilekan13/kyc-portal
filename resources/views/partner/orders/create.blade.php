@extends('layouts.public')

@section('title', 'Create New Order')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('partner.dashboard') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Dashboard
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Create New Order</h1>
            <p class="mt-1 text-sm text-gray-600">Select a partnership model for your additional order</p>
        </div>

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

        <form action="{{ route('partner.orders.store') }}" method="POST" x-data="orderForm()" class="space-y-6">
            @csrf

            <!-- Notice: No Signup Fee -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Good news!</strong> No signup fee required for additional orders. You only pay for the partnership model and optional add-ons.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Partnership Model Selection -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Select Partnership Model</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($partnershipModels as $model)
                        <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none"
                               :class="selectedModel === {{ $model->id }} ? 'border-blue-500 ring-2 ring-blue-500' : 'border-gray-300'"
                               @click="selectModel({{ $model->id }}, {{ $model->price }}, '{{ $model->name }}', {{ $model->duration_months }})">
                            <input type="radio"
                                   name="partnership_model_id"
                                   value="{{ $model->id }}"
                                   class="sr-only"
                                   required
                                   x-model="selectedModel">
                            <div class="flex flex-1">
                                <div class="flex flex-col flex-1">
                                    <span class="block text-sm font-medium text-gray-900">{{ $model->name }}</span>
                                    @if($model->description)
                                        <div class="mt-1 text-sm text-gray-500 prose prose-sm max-w-none">{!! strip_tags($model->description, '<br><p><strong><em><ul><ol><li>') !!}</div>
                                    @endif
                                    <span class="mt-2 text-2xl font-bold text-blue-600">₦{{ number_format($model->price, 2) }}</span>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-blue-600"
                                 :class="selectedModel === {{ $model->id }} ? 'block' : 'hidden'"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </label>
                    @endforeach
                </div>

                @error('partnership_model_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Solar Power Option -->
            @if($solarPowerEnabled)
                <div x-show="selectedModel" x-cloak class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $solarPowerTitle }}</h2>
                    <div class="text-sm text-gray-600 mb-4 prose prose-sm max-w-none">
                        {!! $solarPowerDescription !!}
                    </div>

                    @if($solarPowerImage)
                        <div class="mb-4">
                            @php
                                // If image is from Filament upload (stored in public disk), use storage path
                                $imagePath = str_starts_with($solarPowerImage, 'system-settings/')
                                    ? asset('storage/' . $solarPowerImage)
                                    : asset($solarPowerImage);
                            @endphp
                            <img src="{{ $imagePath }}"
                                 alt="Solar Power Package"
                                 class="w-full h-48 object-cover rounded-lg shadow-sm">
                        </div>
                    @endif

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Solar Power Package</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">₦{{ number_format($solarPowerAmount, 2) }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="solar_power"
                                   value="1"
                                   class="sr-only peer"
                                   x-model="solarPower"
                                   @change="updateTotal">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
            @endif

            <!-- Duration -->
            <div x-show="selectedModel" x-cloak class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Partnership Duration</h2>
                <div class="flex items-center space-x-4">
                    <label class="flex-1">
                        <span class="block text-sm font-medium text-gray-700 mb-2">Duration (Months)</span>
                        <input type="number"
                               name="duration_months"
                               min="1"
                               x-model="durationMonths"
                               readonly
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed text-gray-700">
                    </label>
                </div>
            </div>

            <!-- Order Summary -->
            <div x-show="selectedModel" x-cloak class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Partnership Model:</span>
                        <span class="font-medium text-gray-900" x-text="selectedModelName"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Partnership Fee:</span>
                        <span class="font-medium text-gray-900" x-text="'₦' + modelPrice.toLocaleString('en-NG', {minimumFractionDigits: 2})"></span>
                    </div>
                    <div x-show="solarPower" class="flex justify-between text-sm">
                        <span class="text-gray-600">Solar Power Package:</span>
                        <span class="font-medium text-gray-900">₦{{ number_format($solarPowerAmount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-green-600">
                        <span class="font-medium">Signup Fee:</span>
                        <span class="font-medium">₦0.00 (Waived for additional orders)</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <div class="flex justify-between">
                            <span class="text-base font-semibold text-gray-900">Total Amount:</span>
                            <span class="text-2xl font-bold text-blue-600" x-text="'₦' + totalAmount.toLocaleString('en-NG', {minimumFractionDigits: 2})"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-4">
                <a href="{{ route('partner.dashboard') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </a>

                <button type="submit"
                        :disabled="!selectedModel"
                        :class="selectedModel ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Proceed to Payment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function orderForm() {
    return {
        selectedModel: null,
        selectedModelName: '',
        modelPrice: 0,
        durationMonths: 12,
        solarPower: false,
        solarPowerAmount: {{ $solarPowerAmount }},
        totalAmount: 0,

        selectModel(id, price, name, duration) {
            this.selectedModel = id;
            this.modelPrice = price;
            this.selectedModelName = name;
            this.durationMonths = duration || 12;
            this.updateTotal();
        },

        updateTotal() {
            this.totalAmount = this.modelPrice + (this.solarPower ? this.solarPowerAmount : 0);
        }
    }
}
</script>
@endsection
