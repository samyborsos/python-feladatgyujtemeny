<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

class VerifyQuestions extends Command
{
    protected $signature = 'questions:verify';
    protected $description = 'Verify questions have correct English content';

    public function handle()
    {
        $this->info('🔍 Verifying questions...');

        $questions = Question::all();

        $stats = [
            'total' => $questions->count(),
            'with_title_en' => 0,
            'with_desc_en' => 0,
            'missing_title' => [],
            'missing_desc' => []
        ];

        foreach ($questions as $question) {
            if (!empty($question->title_en)) {
                $stats['with_title_en']++;
            } else {
                $stats['missing_title'][] = $question->id;
            }

            if (!empty($question->description_en)) {
                $stats['with_desc_en']++;
            } else {
                $stats['missing_desc'][] = $question->id;
            }
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Questions', $stats['total']],
                ['With English Title', $stats['with_title_en']],
                ['With English Description', $stats['with_desc_en']]
            ]
        );

        if (count($stats['missing_title']) > 0) {
            $this->warn('Questions missing English title: ' . implode(', ', $stats['missing_title']));
        }

        if (count($stats['missing_desc']) > 0) {
            $this->warn('Questions missing English description: ' . implode(', ', $stats['missing_desc']));
        }

        if ($stats['with_title_en'] === $stats['total'] && $stats['with_desc_en'] === $stats['total']) {
            $this->info('✅ All questions have English content!');
        } else {
            $this->error('❌ Some questions are missing English content!');
        }
    }
}
