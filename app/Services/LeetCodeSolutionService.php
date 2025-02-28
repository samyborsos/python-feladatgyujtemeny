<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Question;
use DOMDocument;
use DOMXPath;

class LeetCodeSolutionService
{
    private $baseUrl = 'https://alfa-leetcode-api.onrender.com';
    private $command;

    public function __construct($command = null)
    {
        $this->command = $command;
    }

    public function importSolutions()
    {
        $this->log("📚 Starting to import LeetCode solutions...");

        // Get all LeetCode questions from database
        $questions = Question::where('source', 'LeetCode API')->get();
        $this->log("Found {$questions->count()} LeetCode questions");

        foreach ($questions as $question) {
            try {
                $this->log("Processing solution for: {$question->title_en}");

                // Get title slug from stored JSON
                $titleSlug = $this->findTitleSlug($question->title_en);
                if (!$titleSlug) {
                    $this->log("❌ Could not find title slug for: {$question->title_en}", 'error');
                    continue;
                }

                // Fetch solution
                $solution = $this->fetchSolution($titleSlug);
                if (!$solution) {
                    $this->log("❌ No solution found for: {$question->title_en}", 'error');
                    continue;
                }

                // Update question with solution
                $question->update([
                    'solution' => $solution
                ]);

                $this->log("✅ Saved solution for: {$question->title_en}");

                // Respect rate limiting
                sleep(1);
            } catch (\Exception $e) {
                $this->log("❌ Error processing {$question->title_en}: " . $e->getMessage(), 'error');
            }
        }
    }

    private function findTitleSlug($title)
    {
        $json = Storage::get('leetcode/all_leetcode_challenges.json');
        $data = json_decode($json, true);

        foreach ($data['problemsetQuestionList'] as $problem) {
            if ($problem['title'] === $title) {
                return $problem['titleSlug'];
            }
        }

        return null;
    }

    private function fetchSolution($titleSlug)
    {
        $response = Http::get("{$this->baseUrl}/officialSolution", [
            'titleSlug' => $titleSlug
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        $content = $data['data']['question']['solution']['content'] ?? null;

        if (!$content) {
            return null;
        }

        // Extract Python solution from content
        return $this->extractPythonSolution($content);
    }

    private function extractPythonSolution($content)
    {
        // Find the first playground URL
        if (preg_match('/https:\/\/leetcode.com\/playground\/([^\/]+)\/shared/', $content, $matches)) {
            $playgroundId = $matches[1];

            // Fetch playground content
            $response = Http::get("https://leetcode.com/playground/{$playgroundId}/shared");

            if ($response->successful()) {
                // Parse the HTML and extract Python solution
                // This part needs to be implemented based on the actual HTML structure
                return $this->parsePythonSolution($response->body());
            }
        }

        return null;
    }

    private function parsePythonSolution($html)
    {
        // Implementation needed for parsing the Python code from HTML
        // This will depend on the actual HTML structure
    }

    private function log($message, $type = 'info')
    {
        if ($this->command) {
            $this->command->$type($message);
        }
    }
}
