<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: localStorage.getItem('darkMode') ?? 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode === 'true' }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('codementor_favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="mask-icon" href="{{ asset('favicon.svg') }}" color="#000000">
    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full flex flex-col transition-colors duration-200
             bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <nav class="mx-auto container px-1 sm:px-2 lg:px-2 py-4">
            <div class="flex justify-between items-center">
                <a href="/" class="text-2xl font-bold text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300">
                    <div class="flex justify-center items-center">
                        <div class="flex items-center">
                            <!-- Circular SVG Logo -->
                            <svg width="60" height="60" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="50" cy="50" r="30" stroke="#D4AF37" stroke-width="5" fill="none" />
                                <circle cx="50" cy="50" r="20" stroke="#D4AF37" stroke-width="5" fill="none" />
                                <circle cx="50" cy="50" r="10" stroke="#D4AF37" stroke-width="5" fill="none" />
                            </svg>
            
                            <!-- Text Logo with Tailwind adjustments -->
                            <span class="text-3xl font-bold text-[#FFD700] flex items-center -mt-1">codementor</span>
                        </div>
                    </div>
                </a>
               {{--  <div class="flex items-center gap-4">
                    <button @click="darkMode = (darkMode === 'true' ? 'false' : 'true')"
                        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <!-- Sun icon -->
                        <svg x-show="darkMode === 'true'" class="w-6 h-6 text-yellow-400" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Moon icon -->
                        <svg x-show="darkMode === 'false'" class="w-6 h-6 text-gray-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div> --}}
            </div>
        </nav>
    </header>

    <!-- Main content -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-600 dark:text-gray-400">
                © 2024 CodeMentor. Minden jog fenntartva.
            </p>
        </div>
    </footer>
</body>

</html>