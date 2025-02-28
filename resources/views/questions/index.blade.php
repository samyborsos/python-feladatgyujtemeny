<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    } */
</style>

<x-layout>
    <!-- Loading Spinner -->
    <div id="loading-spinner"
        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm hidden">
        <div class="relative">
            <!-- Outer ring -->
            <div class="w-16 h-16 border-4 border-blue-400/20 rounded-full animate-spin">
            </div>
            <!-- Inner ring -->
            <div class="w-16 h-16 border-4 border-t-blue-500 rounded-full animate-spin absolute top-0 left-0">
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8" style="padding-top: 50px;">
        <!-- Search Bar with Count -->
        <div class="mb-8 text-center">
            <div class="text-gray-400 mb-2">
                Search in over <span class="font-semibold text-blue-400">{{ $questions->total() }}</span> questions
            </div>
        </div>

        <!-- Search Form -->
        <form action="{{ route('questions.index') }}" method="GET"
            class="mb-8 bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 border border-gray-700/50">
            <!-- Search Input Row -->
            <div class="mb-6">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-3 bg-gray-900/50 border border-gray-600/50 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 text-gray-100 placeholder-gray-400"
                        placeholder="Keresés feladatok között...">
                </div>
            </div>

            <!-- Divider -->
            <div class="h-px w-full bg-gray-700/50 mb-6"></div>

            <!-- Filters Row -->
            <div class="flex flex-wrap gap-4">
                <!-- Source Filter Buttons -->
                <div>
                    <input type="hidden" name="source" value="{{ request('source') }}" id="sourceInput">
                </div>
                <div class="flex items-center gap-2">

                    <button type="button"
                        onclick="document.getElementById('sourceInput').value = ''; this.form.submit();"
                        class="px-4 py-2 rounded-lg flex items-center gap-2 transition-all duration-200
                            {{ !request('source') ? 'bg-blue-600/30 text-blue-300 border-blue-500/50' : 'bg-gray-800/50 text-gray-400 hover:bg-gray-700/50' }}">
                        <i class="fas fa-globe text-lg"></i>
                        <span>Mind</span>
                    </button>

                    <button type="button"
                        onclick="document.getElementById('sourceInput').value = 'CodeWars API'; this.form.submit();"
                        class="px-4 py-2 rounded-lg flex items-center gap-2 transition-all duration-200
                            {{ request('source') == 'CodeWars API' ? 'bg-blue-600/30 text-blue-300 border-blue-500/50' : 'bg-gray-800/50 text-gray-400 hover:bg-gray-700/50' }}">
                        <i class="fas fa-code text-lg"></i>
                        <span>CodeWars</span>
                    </button>

                    <button type="button"
                        onclick="document.getElementById('sourceInput').value = 'LeetCode API'; this.form.submit();"
                        class="px-4 py-2 rounded-lg flex items-center gap-2 transition-all duration-200
                            {{ request('source') == 'LeetCode API' ? 'bg-blue-600/30 text-blue-300 border-blue-500/50' : 'bg-gray-800/50 text-gray-400 hover:bg-gray-700/50' }}">
                        <i class="fas fa-terminal text-lg"></i>
                        <span>LeetCode</span>
                    </button>
                </div>

                <!-- Divider -->
                <div class="h-8 w-px bg-gray-700/50"></div>

                <!-- Sort Buttons -->
                <div class="flex items-center gap-2">
                    <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}" id="sortInput">

                    <button type="button"
                        onclick="document.getElementById('sortInput').value = 'newest'; this.form.submit();"
                        class="px-4 py-2 rounded-lg flex items-center gap-2 transition-all duration-200
                            {{ request('sort', 'newest') == 'newest' ? 'bg-purple-600/30 text-purple-300 border-purple-500/50' : 'bg-gray-800/50 text-gray-400 hover:bg-gray-700/50' }}">
                        <i class="fas fa-clock-rotate-left text-lg"></i>
                        <span>Legújabb</span>
                    </button>

                    <button type="button"
                        onclick="document.getElementById('sortInput').value = 'oldest'; this.form.submit();"
                        class="px-4 py-2 rounded-lg flex items-center gap-2 transition-all duration-200
                            {{ request('sort') == 'oldest' ? 'bg-purple-600/30 text-purple-300 border-purple-500/50' : 'bg-gray-800/50 text-gray-400 hover:bg-gray-700/50' }}">
                        <i class="fas fa-clock text-lg"></i>
                        <span>Legrégebbi</span>
                    </button>

                    <div class="relative group">
                        <button type="button"
                            class="px-4 py-2 rounded-lg flex items-center gap-2 transition-all duration-200
                                {{ in_array(request('sort'), ['difficulty_asc', 'difficulty_desc']) ? 'bg-purple-600/30 text-purple-300 border-purple-500/50' : 'bg-gray-800/50 text-gray-400 hover:bg-gray-700/50' }}">
                            <i class="fas fa-signal text-lg"></i>
                            <span>Nehézség</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            class="absolute top-full left-0 mt-2 w-48 rounded-lg bg-gray-800/95 backdrop-blur-sm border border-gray-700/50 shadow-lg overflow-hidden invisible group-hover:visible opacity-0 group-hover:opacity-100 transition-all duration-200">
                            <button type="button"
                                onclick="document.getElementById('sortInput').value = 'difficulty_asc'; this.form.submit();"
                                class="w-full px-4 py-2 text-left hover:bg-gray-700/50 flex items-center gap-2
                                    {{ request('sort') == 'difficulty_asc' ? 'text-purple-300 bg-purple-600/20' : 'text-gray-300' }}">
                                <i class="fas fa-arrow-up-short-wide text-lg"></i>
                                <span>Növekvő</span>
                            </button>
                            <button type="button"
                                onclick="document.getElementById('sortInput').value = 'difficulty_desc'; this.form.submit();"
                                class="w-full px-4 py-2 text-left hover:bg-gray-700/50 flex items-center gap-2
                                    {{ request('sort') == 'difficulty_desc' ? 'text-purple-300 bg-purple-600/20' : 'text-gray-300' }}">
                                <i class="fas fa-arrow-down-wide-short text-lg"></i>
                                <span>Csökkenő</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Clear Filters Button (only show if any filter is active) -->
                @if(request()->anyFilled(['search', 'source', 'sort']))
                <button type="button" onclick="window.location.href = '{{ route('questions.index') }}'"
                    class="px-4 py-2 rounded-lg flex items-center gap-2 bg-red-900/30 text-red-300 border border-red-500/50 hover:bg-red-800/30 transition-all duration-200">
                    <i class="fas fa-xmark text-lg"></i>
                    <span>Szűrők törlése</span>
                </button>
                @endif
            </div>
        </form>

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-100 transition-transform transform hover:scale-105">Feladatok</h1>
            {{-- <form action="{{ route('questions.refresh') }}" method="POST">
                @csrf
                <button type="submit"
                    class="bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white font-bold py-2 px-4 rounded transition-transform transform hover:scale-105">
                    Feladatok frissítése
                </button>
            </form> --}}
        </div>

        <!-- Fixed Navigation Links -->
        <div class="fixed top-0 left-0 right-0 z-50 bg-gray-900/95 backdrop-blur-sm">
            <div class="container mx-auto px-4">
                <div class="flex justify-between items-center py-4">
                    <a href="/"
                        class="flex items-center gap-2 text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-yellow-400 transition-transform transform hover:scale-105">
                        <svg width="35" height="35" viewBox="0 0 100 100" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="48" stroke="#D4AF37" stroke-width="4" fill="none" />
                            <circle cx="50" cy="50" r="32" stroke="#D4AF37" stroke-width="4" fill="none" />
                            <circle cx="50" cy="50" r="16" stroke="#D4AF37" stroke-width="4" fill="none" />
                        </svg>
                        CodeMentor
                    </a>


                </div>
            </div>
        </div>

        @if (session('success'))
        <div
            class="bg-green-900 border border-green-700 text-green-100 px-4 py-3 rounded relative mb-4 transition-opacity duration-300">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div
            class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded relative mb-4 transition-opacity duration-300">
            {{ session('error') }}
        </div>
        @endif

        <!-- Questions Grid -->
        <div class="mt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($questions as $question)
                <div class="group relative bg-gray-800/50 backdrop-blur-sm rounded-xl shadow-lg border border-gray-700/50 p-6 
                               transition-all duration-300 hover:bg-gray-800 hover:border-gray-600 hover:shadow-2xl">
                    <div class="flex flex-col h-full">
                        <!-- Header with Title and Source -->
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-semibold text-gray-100 group-hover:text-white">
                                {{ $question->title_hu }}
                            </h3>
                            <span class="text-sm text-gray-400  px-2 py-1 rounded-lg flex items-center gap-2">
                                @if($question->source == 'CodeWars API')
                                <img src="https://cdn.prod.website-files.com/6674f0cdb5b7b401612cf015/6674f0cdb5b7b401612cf067_codewars-logomark.svg"
                                    alt="CodeWars" class="h-6 w-6">
                                @elseif($question->source == 'LeetCode API')
                                <svg height="24" viewBox="0 0 95 111" fill="none" xmlns="http://www.w3.org/2000/svg"
                                    class="w-auto">
                                    <path
                                        d="M68.0063 83.0664C70.5 80.5764 74.5366 80.5829 77.0223 83.0809C79.508 85.579 79.5015 89.6226 77.0078 92.1127L65.9346 103.17C55.7187 113.371 39.06 113.519 28.6718 103.513C28.6117 103.456 23.9861 98.9201 8.72653 83.957C-1.42528 74.0029 -2.43665 58.0749 7.11648 47.8464L24.9282 28.7745C34.4095 18.6219 51.887 17.5122 62.7275 26.2789L78.9048 39.362C81.6444 41.5776 82.0723 45.5985 79.8606 48.3429C77.6488 51.0873 73.635 51.5159 70.8954 49.3003L54.7182 36.2173C49.0488 31.6325 39.1314 32.2622 34.2394 37.5006L16.4274 56.5727C11.7767 61.5522 12.2861 69.574 17.6456 74.8292C28.851 85.8169 37.4869 94.2846 37.4969 94.2942C42.8977 99.496 51.6304 99.4184 56.9331 94.1234L68.0063 83.0664Z"
                                        fill="#FFA116" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M41.1067 72.0014C37.5858 72.0014 34.7314 69.1421 34.7314 65.615C34.7314 62.0879 37.5858 59.2286 41.1067 59.2286H88.1245C91.6454 59.2286 94.4997 62.0879 94.4997 65.615C94.4997 69.1421 91.6454 72.0014 88.1245 72.0014H41.1067Z"
                                        fill="#B3B3B3" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M49.9118 2.02335C52.3173 -0.55232 56.3517 -0.686894 58.9228 1.72277C61.494 4.13244 61.6284 8.17385 59.2229 10.7495L16.4276 56.5729C11.7768 61.552 12.2861 69.5738 17.6453 74.8292L37.4088 94.2091C39.9249 96.6764 39.968 100.72 37.505 103.24C35.042 105.761 31.0056 105.804 28.4895 103.337L8.72593 83.9567C-1.42529 74.0021 -2.43665 58.0741 7.1169 47.8463L49.9118 2.02335Z"
                                        fill="black" />
                                </svg>
                                @endif
                            </span>
                        </div>

                        <!-- Footer with Difficulty and Link -->
                        <div class="mt-auto pt-4 flex items-center justify-between">
                            <span class="inline-flex px-3 py-1.5 rounded-lg text-sm font-medium
                                    @if($question->difficulty === '1') bg-green-900/40 text-green-100 border border-green-700/50
                                    @elseif($question->difficulty === '2') bg-blue-900/40 text-blue-100 border border-blue-700/50
                                    @elseif($question->difficulty === '3') bg-yellow-900/40 text-yellow-100 border border-yellow-700/50
                                    @elseif($question->difficulty === '4') bg-orange-900/40 text-orange-100 border border-orange-700/50
                                    @else bg-red-900/40 text-red-100 border border-red-700/50
                                    @endif">
                                {{ $question->difficulty }}. szint
                            </span>

                            <a href="{{ route('questions.show', $question) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600/10 text-blue-400 
                                           border border-blue-500/20 hover:bg-blue-600/20 hover:border-blue-500/30 
                                           transition-all duration-200 group-hover:translate-x-1">
                                <span>Feladat</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $questions->appends(['difficulty' => request()->query('difficulty')])->links() }}
            </div>
        </div>
    </div>
</x-layout>

<script>
    function scrollToSection(sectionId, event) {
    event.preventDefault();
    const section = document.getElementById(sectionId);
    if (section) {
        const offset = 200; // Keep this offset for scrolling to sections
        const elementPosition = section.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;

        window.scrollTo({
            top: offsetPosition,
            behavior: 'smooth'
        });
    }
}

// Intersection Observer to highlight current section
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('[id^="difficulty-"]');
    const navLinks = document.querySelectorAll('.nav-link');

    const observerOptions = {
        root: null,
        rootMargin: '-25% 0px -75% 0px', // Adjusted margins to account for new scroll position
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const activeId = entry.target.id;
                navLinks.forEach(link => {
                    if (link.dataset.section === activeId) {
                        link.classList.add('ring-2', 'ring-white/50');
                        link.style.transform = 'scale(1.05)';
                    } else {
                        link.classList.remove('ring-2', 'ring-white/50');
                        link.style.transform = 'scale(1)';
                    }
                });
            }
        });
    }, observerOptions);

    sections.forEach(section => observer.observe(section));
});

// Back to top button functionality
document.addEventListener('DOMContentLoaded', function() {
    const backToTopButton = document.querySelector('.scroll-show');
    
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) { // Show button after scrolling 300px
            backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
            backToTopButton.classList.add('opacity-100', 'pointer-events-auto');
        } else {
            backToTopButton.classList.add('opacity-0', 'pointer-events-none');
            backToTopButton.classList.remove('opacity-100', 'pointer-events-auto');
        }
    });
});

// Show loading spinner on form submission and page navigation
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submissions
    const form = document.querySelector('form');
    const buttons = document.querySelectorAll('button');  // Changed to select all buttons
    const spinner = document.getElementById('loading-spinner');

    // Show spinner on form submit
    if (form) {
        form.addEventListener('submit', () => {
            spinner.classList.remove('hidden');
        });
    }

    // Show spinner on any button click
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            // Don't show spinner for buttons that have preventDefault() or return false
            if (!button.getAttribute('onclick')?.includes('return false') && 
                !button.getAttribute('onclick')?.includes('preventDefault')) {
                spinner.classList.remove('hidden');
            }
        });
    });

    // Show spinner on navigation links
    document.querySelectorAll('a').forEach(link => {
        if (link.href && !link.href.startsWith('#') && !link.href.includes('javascript:')) {
            link.addEventListener('click', () => {
                spinner.classList.remove('hidden');
            });
        }
    });

    // Hide spinner when page is fully loaded
    window.addEventListener('load', () => {
        spinner.classList.add('hidden');
    });

    // Hide spinner if navigation is cancelled
    window.addEventListener('pageshow', () => {
        spinner.classList.add('hidden');
    });

    // Hide spinner on back/forward navigation
    window.addEventListener('popstate', () => {
        spinner.classList.add('hidden');
    });
});
</script>

<!-- Add at the bottom of your content -->
<button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-8 right-8 bg-gray-800/50 backdrop-blur-sm p-3 rounded-full shadow-lg border border-gray-700/50 
           transition-all duration-300 hover:bg-gray-700/50 hover:scale-110
           opacity-0 pointer-events-none scroll-show">
    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </svg>
</button>