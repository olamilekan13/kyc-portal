@extends('layouts.public')

@section('title', $form->name . ' - KYC Submission')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Form Header -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $form->name }}</h2>
        @if($form->description)
            <p class="text-gray-600">{{ $form->description }}</p>
        @endif
        <div class="mt-4 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Please fill out all required fields marked with <span class="text-red-500">*</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('kyc.submit', $form->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
        @csrf

        <!-- Display General Errors -->
        @if($errors->has('error') || $errors->has('form'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            {{ $errors->first('error') ?? $errors->first('form') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Dynamic Form Fields -->
        @foreach($fields as $field)
            <div class="mb-6">
                <label for="{{ $field->field_name }}" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ $field->field_label }}
                    @if($field->is_required)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @switch($field->field_type)
                    @case('text')
                    @case('email')
                    @case('phone')
                    @case('number')
                        <input
                            type="{{ $field->field_type === 'number' ? 'number' : 'text' }}"
                            id="{{ $field->field_name }}"
                            name="{{ $field->field_name }}"
                            value="{{ old($field->field_name) }}"
                            {{ $field->is_required ? 'required' : '' }}
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($field->field_name) border-red-500 @enderror"
                            placeholder="Enter {{ strtolower($field->field_label) }}"
                        >
                        @break

                    @case('textarea')
                        <textarea
                            id="{{ $field->field_name }}"
                            name="{{ $field->field_name }}"
                            rows="4"
                            {{ $field->is_required ? 'required' : '' }}
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($field->field_name) border-red-500 @enderror"
                            placeholder="Enter {{ strtolower($field->field_label) }}"
                        >{{ old($field->field_name) }}</textarea>
                        @break

                    @case('date')
                        <input
                            type="date"
                            id="{{ $field->field_name }}"
                            name="{{ $field->field_name }}"
                            value="{{ old($field->field_name) }}"
                            {{ $field->is_required ? 'required' : '' }}
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($field->field_name) border-red-500 @enderror"
                        >
                        @break

                    @case('select')
                        <select
                            id="{{ $field->field_name }}"
                            name="{{ $field->field_name }}"
                            {{ $field->is_required ? 'required' : '' }}
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($field->field_name) border-red-500 @enderror"
                        >
                            <option value="">Select {{ strtolower($field->field_label) }}</option>
                            @if(!empty($field->options) && is_array($field->options))
                                @foreach($field->options as $value => $label)
                                    <option value="{{ $value }}" {{ old($field->field_name) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @break

                    @case('file')
                        <div x-data="{ fileName: '' }">
                            <div class="flex items-center">
                                <label class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <span class="text-sm text-gray-700">Choose file</span>
                                    <input
                                        type="file"
                                        id="{{ $field->field_name }}"
                                        name="{{ $field->field_name }}"
                                        {{ $field->is_required ? 'required' : '' }}
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        class="hidden"
                                        @change="fileName = $event.target.files[0]?.name || ''"
                                    >
                                </label>
                                <span x-text="fileName || 'No file chosen'" class="ml-3 text-sm text-gray-500"></span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Accepted formats: PDF, JPG, PNG (Max 5MB)
                            </p>
                        </div>
                        @break
                @endswitch

                @error($field->field_name)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <!-- Submit Button -->
        <div class="mt-8 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                <span class="text-red-500">*</span> Required fields
            </p>
            <button
                type="submit"
                class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
            >
                Submit Application
            </button>
        </div>
    </form>
</div>
@endsection
