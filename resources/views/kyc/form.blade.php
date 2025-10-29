@extends('layouts.public')

@section('title', $form->name . ' - KYC Submission')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8" x-data="kycForm()">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 mb-6 border border-gray-100">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $form->name }}</h1>
                    @if($form->description)
                        <p class="text-gray-600 leading-relaxed">{{ $form->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Info Banner -->
            <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-blue-900">Important Information</h3>
                        <div class="mt-2 text-sm text-blue-800">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Fields marked with <span class="text-red-600 font-semibold">*</span> are required</li>
                                <li>Please ensure all information is accurate and up-to-date</li>
                                <li>Your data is encrypted and securely stored</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <form
            action="{{ route('kyc.submit', $form->id) }}"
            method="POST"
            enctype="multipart/form-data"
            @submit="onSubmit"
            class="bg-white rounded-2xl shadow-xl border border-gray-100"
        >
            @csrf

            <!-- General Error Alert -->
            @if($errors->has('error') || $errors->has('form'))
                <div class="p-6 border-b border-gray-100">
                    <div class="p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-900">Error</h3>
                                <p class="mt-1 text-sm text-red-800">{{ $errors->first('error') ?? $errors->first('form') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form Fields -->
            <div class="p-6 sm:p-8 space-y-6">
                @foreach($fields as $index => $field)
                    <div
                        class="form-field-wrapper"
                        x-data="fieldData{{ $index }}()"
                        x-init="init()"
                    >
                        <!-- Field Label -->
                        <label
                            for="{{ $field->field_name }}"
                            class="block text-sm font-semibold text-gray-700 mb-2"
                        >
                            {{ $field->field_label }}
                            @if($field->is_required)
                                <span class="text-red-600">*</span>
                            @endif
                        </label>

                        <!-- Field Input based on type -->
                        @switch($field->field_type)

                            {{-- Text Input --}}
                            @case('text')
                                <div class="relative">
                                    <input
                                        type="text"
                                        id="{{ $field->field_name }}"
                                        name="{{ $field->field_name }}"
                                        value="{{ old($field->field_name) }}"
                                        {{ $field->is_required ? 'required' : '' }}
                                        x-model="value"
                                        @blur="validate"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error($field->field_name) border-red-500 @enderror"
                                        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
                                        placeholder="Enter {{ strtolower($field->field_label) }}"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg x-show="!hasError && value" class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                @break

                            {{-- Email Input --}}
                            @case('email')
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        type="email"
                                        id="{{ $field->field_name }}"
                                        name="{{ $field->field_name }}"
                                        value="{{ old($field->field_name) }}"
                                        {{ $field->is_required ? 'required' : '' }}
                                        x-model="value"
                                        @blur="validate"
                                        class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error($field->field_name) border-red-500 @enderror"
                                        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
                                        placeholder="you@example.com"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg x-show="isValidEmail && !hasError" class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                @break

                            {{-- Phone Input with Mask --}}
                            @case('phone')
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <input
                                        type="tel"
                                        id="{{ $field->field_name }}"
                                        name="{{ $field->field_name }}"
                                        value="{{ old($field->field_name) }}"
                                        {{ $field->is_required ? 'required' : '' }}
                                        x-model="value"
                                        @input="formatPhone"
                                        @blur="validate"
                                        maxlength="20"
                                        class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error($field->field_name) border-red-500 @enderror"
                                        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
                                        placeholder="+1 (555) 123-4567"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg x-show="isValidPhone && !hasError" class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                @break

                            {{-- Date Input --}}
                            @case('date')
                                <div class="relative">
                                    <input
                                        type="date"
                                        id="{{ $field->field_name }}"
                                        name="{{ $field->field_name }}"
                                        value="{{ old($field->field_name) }}"
                                        {{ $field->is_required ? 'required' : '' }}
                                        x-model="value"
                                        @change="validate"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error($field->field_name) border-red-500 @enderror"
                                        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
                                    >
                                </div>
                                @break

                            {{-- Number Input --}}
                            @case('number')
                                <div class="relative">
                                    <input
                                        type="number"
                                        id="{{ $field->field_name }}"
                                        name="{{ $field->field_name }}"
                                        value="{{ old($field->field_name) }}"
                                        {{ $field->is_required ? 'required' : '' }}
                                        x-model="value"
                                        @blur="validate"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error($field->field_name) border-red-500 @enderror"
                                        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
                                        placeholder="Enter {{ strtolower($field->field_label) }}"
                                    >
                                </div>
                                @break

                            {{-- NIN Input with Auto-Verification --}}
                            @case('nin')
                                <div class="space-y-2">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                            </svg>
                                        </div>
                                        <input
                                            type="text"
                                            id="{{ $field->field_name }}"
                                            name="{{ $field->field_name }}"
                                            value="{{ old($field->field_name) }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                            x-model="value"
                                            @input="validateNINAndAutoVerify"
                                            maxlength="11"
                                            pattern="\d{11}"
                                            class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error($field->field_name) border-red-500 @enderror"
                                            :class="{
                                                'border-red-500 focus:ring-red-500': hasError,
                                                'border-green-500 focus:ring-green-500': ninVerified,
                                                'border-blue-300 focus:ring-blue-300': verifying
                                            }"
                                            placeholder="Enter 11-digit NIN"
                                            :readonly="ninVerified"
                                        >

                                        <!-- Status Icons -->
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                            <!-- Verifying Spinner -->
                                            <svg x-show="verifying" class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>

                                            <!-- Verified Checkmark -->
                                            <svg x-show="ninVerified" class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>

                                            <!-- Error Icon -->
                                            <svg x-show="verificationError && !verifying" class="h-6 w-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Verification Status Messages (Under Field) -->
                                    <div x-show="verifying" class="text-sm text-blue-600 flex items-center">
                                        <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Verifying NIN...</span>
                                    </div>

                                    <div x-show="ninVerified" class="text-sm text-green-600 flex items-center">
                                        <svg class="h-4 w-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span>NIN verified successfully. Information auto-filled.</span>
                                    </div>

                                    <div x-show="verificationError && !verifying" class="text-sm text-red-600 flex items-start">
                                        <svg class="h-4 w-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span x-text="verificationError"></span>
                                    </div>

                                    <!-- Hidden field to store verification status -->
                                    <input type="hidden" :name="'{{ $field->field_name }}_verified'" :value="ninVerified ? '1' : '0'">
                                    <input type="hidden" name="{{ $field->field_name }}_verification_data" x-model="ninVerificationData">
                                </div>
                                @break

                            {{-- Liveness Selfie with Webcam --}}
                            @case('liveness_selfie')
                                <div class="space-y-4">
                                    <!-- Camera Preview or Captured Image -->
                                    <div class="relative bg-gray-900 rounded-lg overflow-hidden" style="max-height: 400px; aspect-ratio: 16/9;">
                                        <!-- Webcam Video Stream -->
                                        <video
                                            x-show="!selfieCapture && cameraActive"
                                            x-ref="webcam"
                                            autoplay
                                            playsinline
                                            class="w-full h-full object-cover"
                                        ></video>

                                        <!-- Captured Selfie Preview -->
                                        <img
                                            x-show="selfieCapture"
                                            :src="selfieCapture"
                                            alt="Captured selfie"
                                            class="w-full h-full object-cover"
                                        >

                                        <!-- Placeholder when camera is off -->
                                        <div x-show="!cameraActive && !selfieCapture" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                                            <svg class="w-20 h-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <p class="text-sm">Click "Start Camera" to begin</p>
                                        </div>

                                        <!-- Camera guide overlay -->
                                        <div x-show="cameraActive && !selfieCapture" class="absolute inset-0 pointer-events-none">
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <div class="w-64 h-80 border-4 border-white/50 rounded-full"></div>
                                            </div>
                                            <div class="absolute bottom-4 left-0 right-0 text-center">
                                                <p class="text-white text-sm bg-black/50 inline-block px-4 py-2 rounded-full">
                                                    Position your face within the oval
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Verifying Overlay -->
                                        <div x-show="verifyingLiveness" class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center">
                                            <svg class="animate-spin h-12 w-12 text-white mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <p class="text-white text-sm">Verifying liveness...</p>
                                        </div>
                                    </div>

                                    <!-- Camera Controls -->
                                    <div class="flex flex-wrap gap-2 justify-center">
                                        <!-- Start Camera Button -->
                                        <button
                                            type="button"
                                            @click="startCamera"
                                            x-show="!cameraActive && !selfieCapture"
                                            class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors inline-flex items-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            Start Camera
                                        </button>

                                        <!-- Capture Button -->
                                        <button
                                            type="button"
                                            @click="captureSelfie"
                                            x-show="cameraActive && !selfieCapture"
                                            class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors inline-flex items-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            Capture Selfie
                                        </button>

                                        <!-- Retake Button -->
                                        <button
                                            type="button"
                                            @click="retakeSelfie"
                                            x-show="selfieCapture && !livenessVerified"
                                            class="px-4 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors inline-flex items-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Retake
                                        </button>

                                        <!-- Verify Button -->
                                        <button
                                            type="button"
                                            @click="verifyLiveness"
                                            x-show="selfieCapture && !livenessVerified && !verifyingLiveness"
                                            class="px-4 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-colors inline-flex items-center"
                                        >
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Verify Liveness
                                        </button>
                                    </div>

                                    <!-- Verification Status Messages -->
                                    <div x-show="livenessVerified" class="flex items-start p-3 bg-green-50 border border-green-200 rounded-lg">
                                        <svg class="h-5 w-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="text-sm text-green-800">
                                            <p class="font-medium">Liveness Verified Successfully</p>
                                            <p class="text-xs mt-1" x-show="faceMatchScore">Face match score: <span x-text="(faceMatchScore * 100).toFixed(0) + '%'"></span></p>
                                        </div>
                                    </div>

                                    <div x-show="livenessError" class="flex items-start p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <svg class="h-5 w-5 text-red-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="text-sm text-red-800">
                                            <p class="font-medium">Verification Failed</p>
                                            <p class="text-xs mt-1" x-text="livenessError"></p>
                                        </div>
                                    </div>

                                    <!-- Hidden fields -->
                                    <input type="hidden" name="{{ $field->field_name }}" x-model="selfieCapture">
                                    <input type="hidden" :name="'{{ $field->field_name }}_verified'" :value="livenessVerified ? '1' : '0'">
                                    <input type="hidden" name="{{ $field->field_name }}_verification_data" x-model="livenessVerificationData">
                                    <canvas x-ref="canvas" class="hidden"></canvas>
                                </div>
                                @break

                            {{-- Textarea --}}
                            @case('textarea')
                                <div class="relative">
                                    <textarea
                                        id="{{ $field->field_name }}"
                                        name="{{ $field->field_name }}"
                                        rows="4"
                                        {{ $field->is_required ? 'required' : '' }}
                                        x-model="value"
                                        @blur="validate"
                                        maxlength="5000"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none @error($field->field_name) border-red-500 @enderror"
                                        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
                                        placeholder="Enter {{ strtolower($field->field_label) }}"
                                    >{{ old($field->field_name) }}</textarea>
                                    <div class="absolute bottom-2 right-2 text-xs text-gray-400">
                                        <span x-text="value ? value.length : 0"></span>/5000
                                    </div>
                                </div>
                                @break

                            {{-- Select Dropdown --}}
                            @case('select')
                                <div class="relative">
                                    <select
                                        id="{{ $field->field_name }}"
                                        name="{{ $field->field_name }}"
                                        {{ $field->is_required ? 'required' : '' }}
                                        x-model="value"
                                        @change="validate"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none @error($field->field_name) border-red-500 @enderror"
                                        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
                                    >
                                        <option value="">Select {{ strtolower($field->field_label) }}</option>
                                        @if(!empty($field->options) && is_array($field->options))
                                            @foreach($field->options as $optValue => $optLabel)
                                                <option value="{{ $optValue }}" {{ old($field->field_name) == $optValue ? 'selected' : '' }}>
                                                    {{ $optLabel }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @break

                            {{-- File Upload with Preview --}}
                            @case('file')
                                <div class="space-y-3">
                                    <!-- File Input -->
                                    <div
                                        @dragover.prevent="dragging = true"
                                        @dragleave.prevent="dragging = false"
                                        @drop.prevent="handleFileDrop($event)"
                                        :class="{ 'border-blue-500 bg-blue-50': dragging }"
                                        class="relative border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-all duration-200"
                                    >
                                        <input
                                            type="file"
                                            id="{{ $field->field_name }}"
                                            name="{{ $field->field_name }}"
                                            {{ $field->is_required ? 'required' : '' }}
                                            accept=".pdf,.jpg,.jpeg,.png"
                                            @change="handleFileSelect($event)"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        >

                                        <div class="space-y-2">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                            <div>
                                                <span class="text-sm font-medium text-blue-600 hover:text-blue-700">Click to upload</span>
                                                <span class="text-sm text-gray-500"> or drag and drop</span>
                                            </div>
                                            <p class="text-xs text-gray-500">PDF, JPG, PNG up to 5MB</p>
                                        </div>
                                    </div>

                                    <!-- File Preview -->
                                    <div x-show="fileName" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="fileName"></p>
                                                    <p class="text-xs text-gray-500" x-text="fileSize"></p>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                @click="clearFile"
                                                class="flex-shrink-0 ml-4 text-red-600 hover:text-red-700"
                                            >
                                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- File size error -->
                                        <div x-show="fileSizeError" class="mt-2 text-xs text-red-600">
                                            File size exceeds 5MB limit
                                        </div>
                                    </div>
                                </div>
                                @break

                        @endswitch

                        <!-- Server-side Error Message -->
                        @error($field->field_name)
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror

                        <!-- Client-side Error Message -->
                        <p x-show="hasError && errorMessage" class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span x-text="errorMessage"></span>
                        </p>
                    </div>
                @endforeach
            </div>

            <!-- Form Footer -->
            <div class="px-6 sm:px-8 py-6 bg-gray-50 border-t border-gray-200 rounded-b-2xl">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="text-sm text-gray-600">
                        <span class="inline-flex items-center">
                            <svg class="h-4 w-4 mr-1 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Required fields
                        </span>
                    </div>

                    <button
                        type="submit"
                        :disabled="isSubmitting"
                        class="relative px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5 active:translate-y-0"
                    >
                        <span x-show="!isSubmitting" class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Submit Application
                        </span>
                        <span x-show="isSubmitting" class="flex items-center">
                            <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Submitting...
                        </span>
                    </button>
                </div>
            </div>
        </form>

        <!-- Security Badge -->
        <div class="mt-6 flex items-center justify-center space-x-2 text-sm text-gray-500">
            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>Secure & Encrypted Submission</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Main form state
function kycForm() {
    return {
        isSubmitting: false,

        onSubmit(event) {
            this.isSubmitting = true;
        }
    }
}

// Individual field data
@foreach($fields as $index => $field)
function fieldData{{ $index }}() {
    return {
        value: '{{ old($field->field_name) }}',
        hasError: false,
        errorMessage: '',
        isRequired: {{ $field->is_required ? 'true' : 'false' }},
        fieldType: '{{ $field->field_type }}',

        // File upload specific
        fileName: '',
        fileSize: '',
        fileSizeError: false,
        dragging: false,

        // NIN verification specific
        ninVerified: false,
        verifying: false,
        verificationError: '',
        ninVerificationData: '',

        // Liveness selfie specific
        cameraActive: false,
        selfieCapture: '',
        livenessVerified: false,
        verifyingLiveness: false,
        livenessError: '',
        livenessVerificationData: '',
        faceMatchScore: null,
        stream: null,

        init() {
            // Initialize any field-specific logic
        },

        // Start webcam
        async startCamera() {
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                });

                this.$refs.webcam.srcObject = this.stream;
                this.cameraActive = true;
                this.livenessError = '';
            } catch (error) {
                console.error('Camera access error:', error);
                this.livenessError = 'Unable to access camera. Please ensure you have granted camera permissions.';
            }
        },

        // Capture selfie from webcam
        captureSelfie() {
            const video = this.$refs.webcam;
            const canvas = this.$refs.canvas;
            const context = canvas.getContext('2d');

            // Set canvas dimensions to match video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw video frame to canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert to base64
            this.selfieCapture = canvas.toDataURL('image/jpeg', 0.8);

            // Stop camera
            this.stopCamera();
        },

        // Retake selfie
        retakeSelfie() {
            this.selfieCapture = '';
            this.livenessVerified = false;
            this.livenessError = '';
            this.faceMatchScore = null;
            this.startCamera();
        },

        // Stop camera
        stopCamera() {
            if (this.stream) {
                this.stream.getTracks().forEach(track => track.stop());
                this.stream = null;
            }
            this.cameraActive = false;
        },

        // Verify liveness with API
        async verifyLiveness() {
            if (!this.selfieCapture) {
                this.livenessError = 'Please capture a selfie first';
                return;
            }

            this.verifyingLiveness = true;
            this.livenessError = '';

            try {
                // Get NIN photo from NIN verification data if available
                let ninPhoto = null;
                const ninVerificationDataStr = document.querySelector('[name$="_verification_data"]')?.value;
                if (ninVerificationDataStr) {
                    try {
                        const ninData = JSON.parse(ninVerificationDataStr);
                        ninPhoto = ninData.photo || null;
                    } catch (e) {
                        console.log('No NIN data for face matching');
                    }
                }

                const response = await fetch('{{ route('api.liveness.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        selfie: this.selfieCapture,
                        nin_photo: ninPhoto
                    })
                });

                const result = await response.json();

                if (result.success && result.verified) {
                    // Liveness verified successfully
                    this.livenessVerified = true;
                    this.livenessError = '';
                    this.faceMatchScore = result.data?.face_match_score || null;
                    this.livenessVerificationData = JSON.stringify(result.data);

                    console.log('Liveness verified successfully:', result.data);
                } else {
                    // Verification failed
                    this.livenessVerified = false;
                    this.livenessError = result.message || 'Liveness verification failed. Please try again.';
                }
            } catch (error) {
                console.error('Liveness verification error:', error);
                this.livenessVerified = false;
                this.livenessError = 'Network error. Please check your connection and try again.';
            } finally {
                this.verifyingLiveness = false;
            }
        },

        // NIN validation with auto-verify
        validateNINAndAutoVerify() {
            // Only allow digits
            this.value = this.value.replace(/\D/g, '');

            // Reset verification if NIN changes
            if (this.value.length !== 11) {
                this.ninVerified = false;
                this.verificationError = '';
            }

            // Basic validation
            if (this.value.length > 0 && this.value.length !== 11) {
                this.hasError = true;
                this.errorMessage = 'NIN must be exactly 11 digits';
            } else {
                this.hasError = false;
                this.errorMessage = '';
            }

            // Auto-verify when 11 digits are entered
            if (this.value.length === 11 && !this.ninVerified && !this.verifying) {
                this.verifyNIN();
            }
        },

        // NIN validation (legacy - kept for compatibility)
        validateNIN() {
            this.validateNINAndAutoVerify();
        },

        // Verify NIN with API
        async verifyNIN() {
            if (this.value.length !== 11) {
                this.hasError = true;
                this.errorMessage = 'Please enter a valid 11-digit NIN';
                return;
            }

            this.verifying = true;
            this.verificationError = '';
            this.hasError = false;

            try {
                const response = await fetch('{{ route('api.nin.verify') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        nin: this.value
                    })
                });

                const result = await response.json();

                if (result.success && result.verified) {
                    // NIN verified successfully
                    this.ninVerified = true;
                    this.verificationError = '';
                    this.ninVerificationData = JSON.stringify(result.data);

                    // Auto-populate form fields
                    this.autoPopulateFields(result.data);

                    // Show success message
                    console.log('NIN verified successfully:', result.data);
                } else {
                    // Verification failed
                    this.ninVerified = false;
                    this.verificationError = result.message || 'NIN verification failed. Please try again.';
                    this.hasError = true;
                    this.errorMessage = result.message;

                    // Log debug information if available
                    if (result.debug) {
                        console.error('NIN Verification Debug Info:', result.debug);
                    }
                }
            } catch (error) {
                console.error('NIN verification error:', error);
                this.ninVerified = false;
                this.verificationError = 'Network error. Please check your connection and try again.';
                this.hasError = true;
                this.errorMessage = 'Failed to verify NIN. Please try again.';
            } finally {
                this.verifying = false;
            }
        },

        // Auto-populate form fields with NIN data
        autoPopulateFields(data) {
            // Mapping of API response fields to form field names
            const fieldMap = {
                'first_name': data.first_name,
                'last_name': data.last_name,
                'middle_name': data.middle_name,
                'date_of_birth': data.date_of_birth,
                'dob': data.date_of_birth,
                'phone_number': data.phone_number,
                'phone': data.phone_number,
                'email': data.email,
                'gender': data.gender,
                'address': data.address,
                'state': data.state,
                'lga': data.lga,
            };

            // Iterate through all form fields and populate if data exists
            Object.keys(fieldMap).forEach(fieldName => {
                const fieldValue = fieldMap[fieldName];
                if (fieldValue) {
                    const inputElement = document.querySelector(`[name="${fieldName}"]`);
                    if (inputElement) {
                        // Trigger Alpine.js update
                        inputElement.value = fieldValue;
                        inputElement.dispatchEvent(new Event('input'));

                        // Add visual indication that field was auto-filled
                        inputElement.classList.add('bg-blue-50', 'border-blue-300');

                        // Make auto-filled fields readonly (optional)
                        // inputElement.setAttribute('readonly', 'readonly');
                    }
                }
            });
        },

        // Email validation
        get isValidEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(this.value);
        },

        // Phone validation
        get isValidPhone() {
            const phoneRegex = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/;
            return phoneRegex.test(this.value);
        },

        // Phone formatting
        formatPhone() {
            // Basic phone formatting (can be enhanced)
            this.value = this.value.replace(/[^\d+\s()-]/g, '');
        },

        // File handling
        handleFileSelect(event) {
            const file = event.target.files[0];
            this.processFile(file);
        },

        handleFileDrop(event) {
            this.dragging = false;
            const file = event.dataTransfer.files[0];
            this.processFile(file);
        },

        processFile(file) {
            if (!file) return;

            this.fileName = file.name;
            this.fileSize = this.formatFileSize(file.size);

            // Check file size (5MB = 5242880 bytes)
            if (file.size > 5242880) {
                this.fileSizeError = true;
                this.hasError = true;
                this.errorMessage = 'File size must not exceed 5MB';
            } else {
                this.fileSizeError = false;
                this.hasError = false;
                this.errorMessage = '';
            }
        },

        clearFile() {
            this.fileName = '';
            this.fileSize = '';
            this.fileSizeError = false;
            this.hasError = false;
            this.errorMessage = '';
            document.getElementById('{{ $field->field_name }}').value = '';
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        },

        // General validation
        validate() {
            this.hasError = false;
            this.errorMessage = '';

            // Required field check
            if (this.isRequired && !this.value) {
                this.hasError = true;
                this.errorMessage = 'This field is required';
                return;
            }

            // Type-specific validation
            if (this.fieldType === 'email' && this.value && !this.isValidEmail) {
                this.hasError = true;
                this.errorMessage = 'Please enter a valid email address';
            }

            if (this.fieldType === 'phone' && this.value && !this.isValidPhone) {
                this.hasError = true;
                this.errorMessage = 'Please enter a valid phone number';
            }
        }
    }
}
@endforeach
</script>
@endpush
@endsection
