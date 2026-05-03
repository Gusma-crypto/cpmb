<?php

namespace App\Modules\ExamRoom\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamRoom\Services\ExamRoomAssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentExamRoomController extends Controller
{
    public function __construct(private readonly ExamRoomAssignmentService $service)
    {
    }

    public function show(Request $request): Response
    {
        return Inertia::render('Student/ExamRoom/Show', [
            'assignments' => $this->service->studentAssignments($request->user()->id),
        ]);
    }
}
