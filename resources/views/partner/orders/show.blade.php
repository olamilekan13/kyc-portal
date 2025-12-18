@extends('layouts.public')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('partner.orders.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Orders
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Order Details</h1>
                    <p class="mt-1 text-sm text-gray-600">Order Number: <span class="font-medium">{{ $order->order_number }}</span></p>
                </div>
                <div>
                    @if($order->payment_status === 'pending')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            Pending Payment
                        </span>
                    @elseif($order->payment_status === 'completed')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Paid
                        </span>
                    @endif
                </div>
            </div>
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

        <!-- Order Information -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Information</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Order Number</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $order->order_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Order Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->created_at->format('M d, Y h:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Partnership Model</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->partnership_model_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Duration</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $order->duration_months }} months</dd>
                </div>
                @if($order->start_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $order->start_date->format('M d, Y') }}</dd>
                    </div>
                @endif
                @if($order->end_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">End Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $order->end_date->format('M d, Y') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Pricing Breakdown -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Pricing Breakdown</h2>
            <div class="space-y-3">
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
                    <span class="font-medium">₦{{ number_format($order->signup_fee_amount, 2) }}</span>
                </div>
                <div class="border-t border-gray-200 pt-3">
                    <div class="flex justify-between">
                        <span class="text-base font-semibold text-gray-900">Total Amount:</span>
                        <span class="text-2xl font-bold text-blue-600">₦{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Information</h2>

            @if($order->payment_status === 'pending' && $order->payment_proof)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Payment proof submitted. Awaiting admin verification.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($order->payment_status === 'completed')
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                Payment verified and completed successfully!
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm font-medium text-gray-500">Payment Status:</dt>
                    <dd class="text-sm text-gray-900">
                        @if($order->payment_status === 'pending')
                            <span class="text-yellow-600 font-medium">Pending</span>
                        @elseif($order->payment_status === 'completed')
                            <span class="text-green-600 font-medium">Completed</span>
                        @else
                            <span class="text-gray-600 font-medium">{{ ucfirst($order->payment_status) }}</span>
                        @endif
                    </dd>
                </div>
                @if($order->payment_method)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Payment Method:</dt>
                        <dd class="text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</dd>
                    </div>
                @endif
                @if($order->paid_at)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Paid At:</dt>
                        <dd class="text-sm text-gray-900">{{ $order->paid_at->format('M d, Y h:i A') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <a href="{{ route('partner.dashboard') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Back to Dashboard
            </a>

            @if($order->payment_status === 'pending' && !$order->payment_proof)
                <a href="{{ route('partner.orders.payment', $order) }}"
                   class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Make Payment
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
