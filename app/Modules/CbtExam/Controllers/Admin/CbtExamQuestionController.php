<?php

namespace App\Modules\CbtExam\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\CbtExam\Models\CbtExam;
use App\Modules\CbtExam\Requests\SyncCbtExamQuestionsRequest;
use App\Modules\CbtExam\Services\CbtExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CbtExamQuestionController extends Controller
{
    public function __construct(private readonly CbtExamService $service)
    {
    }

    public function edit(Request $request, CbtExam $exam): Response
    {
        $exam->load(['questions.category']);

        return Inertia::render('Admin/Cbt/Exams/Questions', [
            'exam' => $exam,
            'selectedQuestions' => $exam->questions,
            'questionBank' => $this->service->questionBank($request->only(['search', 'category_id', 'difficulty', 'type'])),
            'filters' => $request->only(['search', 'category_id', 'difficulty', 'type']),
            ...$this->service->questionFormData(),
        ]);
    }

    public function update(SyncCbtExamQuestionsRequest $request, CbtExam $exam): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->syncQuestions($exam, $validated['questions'] ?? []);

        return back()->with('success', 'Soal paket ujian CBT berhasil disimpan.');
    }
}
