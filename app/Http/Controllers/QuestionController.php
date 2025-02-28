<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        // Cache the total count
        $count = Cache::remember('questions_count', 3600, function () {
            return Question::count();
        });


        // Eager load any relationships you might need
        $query = Question::query()
            ->select(['id', 'title_hu', 'difficulty', 'source',])  // Select only needed fields
            ->latest();

        // Apply sorting
        match ($request->input('sort', 'newest')) {
            'newest' => $query->latest(),
            'oldest' => $query->oldest(),
            'difficulty-asc' => $query->orderBy('difficulty', 'asc'),
            'difficulty-desc' => $query->orderBy('difficulty', 'desc'),
            default => $query->latest()
        };

        // Cache the paginated results
        $cacheKey = 'questions_page_' . $request->get('page', 1) . '_' . $request->get('sort', 'newest');
        $questions = Cache::remember($cacheKey, 3600, function () use ($query) {
            return $query->paginate(30);
        });

        return view('questions.index', [
            'questions' => $questions,
            'count' => $count
        ]);
    }

    public function show(Question $question)
    {
        return view('questions.show', compact('question'));
    }

    public function verify(Request $request, Question $question)
    {
        $code = $request->input('code');

        // Here you would implement Python code execution and testing
        // For security, this should be done in a sandboxed environment
        // You might want to use Docker or a similar solution

        $results = [];
        foreach ($question->test_cases as $test) {
            // This is where you'd actually run the Python code
            // For now, we'll just return a mock result
            $results[] = [
                'input' => $test['input'],
                'expected' => $test['expected'],
                'actual' => null,
                'passed' => false
            ];
        }

        return response()->json(['results' => $results]);
    }
}
