<?php

namespace App\Console\Commands;

use App\Models\Question;
use Illuminate\Console\Command;

class FixQuestionDescriptions extends Command
{
    protected $signature = 'questions:fix-descriptions';
    protected $description = 'Move English descriptions from description_hu to description_en';

    public function handle()
    {
        $questions = Question::whereNotNull('description_hu')
            ->whereNull('description_en')
            ->get();

        $this->info('Found ' . $questions->count() . ' questions to fix.');

        foreach ($questions as $question) {
            $question->update([
                'description_en' => $question->description_hu,
                'description_hu' => "null"
            ]);
        }



        $questions = Question::whereNotNull('title_hu')
            ->whereNull('title_en')
            ->get();

        $this->info('Found ' . $questions->count() . ' questions to fix.');

        foreach ($questions as $question) {
            $question->update([
                'title_en' => $question->title_hu,
                'title_hu' => "null"
            ]);
        }




        $questions = Question::whereNotNull('title_hu')
            ->get();

        $this->info('Found ' . $questions->count() . ' questions to fix.');

        foreach ($questions as $question) {
            $question->update([
                'title_hu' => "null"
            ]);
        }

        $questions = Question::whereNotNull('description_hu')
            ->get();

        $this->info('Found ' . $questions->count() . ' questions to fix.');

        foreach ($questions as $question) {
            $question->update([
                'description_hu' => "null"
            ]);
        }

        $this->info('Fixed all questions!');
    }
}
