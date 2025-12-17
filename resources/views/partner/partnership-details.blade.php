@extends('layouts.public')

@section('title', 'Partnership Details')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <h1 class="text-3xl font-bold text-gray-900">Partnership Details</h1>
                <a href="{{ route('partner.dashboard') }}"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Partnership Model -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Partnership Model</h2>
                <div class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Model Name</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $finalOnboarding->partnership_model_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Model Price</dt>
                        <dd class="mt-1 text-lg font-semibold text-green-600">₦{{ number_format($finalOnboarding->partnership_model_price, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Duration</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $finalOnboarding->duration_months }} months</dd>
                    </div>
                    @if ($finalOnboarding->solar_power)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Solar Power Add-on</dt>
                            <dd class="mt-1 text-sm text-gray-900">₦{{ number_format($finalOnboarding->solar_power_amount ?? 0, 2) }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Information</h2>
                <div class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Signup Fee</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            ₦{{ number_format($finalOnboarding->signup_fee_amount, 2) }}
                            @if ($finalOnboarding->signup_fee_paid)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Paid</span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Model Fee</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            ₦{{ number_format($finalOnboarding->partnership_model_price, 2) }}
                            @if ($finalOnboarding->model_fee_paid)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Paid</span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </dd>
                    </div>
                    <div class="pt-3 border-t border-gray-200">
                        <dt class="text-sm font-medium text-gray-500">Total Amount</dt>
                        <dd class="mt-1 text-xl font-bold text-gray-900">₦{{ number_format($finalOnboarding->total_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                        <dd class="mt-1">
                            @if ($finalOnboarding->payment_status === 'completed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Completed</span>
                            @elseif ($finalOnboarding->payment_status === 'partial')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">Partial</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Pending</span>
                            @endif
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Partnership Duration -->
            @if ($finalOnboarding->partnership_start_date)
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Partnership Duration</h2>
                <div class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($finalOnboarding->partnership_start_date)->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">End Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($finalOnboarding->partnership_end_date)->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Days Remaining</dt>
                        <dd class="mt-1 text-sm font-semibold text-blue-600">
                            {{ max(0, now()->diffInDays(\Carbon\Carbon::parse($finalOnboarding->partnership_end_date), false)) }} days
                        </dd>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
