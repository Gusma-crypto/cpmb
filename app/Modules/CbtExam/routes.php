<?php

use App\Modules\CbtExam\Controllers\Admin\CbtExamController;
use App\Modules\CbtExam\Controllers\Admin\CbtExamQuestionController;
use Illuminate\Support\Facades\Route;

Route::prefix('cbt')->name('cbt.')->group(function (): void {
    Route::resource('exams', CbtExamController::class)
        ->except('show')
        ->parameters(['exams' => 'exam']);

    Route::get('exams/{exam}/questions', [CbtExamQuestionController::class, 'edit'])
        ->name('exams.questions.edit');
    Route::put('exams/{exam}/questions', [CbtExamQuestionController::class, 'update'])
        ->name('exams.questions.update');
    Route::post('exams/{exam}/publish', [CbtExamController::class, 'publish'])
        ->name('exams.publish');
    Route::post('exams/{exam}/close', [CbtExamController::class, 'close'])
        ->name('exams.close');
});
