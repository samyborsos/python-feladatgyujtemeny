<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Log;

class TranslateAllQuestions extends Command
{
    protected $signature = 'questions:translate-all {--debug : Show debug information} {--force : Force retranslation of all questions}';
    protected $description = 'Translate all questions and titles from English to Hungarian';

    private function debug($message, $data = null)
    {
        if ($this->option('debug')) {
            $this->info("🔍 DEBUG: $message");
            if ($data) {
                $this->line(json_encode($data, JSON_PRETTY_PRINT));
            }
            Log::debug($message, $data ?? []);
        }
    }

    public function handle()
    {
        $this->info('🚀 Starting translation process...');

        // Get questions based on force flag
        $questions = $this->option('force')
            ? Question::whereNotNull('description_en')->get()
            : Question::whereNotNull('description_en')
            ->where(function ($query) {
                $query->whereNull('description_hu')
                    ->orWhereNull('title_hu');
            })->get();

        $this->info('📚 Found ' . $questions->count() . ' questions to process.');
        $this->debug('Query parameters', [
            'force' => $this->option('force'),
            'total_questions' => $questions->count()
        ]);

        // Initialize counters
        $stats = [
            'titles_translated' => 0,
            'descriptions_translated' => 0,
            'errors' => 0,
        ];

        $translator = new GoogleTranslate();
        $translator->setSource('en');
        $translator->setTarget('hu');

        $bar = $this->output->createProgressBar($questions->count());
        $bar->setFormat('progress');

        foreach ($questions as $question) {
            try {
                $this->debug("Processing question ID: {$question->id}", [
                    'title_en' => $question->title_en,
                    'has_description' => !empty($question->description_en)
                ]);

                // Translate title if needed
                if ((empty($question->title_hu) || $this->option('force')) && !empty($question->title_en)) {
                    $this->debug("Translating title", ['original' => $question->title_en]);
                    $translatedTitle = $translator->translate($question->title_en);
                    $question->title_hu = $translatedTitle;
                    $stats['titles_translated']++;
                    $this->debug("Title translated", ['translated' => $translatedTitle]);
                }

                // Translate description if needed
                if ((empty($question->description_hu) || $this->option('force')) && !empty($question->description_en)) {
                    $this->debug("Translating description", ['original_length' => strlen($question->description_en)]);
                    $translatedDesc = $translator->translate($question->description_en);
                    $question->description_hu = $translatedDesc;
                    $stats['descriptions_translated']++;
                    $this->debug("Description translated", ['translated_length' => strlen($translatedDesc)]);
                }

                $question->save();
                $bar->advance();

                // Add a small delay to avoid rate limiting
                usleep(100000); // 0.1 second delay

            } catch (\Exception $e) {
                $stats['errors']++;
                $this->error("❌ Failed to translate question {$question->id}: {$e->getMessage()}");
                Log::error("Translation failed for question {$question->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Display final statistics
        $this->info('📊 Translation Statistics:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Titles Translated', $stats['titles_translated']],
                ['Descriptions Translated', $stats['descriptions_translated']],
                ['Errors Encountered', $stats['errors']],
                ['Total Questions Processed', $questions->count()]
            ]
        );

        $this->info('✅ Translation process completed!');

        if ($stats['errors'] > 0) {
            $this->warn("⚠️ Some translations failed. Check the logs for details.");
        }
    }
}
