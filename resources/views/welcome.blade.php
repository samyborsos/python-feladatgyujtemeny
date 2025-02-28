<x-layout>
    <div class="relative min-h-screen isolate overflow-hidden">
        <!-- Animated gradient background -->
        <div class="absolute inset-0 -z-10 overflow-hidden">
            <div
                class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-900 to-amber-950 animate-gradient-slow">
            </div>
            <div class="absolute inset-x-0 -top-40 transform-gpu overflow-hidden blur-3xl sm:-top-80">
                <div
                    class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-amber-500 to-yellow-600 opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]">
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="relative flex flex-col items-center justify-center min-h-screen px-6 py-10 mx-auto">
            <div class="text-center space-y-8 animate-fade-in">
                <h1
                    class="text-4xl sm:text-6xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-yellow-400">
                    CodeMentor
                </h1>

                <p class="text-xl text-gray-300 max-w-md mx-auto">
                    Fejleszd programozási készségeidet valódi kihívásokkal
                </p>

                <div class="flex justify-center">
                    <a href="{{ route('questions.index') }}"
                        class="group relative px-6 py-3 text-lg font-semibold text-white rounded-lg overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-amber-500 to-yellow-500 transition-all duration-300 group-hover:opacity-90">
                        </div>
                        <span class="relative flex items-center gap-2">
                            Kezdj gyakorolni
                            <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>

<style>
    @keyframes gradient {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .animate-gradient-slow {
        background-size: 200% 200%;
        animation: gradient 15s ease infinite;
    }

    .animate-fade-in {
        animation: fadeIn 1s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>