<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CodeWarsService
{
    private $baseUrl = 'https://www.codewars.com/api/v1';
    private $username = 'g964'; // Known active user
    private $command;
    private $translator;

    public function __construct(?Command $command = null)
    {
        $this->command = $command;
        $this->translator = new GoogleTranslate();
        $this->translator->setSource('en');
        $this->translator->setTarget('hu');
    }

    private function log($message, $type = 'info')
    {
        if ($this->command) {
            $this->command->$type($message);
        }
        Log::$type($message);
    }

    public function fetchQuestions($difficulty)
    {
        try {
            // Get existing question titles
            $existingTitles = \App\Models\Question::pluck('title_en')->toArray();
            $this->log("📊 Found " . count($existingTitles) . " existing questions");

            $completedChallenges = collect($this->fetchUserCompletedChallenges()['data'])
                ->take(300); // Increased from 20 to 100 questions

            $this->log("Found " . $completedChallenges->count() . " challenges to test");

            $transformed = $completedChallenges
                ->map(function ($challenge) use ($existingTitles) {
                    try {
                        $details = $this->fetchChallengeDetails($challenge['id']);

                        // Skip if question already exists
                        if (in_array($details['name'], $existingTitles)) {
                            $this->log("⏭️ Skipping existing question: {$details['name']}");
                            return null;
                        }

                        $kyu = abs($details['rank']['id']);

                        $difficulty = match ($kyu) {
                            8, 7 => 1,
                            6, 5 => 2,
                            4 => 3,
                            3, 2 => 4,
                            1 => 5,
                            default => 0
                        };

                        $cleanDescription = $this->cleanDescription($details['description']);

                        // Translate title and description
                        $title_hu = $this->translator->translate($details['name']);
                        $description_hu = $this->translator->translate($cleanDescription);

                        $this->log("Question: {$details['name']} | Kyu: {$kyu} | Our difficulty: {$difficulty}");

                        return [
                            'title_en' => $details['name'],
                            'title_hu' => $title_hu,
                            'description_en' => $cleanDescription,
                            'description_hu' => $description_hu,
                            'initial_code' => $this->generatePythonCode($details),
                            'difficulty' => $difficulty,
                            'test_cases' => $this->generateTestCases(),
                            'source' => 'CodeWars API'
                        ];
                    } catch (\Exception $e) {
                        $this->log("❌ Error processing challenge: " . $e->getMessage());
                        return null;
                    }
                })
                ->filter()  // Remove any null entries from failed processing or skipped questions
                ->filter(fn($q) => $q['difficulty'] == $difficulty)
                ->values()
                ->toArray();

            $this->log("✅ Found " . count($transformed) . " new questions matching difficulty {$difficulty}");
            return $transformed;
        } catch (\Exception $e) {
            $this->log("❌ Error: " . $e->getMessage(), 'error');
            return [];
        }
    }

    private function fetchUserCompletedChallenges()
    {
        $cacheKey = "codewars_user_{$this->username}_challenges";

        return Cache::remember($cacheKey, now()->addDays(7), function () {
            $this->log("    📡 Making API request to fetch user challenges");
            $response = Http::get("{$this->baseUrl}/users/{$this->username}/code-challenges/completed");

            if (!$response->successful()) {
                $this->log("    ❌ API request failed: " . $response->status(), 'error');
                return ['data' => []];
            }

            $this->log("    ✅ Successfully fetched user challenges");
            return $response->json();
        });
    }

    private function fetchChallengeDetails($id)
    {
        $cacheKey = "codewars_challenge_{$id}";

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($id) {
            $this->log("    📡 Fetching details for challenge: {$id}");

            // Add delay to respect rate limiting
            sleep(1);

            $response = Http::get("{$this->baseUrl}/code-challenges/{$id}");

            if (!$response->successful()) {
                $this->log("    ❌ Failed to fetch challenge details: {$id}", 'error');
                return [];
            }

            $this->log("    ✅ Successfully fetched challenge details: {$id}");
            return $response->json();
        });
    }

    private function cleanDescription($description)
    {
        // Clean up the description text
        $cleaned = html_entity_decode($description);
        $cleaned = strip_tags($cleaned);
        $cleaned = str_replace(['\\n', '\\t', '  '], ["\n", "\t", ' '], $cleaned);

        return trim($cleaned);
    }

    private function generatePythonCode($details)
    {
        $name = $details['name'] ?? 'solution';
        $functionName = strtolower(str_replace(' ', '_', $name));
        $functionName = preg_replace('/[^a-z0-9_]/', '', $functionName);
        return "def {$functionName}(args):\n    # Write your solution here\n    pass";
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
}
