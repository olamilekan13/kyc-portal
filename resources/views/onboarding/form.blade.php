@extends('layouts.public')

@section('title', 'Final Onboarding - Partnership Selection')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 mb-6 border border-gray-100">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Complete Your Onboarding</h1>
                    <p class="text-gray-600 leading-relaxed">Your KYC has been submitted successfully. Please select a partnership model to continue.</p>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="mt-6 flex items-center justify-between">
                <div class="flex items-center flex-1">
                    <div class="flex items-center text-green-600 relative flex-1">
                        <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 bg-green-500 border-2 border-green-500 flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-green-600">KYC Submitted</div>
                    </div>
                    <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-blue-500"></div>
                </div>

                <div class="flex items-center flex-1">
                    <div class="flex items-center text-blue-600 relative flex-1">
                        <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 bg-blue-500 border-2 border-blue-500 flex items-center justify-center">
                            <span class="text-white font-bold">2</span>
                        </div>
                        <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-blue-600">Select Partnership</div>
                    </div>
                    <div class="flex-auto border-t-2 transition duration-500 ease-in-out border-gray-300"></div>
                </div>

                <div class="flex items-center">
                    <div class="flex items-center text-gray-500 relative">
                        <div class="rounded-full transition duration-500 ease-in-out h-10 w-10 bg-white border-2 border-gray-300 flex items-center justify-center">
                            <span class="text-gray-500 font-bold">3</span>
                        </div>
                        <div class="absolute top-0 -ml-10 text-center mt-12 w-32 text-xs font-medium text-gray-500">Payment</div>
                    </div>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <div class="flex items-start">
                    <svg class="h-5 w-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm text-red-800 font-medium">Please correct the following errors:</p>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('onboarding.submit', ['token' => $kycSubmission->onboarding_token]) }}" method="POST" enctype="multipart/form-data" x-data="{ selectedModel: {{ $finalOnboarding->partnership_model_id ?? 'null' }}, paymentMethod: '{{ $finalOnboarding->payment_method ?? 'bank_transfer' }}' }">
            @csrf

            <!-- Dynamic Form Fields Section -->
            @if($onboardingForm->fields->count() > 0)
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8 mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ $onboardingForm->name }}</h2>
                @if($onboardingForm->description)
                    <p class="text-gray-600 mb-6">{{ $onboardingForm->description }}</p>
                @endif

                <div class="space-y-6">
                    @foreach($onboardingForm->fields as $field)
                        <div class="form-field-wrapper">
                            <label for="{{ $field->field_name }}" class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ $field->field_label }}
                                @if($field->is_required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            @if($field->field_type === 'text' || $field->field_type === 'email' || $field->field_type === 'phone' || $field->field_type === 'number')
                                <input
                                    type="{{ $field->field_type === 'number' ? 'number' : ($field->field_type === 'email' ? 'email' : 'text') }}"
                                    id="{{ $field->field_name }}"
                                    name="{{ $field->field_name }}"
                                    value="{{ old($field->field_name, $finalOnboarding->form_data[$field->field_name] ?? '') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error($field->field_name) border-red-500 @enderror"
                                    {{ $field->is_required ? 'required' : '' }}
                                />

                            @elseif($field->field_type === 'date')
                                <input
                                    type="date"
                                    id="{{ $field->field_name }}"
                                    name="{{ $field->field_name }}"
                                    value="{{ old($field->field_name, $finalOnboarding->form_data[$field->field_name] ?? '') }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error($field->field_name) border-red-500 @enderror"
                                    {{ $field->is_required ? 'required' : '' }}
                                />

                            @elseif($field->field_type === 'textarea')
                                <textarea
                                    id="{{ $field->field_name }}"
                                    name="{{ $field->field_name }}"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error($field->field_name) border-red-500 @enderror"
                                    {{ $field->is_required ? 'required' : '' }}
                                >{{ old($field->field_name, $finalOnboarding->form_data[$field->field_name] ?? '') }}</textarea>

                            @elseif($field->field_type === 'select')
                                <select
                                    id="{{ $field->field_name }}"
                                    name="{{ $field->field_name }}"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error($field->field_name) border-red-500 @enderror"
                                    {{ $field->is_required ? 'required' : '' }}
                                >
                                    <option value="">Select an option</option>
                                    @if($field->options)
                                        @foreach($field->options as $value => $label)
                                            <option value="{{ $value }}" {{ old($field->field_name, $finalOnboarding->form_data[$field->field_name] ?? '') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>

                            @elseif($field->field_type === 'file')
                                <input
                                    type="file"
                                    id="{{ $field->field_name }}"
                                    name="{{ $field->field_name }}"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all @error($field->field_name) border-red-500 @enderror"
                                    {{ $field->is_required ? 'required' : '' }}
                                />
                                @if(isset($finalOnboarding->form_data[$field->field_name]['original_name']))
                                    <p class="mt-1 text-sm text-gray-600">
                                        Current file: {{ $finalOnboarding->form_data[$field->field_name]['original_name'] }}
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Max file size: 5MB. Allowed types: PDF, JPG, PNG</p>
                            @endif

                            @error($field->field_name)
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Partnership Models -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 sm:p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Select Your Partnership Model</h2>

                <!-- Fee Information -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-blue-800">
                                <span class="font-semibold">Signup Fee:</span> ₦{{ number_format($signupFee, 2) }} (Compulsory, Non-refundable)
                            </p>
                            <p class="text-xs text-blue-700 mt-1">This fee is required for all partnership models</p>
                        </div>
                    </div>
                </div>

                <!-- Partnership Models Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    @forelse($partnershipModels as $model)
                        <label class="relative cursor-pointer">
                            <input
                                type="radio"
                                name="partnership_model_id"
                                value="{{ $model->id }}"
                                x-model="selectedModel"
                                class="peer sr-only"
                                required
                            />
                            <div class="border-2 border-gray-200 rounded-xl p-5 transition-all hover:border-blue-400 peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:bg-blue-50">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-lg font-bold text-gray-900">{{ $model->name }}</h3>
                                    <div class="peer-checked:block hidden">
                                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                @if($model->description)
                                    <div class="text-sm text-gray-600 mb-4 prose prose-sm max-w-none prose-headings:text-gray-900 prose-a:text-blue-600 prose-strong:text-gray-900">{!! $model->description !!}</div>
                                @endif
                                <div class="pt-3 border-t border-gray-200">
                                    <p class="text-2xl font-bold text-blue-600">₦{{ number_format($model->price, 2) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $partnershipFeeLabel }}</p>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="col-span-full p-6 text-center bg-gray-50 rounded-lg">
                            <p class="text-gray-600">No partnership models available at the moment.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Payment Method Selection -->
                <div x-show="selectedModel" x-cloak class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Payment Method</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Bank Transfer -->
                        <label class="relative cursor-pointer">
                            <input
                                type="radio"
                                name="payment_method"
                                value="bank_transfer"
                                x-model="paymentMethod"
                                class="peer sr-only"
                                required
                            />
                            <div class="border-2 border-gray-200 rounded-xl p-5 transition-all hover:border-blue-400 peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:bg-blue-50">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-gray-600 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <span class="ml-3 font-semibold text-gray-900">Bank Transfer</span>
                                    </div>
                                    <div class="peer-checked:block hidden">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">Make payment via bank transfer</p>
                            </div>
                        </label>

                        <!-- Paystack -->
                        <label class="relative cursor-pointer">
                            <input
                                type="radio"
                                name="payment_method"
                                value="paystack"
                                x-model="paymentMethod"
                                class="peer sr-only"
                            />
                            <div class="border-2 border-gray-200 rounded-xl p-5 transition-all hover:border-blue-400 peer-checked:border-blue-600 peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:bg-blue-50">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 text-gray-600 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="ml-3 font-semibold text-gray-900">Pay Online</span>
                                    </div>
                                    <div class="peer-checked:block hidden">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">Pay instantly with Paystack</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Solar Power Option -->
                @if($solarPowerEnabled)
                <div x-show="selectedModel" x-cloak class="mb-6" x-data="{ solarPower: '{{ old('solar_power', $finalOnboarding->solar_power ?? 'no') }}', solarPowerAmount: {{ $solarPowerAmount ?? 0 }} }">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $solarPowerTitle }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Yes Option -->
                        <label class="relative cursor-pointer">
                            <input
                                type="radio"
                                name="solar_power"
                                value="yes"
                                x-model="solarPower"
                                class="peer sr-only"
                            />
                            <div class="border-2 border-gray-200 rounded-xl p-5 transition-all hover:border-green-400 peer-checked:border-green-600 peer-checked:ring-2 peer-checked:ring-green-200 peer-checked:bg-green-50">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-gray-900">Yes</span>
                                    <div class="peer-checked:block hidden">
                                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">Include Solar Power Generator in my package</p>
                            </div>
                        </label>

                        <!-- No Option -->
                        <label class="relative cursor-pointer">
                            <input
                                type="radio"
                                name="solar_power"
                                value="no"
                                x-model="solarPower"
                                class="peer sr-only"
                            />
                            <div class="border-2 border-gray-200 rounded-xl p-5 transition-all hover:border-gray-400 peer-checked:border-gray-600 peer-checked:ring-2 peer-checked:ring-gray-200 peer-checked:bg-gray-50">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-semibold text-gray-900">No</span>
                                    <div class="peer-checked:block hidden">
                                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">Continue without Solar Power Generator in my package</p>
                            </div>
                        </label>
                    </div>

                    <!-- Solar Power Details (shown when Yes is selected) -->
                    <div x-show="solarPower === 'yes'" x-cloak class="mt-4 p-3 sm:p-5 bg-gradient-to-r from-green-50 to-yellow-50 border border-green-200 rounded-xl overflow-hidden">
                        <div class="flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
                            @php
                                $solarImage = \App\Models\SystemSetting::get('solar_power_image', 'images/solar_power.jpg');
                                // If image is from Filament upload (stored in public disk), use storage path
                                $imagePath = str_starts_with($solarImage, 'system-settings/')
                                    ? asset('storage/' . $solarImage)
                                    : asset($solarImage);
                            @endphp
                            <img src="{{ $imagePath }}" alt="Solar Power" class="w-full sm:w-32 h-32 object-cover rounded-lg shadow-lg flex-shrink-0">
                            <div class="flex-1 min-w-0 w-full overflow-hidden">
                                <h4 class="text-sm sm:text-base md:text-lg font-bold text-gray-900 mb-2 break-words">{{ \App\Models\SystemSetting::get('solar_power_title', 'Solar Power Package') }}</h4>
                                <div class="text-xs sm:text-sm text-gray-700 mb-3 solar-description">
                                    {!! $solarPowerDescription ?? 'Get reliable, clean energy for your operations with our solar power solution. This package includes installation and maintenance.' !!}
                                </div>
                                <div class="flex items-baseline gap-1 sm:gap-2 flex-wrap">
                                    <span class="text-xs sm:text-sm text-gray-600 flex-shrink-0">Price:</span>
                                    <span class="text-lg sm:text-xl md:text-2xl font-bold text-green-600 break-all">₦<span x-text="solarPowerAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Submit Button -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        <svg class="inline w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Secure payment processing
                    </div>
                    <button
                        type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!selectedModel"
                    >
                        Continue to Payment
                        <svg class="inline-block ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function calculateTotal() {
        const models = @json($partnershipModels);
        const selectedModelId = parseInt(Alpine.raw(this.selectedModel));
        const signupFee = {{ $signupFee }};

        if (!selectedModelId) return '0.00';

        const model = models.find(m => m.id === selectedModelId);
        if (!model) return '0.00';

        const total = signupFee + parseFloat(model.price);
        return total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
</script>

<style>
    [x-cloak] { display: none !important; }

    /* Ensure text wraps properly on small devices */
    .overflow-wrap-anywhere {
        overflow-wrap: anywhere;
        word-wrap: anywhere;
        word-break: break-word;
        hyphens: auto;
    }

    /* Fix prose overflow on mobile */
    .prose {
        overflow-wrap: break-word;
        word-wrap: break-word;
    }

    .prose p {
        overflow-wrap: break-word;
        word-wrap: break-word;
    }

    /* Solar description specific fixes for very small screens */
    .solar-description {
        overflow-wrap: break-word;
        word-wrap: break-word;
        word-break: break-word;
        hyphens: auto;
        max-width: 100%;
    }

    .solar-description * {
        overflow-wrap: break-word !important;
        word-wrap: break-word !important;
        word-break: break-word !important;
        max-width: 100% !important;
    }

    .solar-description p {
        margin: 0 !important;
        padding: 0 !important;
    }

    .solar-description ul,
    .solar-description ol {
        padding-left: 1.25rem !important;
        margin: 0.5rem 0 !important;
    }

    .solar-description li {
        margin: 0.25rem 0 !important;
    }

    /* Ensure no horizontal overflow on very small screens */
    @media (max-width: 400px) {
        .solar-description {
            font-size: 0.75rem !important;
            line-height: 1.4 !important;
        }
    }
</style>
@endsection
