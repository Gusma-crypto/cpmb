<?php

namespace App\Modules\ExamCard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamCard\Services\ExamCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentExamCardController extends Controller
{
    public function __construct(private readonly ExamCardService $service)
    {
    }

    public function index(Request $request): Response
    {
        $this->service->ensureStudentCards($request->user()->id);

        return Inertia::render('Student/ExamCard/Index', [
            'cards' => $this->service->studentCards($request->user()->id),
        ]);
    }
}
