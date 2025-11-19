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
</body>
</html>
