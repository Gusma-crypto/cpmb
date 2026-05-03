<?php

namespace App\Modules\QuestionBank\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\QuestionBank\Models\CbtQuestion;
use App\Modules\QuestionBank\Requests\StoreCbtQuestionRequest;
use App\Modules\QuestionBank\Requests\UpdateCbtQuestionRequest;
use App\Modules\QuestionBank\Services\CbtQuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CbtQuestionController extends Controller
{
    public function __construct(private readonly CbtQuestionService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Questions/Index', [
            'questions' => $this->service->paginate($request->only(['search', 'category_id', 'type'])),
            'filters' => $request->only(['search', 'category_id', 'type']),
            ...$this->service->formData(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Questions/Create', [
            ...$this->service->formData(),
        ]);
    }

    public function store(StoreCbtQuestionRequest $request): RedirectResponse
    {
        $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Soal berhasil ditambahkan.');
    }

    public function show(CbtQuestion $question): Response
    {
        return Inertia::render('Admin/Questions/Show', [
            'question' => $question->load(['category', 'creator', 'options']),
        ]);
    }

    public function edit(CbtQuestion $question): Response
    {
        return Inertia::render('Admin/Questions/Edit', [
            'question' => $question->load('options'),
            ...$this->service->formData(),
        ]);
    }

    public function update(UpdateCbtQuestionRequest $request, CbtQuestion $question): RedirectResponse
    {
        $this->service->update($question, $request->validated());

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(CbtQuestion $question): RedirectResponse
    {
        $this->service->delete($question);

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Soal berhasil dihapus.');
    }
}
