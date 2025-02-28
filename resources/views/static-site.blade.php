<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coding Questions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Add any custom CSS here */
        [x-cloak] {
            display: none !important;
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-900 text-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center mb-8">Coding Questions</h1>

        <div x-data="{ activeTab: 'easy' }">
            <!-- Difficulty Tabs -->
            <div class="flex justify-center space-x-4 mb-8">
                @foreach($groupedQuestions as $difficulty => $questions)
                <button @click="activeTab = '{{ $difficulty }}'"
                    :class="{ 'bg-blue-600': activeTab === '{{ $difficulty }}', 'bg-gray-700': activeTab !== '{{ $difficulty }}' }"
                    class="px-4 py-2 rounded-lg transition-colors">
                    {{ ucfirst($difficulty) }}
                </button>
                @endforeach
            </div>

            <!-- Questions Grid -->
            @foreach($groupedQuestions as $difficulty => $questions)
            <div x-show="activeTab === '{{ $difficulty }}'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($questions as $question)
                    <div x-data="{ open: false }"
                        class="bg-gray-800 rounded-lg p-6 shadow-lg hover:shadow-xl transition-shadow">
                        <h3 class="text-xl font-semibold mb-4">{{ $question->title_en }}</h3>

                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm text-gray-400">{{ $question->source }}</span>
                            <button @click="open = !open" class="text-blue-400 hover:text-blue-300">
                                View Details
                            </button>
                        </div>

                        <!-- Question Details -->
                        <div x-show="open" x-cloak class="mt-4">
                            <div class="prose prose-invert max-w-none">
                                {!! $question->description_html !!}
                            </div>

                            @if($question->initial_code)
                            <div class="mt-4">
                                <h4 class="text-lg font-semibold mb-2">Initial Code:</h4>
                                <pre
                                    class="bg-gray-900 p-4 rounded-lg overflow-x-auto"><code>{{ $question->initial_code }}</code></pre>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</body>

</html>