<?php

namespace App\Modules\ExamSchedule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamSchedule\Services\ExamScheduleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentExamScheduleController extends Controller
{
    public function __construct(private readonly ExamScheduleService $service)
    {
    }

    public function show(Request $request): Response
    {
        return Inertia::render('Student/ExamSchedule/Show', [
            'schedules' => $this->service->studentSchedules($request->user()),
        ]);
    }
}
