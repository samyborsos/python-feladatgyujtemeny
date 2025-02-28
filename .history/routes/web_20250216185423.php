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

use Illuminate\Support\Facades\Http;

Route::get('/leetcode', function () {
    // Set the API URL
    $url = 'https://alfa-leetcode-api.onrender.com/problems';

    // Make the HTTP GET request
    $response = Http::get($url);

    // Check if the request was successful
    if ($response->successful()) {
        $data = $response->json();
        
        // Transform the data to match the specified structure
        $formattedResponse = [
            'totalQuestions' => $data['total'],
            'count' => count($data['problemsetQuestionList']),
            'problemsetQuestionList' => array_map(function($question) {
                return [
                    'acRate' => $question['acRate'],
                    'difficulty' => $question['difficulty'],
                    'freqBar' => $question['freqBar'],
                    'questionFrontendId' => $question['questionFrontendId'],
                    'isFavor' => $question['isFavor'],
                    'isPaidOnly' => $question['isPaidOnly'],
                    'status' => $question['status'],
                    'title' => $question['title'],
                    'titleSlug' => $question['titleSlug'],
                    'topicTags' => array_map(function($tag) {
                        return [
                            'name' => $tag['name'],
                            'id' => $tag['id'],
                            'slug' => $tag['slug'],
                        ];
                    }, $question['topicTags']),
                    'hasSolution' => $question['hasSolution'],
                    'hasVideoSolution' => $question['hasVideoSolution'],
                ];
            }, $data['problemsetQuestionList']),
        ];

        // Return the formatted JSON data
        return response()->json($formattedResponse);
    } else {
        return response()->json(['message' => 'Failed to fetch data from API.'], 500);
    }
});
