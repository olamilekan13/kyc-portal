@extends('layouts.public')

@section('title', 'KYC Details')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <h1 class="text-3xl font-bold text-gray-900">KYC Submission Details</h1>
                <a href="{{ route('partner.dashboard') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900">Submission Information</h2>
                <p class="mt-1 text-sm text-gray-600">Reference Number: #{{ $kycSubmission->id }}</p>
            </div>

            <div class="space-y-4">
                @foreach ($kycSubmission->submission_data as $key => $value)
                    @if (!str_ends_with($key, '_verified') && !is_array($value))
                        <div class="border-b border-gray-200 pb-4">
                            <dt class="text-sm font-medium text-gray-500">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $value }}</dd>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if ($kycSubmission->status === 'approved')
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif ($kycSubmission->status === 'declined')
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">
                            Status: <span class="text-{{ $kycSubmission->status === 'approved' ? 'green' : ($kycSubmission->status === 'declined' ? 'red' : 'yellow') }}-600">
                                {{ ucfirst($kycSubmission->status) }}
                            </span>
                        </p>
                        <p class="text-sm text-gray-500">Submitted on {{ $kycSubmission->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
