<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'KYC Submission Portal')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Prose/Typography styles for rich text content */
        .prose {
            color: #374151;
            max-width: 65ch;
        }
        .prose p {
            margin-top: 0.75em;
            margin-bottom: 0.75em;
        }
        .prose h2 {
            font-weight: 700;
            font-size: 1.25em;
            margin-top: 1.5em;
            margin-bottom: 0.75em;
            line-height: 1.3;
            color: #111827;
        }
        .prose h3 {
            font-weight: 600;
            font-size: 1.125em;
            margin-top: 1.25em;
            margin-bottom: 0.5em;
            line-height: 1.4;
            color: #111827;
        }
        .prose strong {
            font-weight: 600;
            color: #111827;
        }
        .prose em {
            font-style: italic;
        }
        .prose ul, .prose ol {
            margin-top: 0.75em;
            margin-bottom: 0.75em;
            padding-left: 1.625em;
        }
        .prose ul {
            list-style-type: disc;
        }
        .prose ol {
            list-style-type: decimal;
        }
        .prose li {
            margin-top: 0.375em;
            margin-bottom: 0.375em;
        }
        .prose a {
            color: #2563eb;
            text-decoration: underline;
            font-weight: 500;
        }
        .prose a:hover {
            color: #1d4ed8;
        }
        .prose-sm {
            font-size: 0.875rem;
            line-height: 1.5;
        }
        .prose-sm p {
            margin-top: 0.5em;
            margin-bottom: 0.5em;
        }
        .prose-sm h2 {
            font-size: 1.125em;
            margin-top: 1em;
            margin-bottom: 0.5em;
        }
        .prose-sm h3 {
            font-size: 1em;
            margin-top: 0.875em;
            margin-bottom: 0.375em;
        }
        .prose-sm ul, .prose-sm ol {
            margin-top: 0.5em;
            margin-bottom: 0.5em;
            padding-left: 1.5em;
        }
        .prose-sm li {
            margin-top: 0.25em;
            margin-bottom: 0.25em;
        }
        .max-w-none {
            max-width: none;
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    <!-- Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="https://partners.dmpluspower.com/" target="_blank" rel="noopener noreferrer" class="flex items-center">
                        @if(file_exists(public_path('images/logo.png')))
                            <img src="{{ asset('images/logo.png') }}" alt="DmplusPower" class="h-12 w-auto">
                        @elseif(file_exists(public_path('images/logo.jpg')))
                            <img src="{{ asset('images/logo.jpg') }}" alt="DmplusPower" class="h-12 w-auto">
                        @elseif(file_exists(public_path('images/logo.svg')))
                            <img src="{{ asset('images/logo.svg') }}" alt="DmplusPower" class="h-12 w-auto">
                        @else
                            <span class="text-xl font-bold text-red-600">Dmplus</span><span class="text-xl font-bold text-blue-600">Power</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} <a href="https://partners.dmpluspower.com/" target="_blank" rel="noopener noreferrer" class="hover:underline text-blue-600">DmplusPower</a>. All rights reserved.
            </p>
        </div>
    </footer>

    @stack('scripts')

    <!-- WhatsApp Business Chat Widget -->
    @php
        $whatsappEnabled = \App\Models\SystemSetting::get('whatsapp_chat_enabled', '0');
        $whatsappNumber = \App\Models\SystemSetting::get('whatsapp_business_number', '');
        $whatsappMessage = \App\Models\SystemSetting::get('whatsapp_welcome_message', 'Hello! How can we help you today?');
    @endphp

    @if($whatsappEnabled == '1' && !empty($whatsappNumber))
    <div id="whatsapp-chat-widget" x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
        <!-- Chat Button -->
        <button
            @click="open = !open"
            class="bg-green-500 hover:bg-green-600 text-white rounded-full p-4 shadow-2xl transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-4 focus:ring-green-300"
            aria-label="Open WhatsApp Chat"
        >
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
        </button>

        <!-- Chat Popup -->
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="absolute bottom-20 right-0 w-80 bg-white rounded-2xl shadow-2xl overflow-hidden"
        >
            <!-- Header -->
            <div class="bg-green-500 text-white p-4 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-7 h-7 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Chat with us</h3>
                        <p class="text-xs text-green-100">Typically replies instantly</p>
                    </div>
                </div>
                <button
                    @click="open = false"
                    class="text-white hover:text-green-100 transition-colors"
                    aria-label="Close chat"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Message Body -->
            <div class="p-6 bg-gray-50">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <p class="text-sm text-gray-700">{{ $whatsappMessage }}</p>
                    <p class="text-xs text-gray-500 mt-2">Click below to start chatting</p>
                </div>
            </div>

            <!-- Action Button -->
            <div class="p-4 bg-white border-t border-gray-100">
                <a
                    href="https://wa.me/{{ $whatsappNumber }}?text={{ urlencode($whatsappMessage) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center justify-center w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                    Start Chat
                </a>
            </div>
        </div>
    </div>
    @endif
</body>
</html>
