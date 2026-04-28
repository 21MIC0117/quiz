<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QuizController::class, 'index'])->name('home');

Route::resource('quizzes', QuizController::class)->except(['edit', 'update']);

Route::get('quizzes/{quiz}/questions/create', [QuestionController::class, 'create'])->name('questions.create');
Route::post('quizzes/{quiz}/questions', [QuestionController::class, 'store'])->name('questions.store');
Route::delete('quizzes/{quiz}/questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

Route::get('quizzes/{quiz}/attempt', [AttemptController::class, 'take'])->name('attempts.take');
Route::post('quizzes/{quiz}/attempt', [AttemptController::class, 'submit'])->name('attempts.submit');
Route::get('attempts/{attempt}', [AttemptController::class, 'show'])->name('attempts.show');
