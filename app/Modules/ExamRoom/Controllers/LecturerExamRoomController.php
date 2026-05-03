<?php

namespace App\Modules\ExamRoom\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamRoom\Services\ExamRoomAssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LecturerExamRoomController extends Controller
{
    public function __construct(private readonly ExamRoomAssignmentService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Lecturer/ExamRooms/Index', [
            'assignments' => $this->service->lecturerAssignments($request->user()->id),
        ]);
    }
}
