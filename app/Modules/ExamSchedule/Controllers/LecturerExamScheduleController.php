<?php

namespace App\Modules\ExamSchedule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamSchedule\Services\ExamScheduleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LecturerExamScheduleController extends Controller
{
    public function __construct(private readonly ExamScheduleService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Lecturer/ExamSchedules/Index', [
            'schedules' => $this->service->supervisorSchedules($request->user()),
        ]);
    }
}
