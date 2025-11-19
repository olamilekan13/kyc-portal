@extends('layouts.public')

@section('title', 'Onboarding Already Completed')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8 flex items-center">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-blue-600 px-8 py-10 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                    <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">Onboarding Complete</h1>
                <p class="text-blue-100">Your onboarding has already been completed</p>
            </div>

            <!-- Content -->
            <div class="px-8 py-8 text-center">
                <p class="text-gray-600 mb-6">
                    This onboarding link has already been used and the process has been completed.
                </p>

                <div class="p-5 bg-blue-50 border border-blue-200 rounded-xl mb-6">
                    <p class="text-sm text-gray-700">
                        <span class="font-semibold">Reference Number:</span><br>
                        <span class="font-mono text-lg text-gray-900">{{ $kycSubmission->onboarding_token }}</span>
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start text-left">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm text-gray-700">Your KYC submission has been received</p>
                    </div>
                    <div class="flex items-start text-left">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm text-gray-700">Your partnership model selection is complete</p>
                    </div>
                    <div class="flex items-start text-left">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm text-gray-700">Onboarding completed on {{ $kycSubmission->onboarding_completed_at->format('M d, Y') }}</p>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        If you have any questions, please contact our support team<br>
                        with your reference number.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
