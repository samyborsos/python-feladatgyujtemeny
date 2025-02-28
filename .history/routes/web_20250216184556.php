<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
Route::post('/questions/{question}/verify', [QuestionController::class, 'verify'])->name('questions.verify');

Route::get('/leetcode', function () {
    $request = Request();
    // Set the API URL and parameters
    $url = 'https://alfa-leetcode-api.onrender.com/problems';
    $difficulty = $request->get('difficulty', 'medium');
    $limit = $request->get('limit', 1);

    // Make the HTTP GET request
    $response = Http::get($url, [
        'tags' => $difficulty,
        'limit' => $limit,
    ]);

    // Check if the request was successful
    if ($response->successful()) {
        $data = $response->json();
        $questions = $data['problemsetQuestionList'];

        // Print the details of the first question (for demonstration purposes)
        if (!empty($questions)) {
            $question = $questions[0];
            return response()->json([
                'Title' => $question['title'],
                'Difficulty' => $question['difficulty'],
                'Tags' => array_column($question['topicTags'], 'name'),
                'URL' => "https://leetcode.com/problems/{$question['titleSlug']}",
            ]);
        } else {
            return response()->json(['message' => 'No questions found.'], 404);
        }
    } else {
        return response()->json(['message' => 'Failed to fetch data from API.'], 500);
    }


    return view('welcome');
});
