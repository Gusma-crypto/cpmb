<?php

use App\Modules\ExamCard\Controllers\ExamCardController;
use App\Modules\ExamCard\Controllers\ExamCardPrintController;
use Illuminate\Support\Facades\Route;

Route::resource('exam-cards', ExamCardController::class)
    ->only(['index', 'store', 'show'])
    ->parameters(['exam-cards' => 'examCard']);
Route::post('exam-cards/{examCard}/print', [ExamCardPrintController::class, 'store'])
    ->name('exam-cards.print');
