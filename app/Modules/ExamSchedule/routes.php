<?php

use App\Modules\ExamSchedule\Controllers\ExamScheduleController;
use Illuminate\Support\Facades\Route;

Route::resource('exam-schedules', ExamScheduleController::class)
    ->parameters(['exam-schedules' => 'examSchedule']);
