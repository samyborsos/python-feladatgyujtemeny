<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\AIService;
use Illuminate\Console\Command;

class GenerateSolutions extends Command
{
    protected $signature = 'solutions:generate {--limit=5} {--unsolved-only}';
    protected $description = 'Generate AI solutions for questions';

    private $aiService;

    public function __construct(AIService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    public function handle()
    {
        $query = Question::query();

        if ($this->option('unsolved-only')) {
            $query->whereNull('solution');
        }

        $questions = $query->take($this->option('limit'))->get();

        if ($questions->isEmpty()) {
            $this->error('No questions found to process!');
            return;
        }

        $this->info('====================================');
        $this->info('Starting to process ' . count($questions) . ' questions...');
        $this->info('====================================');

        foreach ($questions as $index => $question) {
            $this->info("\n[" . ($index + 1) . "/" . count($questions) . "] Processing question:");
            $this->line("Title: " . $question->title_hu);
/*             $this->line("ID: " . $question->id);
            $this->line("Source: " . $question->source); */

            try {
                $this->info("\n→ Generating solution...");

                // Set a timeout for the API call
                set_time_limit(300); // 5 minutes per question

                $solution = $this->aiService->generateSolution(
                    $question->title_hu,
                    $question->description_hu
                );

                if (empty($solution)) {
                    throw new \Exception('Empty solution received');
                }

                $question->update(['solution' => $solution]);
                $this->info('✓ Solution generated successfully');
                $this->line('----------------------------------------');
            } catch (\Exception $e) {
                $this->error('✗ Failed: ' . $e->getMessage());
                $this->line('----------------------------------------');
            }

            // Add a small delay between requests to avoid rate limiting
            if ($index < count($questions) - 1) {
                $this->info("Waiting 2 seconds before next question...");
                sleep(2);
            }
        }

        $this->info("\n====================================");
        $this->info('Solution generation process completed!');
        $this->info("====================================\n");
    }
}
