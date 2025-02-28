<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/questions', [QuestionController::class, 'index'])->name('questions.index');
Route::get('/questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
Route::post('/questions/{question}/verify', [QuestionController::class, 'verify'])->name('questions.verify');

Route::get('/leetcode', function () {
    
    return view('welcome');
});
