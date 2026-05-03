<?php

namespace App\Modules\CbtResult\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\CbtResult\Services\CbtResultService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentCbtResultController extends Controller
{
    public function __construct(private readonly CbtResultService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Student/Cbt/Results', [
            'results' => $this->service->studentResults($request->user()),
        ]);
    }
}
