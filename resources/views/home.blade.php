<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings->title ?? 'KYC Portal' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'dmp-red': '#C1272D',
                        'dmp-blue': '#2563EB',
                        'dmp-dark-blue': '#1E40AF',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .pulse-slow {
            animation: pulse-slow 4s ease-in-out infinite;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 via-white to-red-50">
    <!-- Decorative background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-10 w-72 h-72 bg-dmp-blue/10 rounded-full blur-3xl pulse-slow"></div>
        <div class="absolute bottom-20 left-10 w-96 h-96 bg-dmp-red/10 rounded-full blur-3xl pulse-slow" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-md shadow-sm sticky top-0 z-50 border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <a href="https://partners.dmpluspower.com/" target="_blank" rel="noopener noreferrer" class="flex items-center transform hover:scale-105 transition-transform duration-200">
                            @if(file_exists(public_path('images/logo.png')))
                                <img src="{{ asset('images/logo.png') }}" alt="DmplusPower" class="h-14 w-auto">
                            @elseif(file_exists(public_path('images/logo.jpg')))
                                <img src="{{ asset('images/logo.jpg') }}" alt="DmplusPower" class="h-14 w-auto">
                            @elseif(file_exists(public_path('images/logo.svg')))
                                <img src="{{ asset('images/logo.svg') }}" alt="DmplusPower" class="h-14 w-auto">
                            @else
                                <span class="text-2xl font-bold text-dmp-red">Dmplus</span><span class="text-2xl font-bold text-dmp-blue">Power</span>
                            @endif
                        </a>
                    </div>

                    <!-- Navigation -->
                    @if (Route::has('login'))
                        <nav class="flex items-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 bg-dmp-blue text-white font-semibold rounded-lg hover:bg-dmp-dark-blue transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-4 py-2 text-gray-700 font-medium hover:text-dmp-blue transition-colors duration-200">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-6 py-2 bg-gradient-to-r from-dmp-red to-red-600 text-white font-semibold rounded-lg hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-6xl mx-auto w-full">
                <div class="text-center mb-12">
                    <!-- Lightning Icon -->
                    <div class="flex justify-center mb-8 float-animation">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-dmp-red to-dmp-blue rounded-full blur-xl opacity-50"></div>
                            <div class="relative bg-gradient-to-br from-dmp-red via-dmp-blue to-dmp-dark-blue p-6 rounded-full">
                                <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Page Title -->
                    <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6 bg-gradient-to-r from-dmp-red via-dmp-blue to-dmp-dark-blue bg-clip-text text-transparent leading-tight">
                        {{ $settings->title ?? 'Welcome to KYC Portal' }}
                    </h1>

                    <!-- Subtitle -->
                    @if($settings && $settings->subtitle)
                        <p class="text-xl md:text-2xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed">
                            {{ $settings->subtitle }}
                        </p>
                    @endif

                    <!-- Call to Action Button -->
                    <div class="mb-16">
                        <a href="/kyc" class="group inline-flex items-center px-10 py-5 text-xl font-bold text-white bg-gradient-to-r from-dmp-red to-dmp-blue rounded-full shadow-2xl hover:shadow-3xl transform hover:scale-105 transition-all duration-300 hover:from-dmp-blue hover:to-dmp-red">
                            <span>{{ $settings->button_text ?? 'Start KYC Process' }}</span>
                            <svg class="w-6 h-6 ml-3 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                        <p class="mt-4 text-sm text-gray-500">
                            <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            Secure & Encrypted Process
                        </p>
                    </div>
                </div>

                <!-- Instructions / Content -->
                @if($settings && $settings->instructions)
                    <div class="max-w-4xl mx-auto">
                        <div class="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl p-8 md:p-12 border border-gray-100 hover:shadow-3xl transition-shadow duration-300">
                            <div class="prose prose-lg prose-headings:text-gray-900 prose-p:text-gray-600 prose-a:text-dmp-blue prose-strong:text-gray-900 prose-ul:text-gray-600 max-w-none">
                                {!! $settings->instructions !!}
                            </div>
                        </div>

                        <!-- Features Grid -->
                        <div class="grid md:grid-cols-3 gap-6 mt-12">
                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-blue-100">
                                <div class="w-12 h-12 bg-gradient-to-br from-dmp-blue to-blue-600 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Secure Process</h3>
                                <p class="text-gray-600 text-sm">Your data is encrypted and protected with industry-standard security</p>
                            </div>

                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-red-100">
                                <div class="w-12 h-12 bg-gradient-to-br from-dmp-red to-red-600 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Fast Verification</h3>
                                <p class="text-gray-600 text-sm">Quick and efficient verification process in minutes</p>
                            </div>

                            <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 border border-purple-100">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mb-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900 mb-2">Compliant</h3>
                                <p class="text-gray-600 text-sm">Fully compliant with regulatory requirements and standards</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-dmp-blue" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm text-gray-600">
                            &copy; {{ date('Y') }} <a href="https://partners.dmpluspower.com/" target="_blank" rel="noopener noreferrer" class="font-semibold text-dmp-blue hover:text-dmp-dark-blue transition-colors">DmplusPower</a>. All rights reserved.
                        </p>
                    </div>
                    <p class="text-xs text-gray-500 italic">...power on the go!!</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
