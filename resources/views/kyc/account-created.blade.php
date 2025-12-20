@extends('layouts.public')

@section('title', 'Account Created Successfully')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-2xl">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <!-- Success Icon -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                <svg class="h-10 w-10 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Title -->
            <div class="mt-6 text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Account Created Successfully!
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Your KYC submission has been received and your partner account has been created.
                </p>
            </div>

            <!-- Credentials Box -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Your Login Credentials
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <div class="flex items-center bg-white border border-gray-300 rounded-md px-3 py-2">
                            <span class="text-gray-900 font-mono text-sm flex-1">{{ $email }}</span>
                            <button onclick="copyToClipboard('{{ $email }}')"
                                    class="ml-2 text-blue-600 hover:text-blue-700 focus:outline-none"
                                    title="Copy email">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <div class="flex items-center bg-white border border-gray-300 rounded-md px-3 py-2">
                            <span class="text-gray-900 font-mono text-sm flex-1" id="password-text">{{ $password }}</span>
                            <button onclick="togglePassword()"
                                    class="ml-2 text-gray-600 hover:text-gray-700 focus:outline-none"
                                    title="Toggle visibility"
                                    id="toggle-btn">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <button onclick="copyToClipboard('{{ $password }}')"
                                    class="ml-2 text-blue-600 hover:text-blue-700 focus:outline-none"
                                    title="Copy password">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">
                                {{ $credentialsTitle }}
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>{{ $credentialsMessage }} These credentials have also been sent to your email address: <strong>{{ $email }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="mt-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">What's Next?</h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-600 text-sm font-bold">1</div>
                        </div>
                        <p class="ml-3 text-sm text-gray-600">
                            Log in to your partner dashboard using the credentials above
                        </p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-600 text-sm font-bold">2</div>
                        </div>
                        <p class="ml-3 text-sm text-gray-600">
                            Complete the partnership form by selecting your preferred partnership model
                        </p>
                    </div>
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-6 w-6 rounded-full bg-blue-100 text-blue-600 text-sm font-bold">3</div>
                        </div>
                        <p class="ml-3 text-sm text-gray-600">
                            Complete the payment to activate your partnership
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-8">
                <a href="{{ url('/partner/login') }}"
                   class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Continue to Partner Dashboard
                </a>
            </div>

            <!-- Alternative: Direct Link -->
            <div class="mt-4 text-center">
                <p class="text-sm text-gray-600">
                    Or visit directly: <a href="{{ url('/partner/login') }}" class="text-blue-600 hover:text-blue-700 font-medium">{{ url('/partner/login') }}</a>
                </p>
            </div>

            <!-- Support Info -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>If you have any questions or need assistance, please contact our support team.</p>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}

let passwordVisible = true;
const originalPassword = '{{ $password }}';

function togglePassword() {
    const passwordText = document.getElementById('password-text');
    passwordVisible = !passwordVisible;

    if (passwordVisible) {
        passwordText.textContent = originalPassword;
    } else {
        passwordText.textContent = '••••••••••';
    }
}
</script>
@endsection
