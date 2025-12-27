<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/questions', [QuestionController::class, 'index']);
Route::post('/answers', [AnswerController::class, 'store']);
Route::get('/answers', [AnswerController::class, 'index']);
Route::get('/answers/{id}', [AnswerController::class, 'show']);
Route::get('/statistics', [StatisticsController::class, 'index']);

