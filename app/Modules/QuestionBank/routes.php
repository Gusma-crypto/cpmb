<?php

use App\Modules\QuestionBank\Controllers\CbtQuestionCategoryController;
use App\Modules\QuestionBank\Controllers\CbtQuestionController;
use Illuminate\Support\Facades\Route;

Route::resource('question-categories', CbtQuestionCategoryController::class)
    ->except('show')
    ->parameters(['question-categories' => 'questionCategory']);

Route::resource('questions', CbtQuestionController::class)
    ->parameters(['questions' => 'question']);
