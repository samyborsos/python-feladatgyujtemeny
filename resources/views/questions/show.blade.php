<x-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <a href="{{ route('questions.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600/10 text-blue-400 
                                       border border-blue-500/20 hover:bg-blue-600/20 hover:border-blue-500/30 
                                       transition-all duration-200 group-hover:translate-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Vissza a feladatokhoz</span>

            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 transition-colors">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-gray-100">{{ $question->title_hu }}</h1>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                        @if($question->difficulty === '1') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100
                        @elseif($question->difficulty === '2') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-100
                        @elseif($question->difficulty === '3') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-100
                        @elseif($question->difficulty === '4') bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-100
                        @else bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-100
                        @endif">
                        {{ $question->difficulty }}. szint
                    </span>
                </div>
            </div>

            <div class="prose prose-invert max-w-none">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Feladat leírása</h2>
                {!! $question->description_hu !!}
            </div>

            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Függvény váz</h2>
                <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                    <pre class="font-mono text-sm text-gray-800 dark:text-gray-200">{{ $question->initial_code }}</pre>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Tesztesetek</h2>
                <div class="grid gap-4">
                    @php
                    $testCases = is_string($question->test_cases) ? json_decode($question->test_cases, true) :
                    $question->test_cases;
                    @endphp

                    @if(is_array($testCases))
                    @foreach($testCases as $index => $test)
                    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow transition-transform transform">
                        <p class="font-semibold mb-2 text-gray-900 dark:text-gray-100">{{ $index + 1 }}. teszteset:</p>
                        <div class="font-mono text-sm mb-2">
                            <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded">
                                <pre class="text-gray-800 dark:text-gray-200">{{ $test['input'] }}</pre>
                            </div>
                        </div>
                        <div class="font-mono text-sm">
                            <p class="text-gray-600 dark:text-gray-400 mb-1">Várt kimenet:</p>
                            <div class="bg-gray-100 dark:bg-gray-800 p-2 rounded">
                                <pre class="text-gray-800 dark:text-gray-200">{{ $test['expected'] }}</pre>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <p>No test cases available.</p>
                    @endif
                </div>
            </div>

            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Megoldás</h2>
                <div class="relative">
                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                        @php
                        $solution = $question->solution;
                        $solution = preg_replace('/^```python\n?/', '', $solution);
                        $solution = preg_replace('/```$/', '', $solution);
                        $solution = trim($solution);
                        @endphp
                        <pre class="language-python"><code class="text-sm text-gray-200">{{ $solution }}</code></pre>
                        <button onclick="copySolution()" class="absolute top-3 right-3 px-4 py-2 
                                       bg-blue-500/10 text-blue-400 
                                       rounded-lg border border-blue-500/20
                                       hover:bg-blue-500/20 hover:border-blue-500/30 
                                       transition-all duration-300 ease-in-out
                                       flex items-center gap-2 group">
                            <i class="fas fa-copy group-hover:scale-110 transition-transform duration-200"></i>
                            <span class="text-sm font-medium">Másolás</span>
                        </button>
                    </div>
                </div>
            </div>

            <div id="results" class="mt-8 hidden">
                <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Teszteredmények</h2>
                <div id="test-results" class="grid gap-4">
                </div>
            </div>
        </div>
    </div>

    <script>
        function copySolution() {
            const solution = `{!! str_replace('`', '\`', $solution) !!}`;
            const btn = event.target.closest('button');
            
            // Change button appearance during copy
            btn.innerHTML = `
                <i class="fas fa-spinner fa-spin"></i>
                <span class="text-sm font-medium">Másolás...</span>
            `;
            
            navigator.clipboard.writeText(solution).then(() => {
                // Success state
                btn.innerHTML = `
                    <i class="fas fa-check text-green-400"></i>
                    <span class="text-sm font-medium text-green-400">Másolva!</span>
                `;
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    btn.innerHTML = `
                        <i class="fas fa-copy group-hover:scale-110 transition-transform duration-200"></i>
                        <span class="text-sm font-medium">Másolás</span>
                    `;
                }, 2000);
            }).catch(() => {
                // Error state
                btn.innerHTML = `
                    <i class="fas fa-times text-red-400"></i>
                    <span class="text-sm font-medium text-red-400">Hiba!</span>
                `;
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    btn.innerHTML = `
                        <i class="fas fa-copy group-hover:scale-110 transition-transform duration-200"></i>
                        <span class="text-sm font-medium">Másolás</span>
                    `;
                }, 2000);
            });
        }
    </script>
</x-layout>