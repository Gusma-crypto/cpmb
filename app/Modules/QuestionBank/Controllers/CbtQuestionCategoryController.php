<?php

namespace App\Modules\QuestionBank\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\QuestionBank\Models\CbtQuestionCategory;
use App\Modules\QuestionBank\Requests\StoreCbtQuestionCategoryRequest;
use App\Modules\QuestionBank\Requests\UpdateCbtQuestionCategoryRequest;
use App\Modules\QuestionBank\Services\CbtQuestionCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CbtQuestionCategoryController extends Controller
{
    public function __construct(private readonly CbtQuestionCategoryService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/QuestionCategories/Index', [
            'categories' => $this->service->paginate($request->only('search')),
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/QuestionCategories/Create');
    }

    public function store(StoreCbtQuestionCategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()
            ->route('admin.question-categories.index')
            ->with('success', 'Kategori soal berhasil ditambahkan.');
    }

    public function edit(CbtQuestionCategory $questionCategory): Response
    {
        return Inertia::render('Admin/QuestionCategories/Edit', [
            'category' => $questionCategory,
        ]);
    }

    public function update(UpdateCbtQuestionCategoryRequest $request, CbtQuestionCategory $questionCategory): RedirectResponse
    {
        $this->service->update($questionCategory, $request->validated());

        return redirect()
            ->route('admin.question-categories.index')
            ->with('success', 'Kategori soal berhasil diperbarui.');
    }

    public function destroy(CbtQuestionCategory $questionCategory): RedirectResponse
    {
        $this->service->delete($questionCategory);

        return redirect()
            ->route('admin.question-categories.index')
            ->with('success', 'Kategori soal berhasil dihapus.');
    }
}
