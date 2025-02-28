<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Throwable;

class LeetCodeService
{
    private $baseUrl = 'http://127.0.0.1:3000';
    private $translator;
    private $command;

    public function __construct($command = null)
    {
        $this->command = $command;
        $this->translator = new GoogleTranslate();
        $this->translator->setSource('en');
        $this->translator->setTarget('hu');
    }

    public function fetchQuestions($difficulty = null)
    {
        try {
            $this->command?->info("🔍 Loading all LeetCode questions...");

            // Load questions from JSON
            $jsonPath = storage_path('app' . DIRECTORY_SEPARATOR . 'leetcode' . DIRECTORY_SEPARATOR . 'all_leetcode_challenges.json');
            $data = json_decode(file_get_contents($jsonPath), true);
            $questions = collect($data['problemsetQuestionList']);

            // Take only first 30 questions for testing
            /* $questions = $questions->take(30); */

            $this->command?->info("Processing " . $questions->count() . " questions");

            $ai = new AIService();
            $batch = [];
            $batchSize = 50; // Process 5 questions at a time
            $processedCount = 0;
            $skippedCount = 0;
            $startTime = now();

            foreach ($questions as $index => $question) {
                try {
                    // Check if question already exists
                    if (\App\Models\Question::where('title_en', $question['title'])->exists()) {
                        $skippedCount++;
                        continue;
                    }

                    $this->command?->info("\nProcessing: {$question['title']} ({$question['titleSlug']})");

                    // Get question details from our local API
                    $response = Http::get("{$this->baseUrl}/select", [
                        'titleSlug' => $question['titleSlug']
                    ]);

                    if (!$response->successful()) {
                        $this->command?->error("  API Error: HTTP " . $response->status());
                        continue;
                    }

                    $details = $response->json();

                    // Check if question data exists
                    if (!isset($details['question']) || empty($details['question'])) {
                        $this->command?->warn("  No question content found for: {$question['title']}");
                        continue;
                    }

                    $description_en = $details['question'];

                    try {
                        // Only attempt translation if we have content
                        $title_hu = !empty($question['title']) ?
                            ($this->translator->translate($question['title']) ?? $question['title']) :
                            $question['title'];

                        $description_hu = !empty($description_en) ?
                            ($this->translator->translate($description_en) ?? $description_en) :
                            $description_en;

                        $batch[] = [
                            'title_en' => $question['title'],
                            'title_hu' => $title_hu,
                            'description_en' => $description_en,
                            'description_hu' => $description_hu,
                            'difficulty' => $this->mapDifficulty($question['difficulty']),
                            'initial_code' => $this->generatePythonCode($question),
                            'solution' => null,
                            'test_cases' => json_encode($this->generateTestCases()),
                            'source' => 'LeetCode API',
                            'created_at' => now()->format('Y-m-d H:i:s'),
                            'updated_at' => now()->format('Y-m-d H:i:s')
                        ];

                        $processedCount++;

                        // Insert batch when it reaches the batch size or at the end
                        if (count($batch) >= $batchSize || $index === count($questions) - 1) {
                            \App\Models\Question::insert($batch);
                            $this->command?->info("  ✓ Batch saved (" . count($batch) . " questions)");
                            $batch = [];
                        }
                    } catch (\Exception $e) {
                        $this->command?->warn("  Translation failed for question: {$question['title']} - {$e->getMessage()}");
                        continue;
                    }
                } catch (\Exception $e) {
                    $this->command?->error("Error processing question: " . $e->getMessage());
                    $this->command?->error("  Stack trace: " . $e->getTraceAsString());
                }
            }

            $this->command?->info("\n✅ Processing complete!");
            $this->command?->info("  ✓ Processed: {$processedCount} questions");
            $this->command?->info("  ✓ Skipped: {$skippedCount} existing questions");

            return true;
        } catch (\Exception $e) {
            $this->command?->error("❌ Error: " . $e->getMessage());
            return false;
        }
    }

    private function fetchQuestionDetails($titleSlug)
    {
        try {
            $response = Http::get("{$this->baseUrl}/select", [
                'titleSlug' => $titleSlug
            ]);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function fetchSolution($titleSlug)
    {
        try {
            $this->command?->info("  Requesting solution for: {$titleSlug}");

            $url = "http://127.0.0.1:3000/officialSolution?titleSlug=" . urlencode($titleSlug);
            $response = Http::get($url);

            if (!$response->successful()) {
                $this->command?->error("  API Error: HTTP " . $response->status());
                return null;
            }

            $data = $response->json();

            if (
                !isset($data['data']['question']['solution']['content']) ||
                $data['data']['question']['solution']['content'] === null
            ) {
                $this->command?->warn("  No solution content found");
                return null;
            }

            $content = $data['data']['question']['solution']['content'];

            // Extract the first playground URL
            if (preg_match('/https:\/\/leetcode\.com\/playground\/([^\/]+)\/shared/', $content, $matches)) {
                $playgroundUrl = $matches[0];
                $this->command?->info("  Found playground URL: " . $playgroundUrl);

                // Get the playground content
                $playgroundResponse = Http::get($playgroundUrl);

                if ($playgroundResponse->successful()) {
                    $html = $playgroundResponse->body();

                    // Look for Python-specific indicators in the response
                    if (strpos($html, 'Python') !== false || strpos($html, 'python') !== false) {
                        // Extract code between Python markers or class definition
                        if (
                            preg_match('/class Solution[:\s].*?(?=class|$)/s', $html, $matches) ||
                            preg_match('/```python\n(.*?)\n```/s', $html, $matches)
                        ) {

                            $code = trim($matches[0]);
                            if (!empty($code)) {
                                $this->command?->info("  ✓ Successfully got Python solution");
                                return $code;
                            }
                        } else {
                            $this->command?->warn("  Found playground but no Python solution");
                        }
                    } else {
                        $this->command?->warn("  Playground does not contain Python code");
                    }
                }
            }

            $this->command?->warn("  No valid Python solution found in content");
            return null;
        } catch (\Exception $e) {
            $this->command?->error("  Solution fetch error: " . $e->getMessage());
            return null;
        }
    }

    private function generateTestCases()
    {
        return [
            [
                'input' => 'example_input',
                'expected' => 'expected_output'
            ]
        ];
    }

    private function generatePythonCode($question)
    {
        $functionName = $this->generateFunctionName($question['titleSlug']);
        $params = $this->inferParameters($question['title']);

        return "def {$functionName}({$params}):\n    # Write your solution here\n    pass";
    }

    private function generateFunctionName($titleSlug)
    {
        return str_replace('-', '_', $titleSlug);
    }

    private function mapDifficulty($leetcodeDifficulty)
    {
        return match (strtolower($leetcodeDifficulty)) {
            'easy' => 1,
            'medium' => 3,
            'hard' => 5,
            default => 1
        };
    }

    private function mapDifficultyReverse($ourDifficulty)
    {
        return match ($ourDifficulty) {
            1, 2 => 'Easy',
            3 => 'Medium',
            4, 5 => 'Hard',
            default => 'Easy'
        };
    }

    private function inferParameters($title)
    {
        $title = strtolower($title);
        if (str_contains($title, 'array') || str_contains($title, 'numbers')) {
            return 'nums';
        } elseif (str_contains($title, 'string')) {
            return 's';
        } else {
            return 'n';
        }
    }

    private function isRateLimited()
    {
        try {
            $response = Http::get("{$this->baseUrl}/problems");
            return $response->status() === 429;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function testApiConnection()
    {
        try {
            $response = Http::get("{$this->baseUrl}/problems");
            $this->command?->info("API Connection Test: " . ($response->successful() ? "✅ Success" : "❌ Failed"));
            return $response->successful();
        } catch (\Exception $e) {
            $this->command?->error("API Connection Error: " . $e->getMessage());
            return false;
        }
    }

    public function testSolutionFetch()
    {
        $titleSlug = 'two-sum';  // Test with the first question
        $this->command?->info("Testing solution fetch for: {$titleSlug}");

        try {
            $response = Http::get("{$this->baseUrl}/officialSolution", [
                'titleSlug' => $titleSlug
            ]);

            $this->command?->info("Response status: " . $response->status());
            $this->command?->info("Response body: " . $response->body());

            return $response->json();
        } catch (\Exception $e) {
            $this->command?->error("Test failed: " . $e->getMessage());
            return null;
        }
    }

    private function checkSolution($titleSlug)
    {
        try {
            $response = Http::get("http://127.0.0.1:3000/officialSolution", [
                'titleSlug' => $titleSlug
            ]);

            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();

            // Check if solution exists and has content
            if (
                !isset($data['data']['question']['solution']['content']) ||
                $data['data']['question']['solution']['content'] === null
            ) {
                return false;
            }

            $content = $data['data']['question']['solution']['content'];

            // Check if there's a playground URL
            return preg_match('/https:\/\/leetcode\.com\/playground\/([^\/]+)\/shared/', $content) === 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function testPlaygroundExtraction()
    {
        $this->command?->info("\n🧪 Testing playground extraction");

        $playgroundId = "U4oRxsP8";

        try {
            // Add https:// to the URL
            $url = "https://leetcode.com/playground/" . urlencode($playgroundId) . "/shared";
            $this->command?->info("Requesting: " . $url);

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => 'https://leetcode.com/',
                'Origin' => 'https://leetcode.com'
            ])->get($url);

            if (!$response->successful()) {
                $this->command?->error("❌ Failed to fetch playground: HTTP " . $response->status());
                $this->command?->error("Response: " . $response->body());
                return;
            }

            $html = $response->body();
            $this->command?->info("✅ Got playground content");

            // Try to find the Python code
            if (preg_match('/<div class="CodeMirror-code">(.*?)<\/div>/s', $html, $matches)) {
                $this->command?->info("\n📝 Found code content:");
                $code = strip_tags($matches[1]);
                $code = html_entity_decode($code);
                $this->command?->info($code);
            }
        } catch (\Exception $e) {
            $this->command?->error("❌ Test failed: " . $e->getMessage());
            $this->command?->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
