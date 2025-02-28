<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\AITranslationService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
Route::get('/questions/search', [QuestionController::class, 'search'])->name('questions.search');
Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');


// Update the test route with debugging
Route::get('/test-translation', function () {
    dump('Translation test started');

    $translator = new AITranslationService();

    $text = "Write a function that takes an array of integers and returns the sum of all positive numbers.
             Example:
             Input: [1, -4, 7, -2, 3]
             Output: 11
             Explanation: The positive numbers are 1, 7, and 3, and their sum is 11.";

    dump('Input text prepared');

    try {
        $translated = $translator->translateDescription($text);
        dump('Translation completed successfully');
    } catch (\Exception $e) {
        dump('Route exception caught:', $e->getMessage());
        $translated = 'Translation failed: ' . $e->getMessage();
    }

    return view('test-translation', [
        'original' => $text,
        'translated' => $translated
    ]);
});
