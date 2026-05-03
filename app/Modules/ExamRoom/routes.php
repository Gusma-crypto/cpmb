<?php

use App\Modules\ExamRoom\Controllers\ExamRoomAssignmentController;
use App\Modules\ExamRoom\Controllers\ExamRoomController;
use Illuminate\Support\Facades\Route;

Route::resource('exam-rooms', ExamRoomController::class)
    ->parameters(['exam-rooms' => 'examRoom']);
Route::resource('exam-room-assignments', ExamRoomAssignmentController::class)
    ->parameters(['exam-room-assignments' => 'examRoomAssignment']);
