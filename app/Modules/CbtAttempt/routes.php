<?php

use App\Modules\CbtAttempt\Controllers\Student\CbtAttemptController;
use Illuminate\Support\Facades\Route;

Route::get('/cbt/start/{schedule}', [CbtAttemptController::class, 'start'])
    ->name('cbt.start');
Route::get('/cbt/attempt/{attempt:uuid}', [CbtAttemptController::class, 'show'])
    ->name('cbt.attempt.show');
Route::post('/cbt/autosave', [CbtAttemptController::class, 'autosave'])
    ->middleware('throttle:60,1')
    ->name('cbt.autosave');
Route::post('/cbt/flag', [CbtAttemptController::class, 'flag'])
    ->middleware('throttle:60,1')
    ->name('cbt.flag');
Route::post('/cbt/submit/{attempt:uuid}', [CbtAttemptController::class, 'submit'])
    ->name('cbt.submit');
Route::post('/cbt/heartbeat/{attempt:uuid}', [CbtAttemptController::class, 'heartbeat'])
    ->middleware('throttle:30,1')
    ->name('cbt.heartbeat');
