<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full"
    x-data="{ darkMode: localStorage.getItem('darkMode') ?? 'true' }"
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode === 'true' }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('codementor_favicon.svg') }}">
    <link rel="mask-icon" href="{{ asset('codementor_favicon.svg') }}" color="#000000">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="min-h-screen bg-gray-900 text-gray-100">
    <!-- Header -->
    <header class="relative z-10">
        <nav class="mx-auto container px-6 py-4">
            <div class="flex items-center justify-between">
                <a href="/"
                    class="flex items-center gap-2 text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-yellow-400">
                    <svg width="35" height="35" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="48" stroke="#D4AF37" stroke-width="4" fill="none" />
                        <circle cx="50" cy="50" r="32" stroke="#D4AF37" stroke-width="4" fill="none" />
                        <circle cx="50" cy="50" r="16" stroke="#D4AF37" stroke-width="4" fill="none" />
                    </svg>

                    CodeMentor
                </a>

                <div class="flex items-center gap-4">
                    @auth
                    <a href="{{ route('questions.index') }}"
                        class="text-gray-300 hover:text-amber-300 transition">Feladatok</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-300 hover:text-amber-300 transition">
                            Kijelentkezés
                        </button>
                    </form>
                    @else
                    {{-- <a href="{{ route('login') }}"
                        class="text-gray-300 hover:text-amber-300 transition">Bejelentkezés</a>
                    <a href="{{ route('register') }}"
                        class="text-gray-300 hover:text-amber-300 transition">Regisztráció</a> --}}
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="relative z-10 mt-auto">
        <div class="mx-auto max-w-7xl px-6 py-4">
            <div class="border-t border-gray-800 pt-4">
                <p class="text-center text-sm text-gray-500">
                    © {{ date('Y') }} CodeMentor. Minden jog fenntartva.
                </p>
            </div>
        </div>
    </footer>
</body>

</html>