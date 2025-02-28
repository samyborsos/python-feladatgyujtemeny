<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AIService
{
    protected $command;

    public function __construct($command = null)
    {
        $this->command = $command;
    }

    public function generateSolution($title, $description)
    {
        try {
            $this->command?->info("→ Generating solution...");

            // Create a more explicit prompt with English function name
            $functionName = strtolower(str_replace(' ', '_', $title));
            $functionName = preg_replace('/[^a-z0-9_]/', '', $functionName);

            $response = Http::timeout(20)
                ->post('http://localhost:11434/api/generate', [
                    'model' => 'tinyllama',
                    'prompt' => "Create a Python function named '{$functionName}' for this problem:
Description: {$description}

STRICT Requirements:
- Function name must be: {$functionName}
- No comments
- No docstrings
- No explanations
- Pure Python 3 code only
- Return the solution

Example format:
def {$functionName}(nums, target):
    return result",
                    'stream' => false,
                    'temperature' => 0.7,
                    'max_tokens' => 300
                ]);

            if ($response->successful()) {
                $solution = $response->json('response');

                if (empty($solution)) {
                    $this->command?->error("✗ Failed: Empty solution received");
                    return null;
                }

                $formattedSolution = $this->formatSolution($solution);
                if ($formattedSolution) {
                    try {
                        // Find the question
                        $question = Question::where('title_hu', $title)->first();

                        if ($question) {
                            // Store old solution for verification
                            $oldSolution = $question->solution;

                            // Update the solution
                            $updated = $question->update(['solution' => $formattedSolution]);

                            // Verify the update
                            $question->refresh();

                            if ($updated && $question->solution === $formattedSolution) {
                                $this->command?->info("✓ Solution successfully updated in database");
                                $this->command?->info("Question ID: " . $question->id);
                                $this->command?->info("Old solution length: " . strlen($oldSolution ?? ''));
                                $this->command?->info("New solution length: " . strlen($question->solution));
                            } else {
                                $this->command?->error("✗ Database update failed verification");
                                $this->command?->error("Please check question ID: " . $question->id);
                            }
                        } else {
                            $this->command?->error("✗ Question not found: " . $title);
                            // Show available titles for debugging
                            $titles = Question::pluck('title_hu')->take(5)->toArray();
                            $this->command?->info("First 5 available titles: " . implode(', ', $titles));
                        }
                    } catch (\Exception $e) {
                        $this->command?->error("✗ Database Error: " . $e->getMessage());
                    }
                }
                return $formattedSolution;
            }

            $this->command?->error("✗ API request failed");
            return null;
        } catch (\Exception $e) {
            $this->command?->error("✗ Error: " . $e->getMessage());
            return null;
        }
    }

    private function retryWithDifferentParams($title, $description)
    {
        try {
            $response = Http::timeout(15)
                ->post('http://localhost:11434/api/generate', [
                    'model' => 'phi', // or try a different model if available
                    'prompt' => $this->getSimplifiedPrompt($title, $description),
                    'stream' => false,
                    'temperature' => 1.0, // Higher temperature for more variety
                    'max_tokens' => 300
                ]);

            if ($response->successful()) {
                return $this->formatSolution($response->json('response'));
            }
        } catch (\Exception $e) {
            $this->command?->error("❌ Retry failed: " . $e->getMessage());
        }
        return null;
    }

    private function getSolutionPrompt($title, $description): string
    {
        $functionName = strtolower(str_replace(' ', '_', $title));

        return "Create a Python solution for this problem:
Problem: {$title}
Details: {$description}
Requirements:
- Use function name: {$functionName}
- Python 3 syntax
- Return solution only
- No comments needed";
    }

    private function getSimplifiedPrompt($title, $description): string
    {
        return "Write a Python function to solve: {$title}. {$description}";
    }

    private function formatSolution($solution)
    {
        if (empty($solution)) return null;

        // Clean up the solution
        $solution = trim($solution);

        // Remove docstrings
        $solution = preg_replace('/""".*?"""/s', '', $solution);

        // Remove comments
        $solution = preg_replace('/#.*$/m', '', $solution);

        // Remove empty lines
        $solution = preg_replace('/^\s*[\r\n]/m', '', $solution);

        // Ensure it's wrapped in code block
        if (!str_starts_with($solution, '```python')) {
            $solution = "```python\n$solution";
        }
        if (!str_ends_with($solution, '```')) {
            $solution .= "\n```";
        }

        return $solution;
    }

    public function generateDescription($title, $originalDescription)
    {
        // Cache key based on title and description
        $cacheKey = 'description_' . md5($title . $originalDescription);

        // Try to get from cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $prompt = "Create a clear, structured description for this programming problem.
Title: {$title}
Original Description: {$originalDescription}

Requirements:
- Start with a clear function definition explanation
- Explain each parameter
- Specify the return value
- Keep it concise but complete
- NO code examples
- NO test cases
- Format MUST be exactly like this example:

Implement a function def function_name(param1, param2) that calculates X.

The first parameter param1 represents A. The second parameter param2 represents B.

The function should return Z, which represents the result of X.";

        try {
            $response = Http::timeout(30)
                ->retry(2, 100)
                ->post('http://localhost:11434/api/generate', [
                    'model' => 'phi',
                    'prompt' => $prompt,
                    'stream' => false,
                    'temperature' => 0.7,
                    'max_tokens' => 300
                ]);

            if ($response->successful()) {
                $description = $response->json('response');

                // Cache the result for 24 hours
                Cache::put($cacheKey, $description, now()->addHours(24));

                return $description;
            }

            $this->command?->error("❌ Failed to generate description: " . $response->body());
            return null;
        } catch (\Exception $e) {
            $this->command?->error("❌ AI Description Generation Error: " . $e->getMessage());
            return null;
        }
    }
}
