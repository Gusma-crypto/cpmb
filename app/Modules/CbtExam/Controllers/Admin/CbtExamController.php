<?php

namespace App\Modules\CbtExam\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\CbtExam\Models\CbtExam;
use App\Modules\CbtExam\Requests\StoreCbtExamRequest;
use App\Modules\CbtExam\Requests\UpdateCbtExamRequest;
use App\Modules\CbtExam\Services\CbtExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CbtExamController extends Controller
{
    public function __construct(private readonly CbtExamService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Cbt/Exams/Index', [
            'exams' => $this->service->list($request->only(['search', 'status', 'program_id'])),
            'filters' => $request->only(['search', 'status', 'program_id']),
            ...$this->service->formData(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Cbt/Exams/Create', [
            ...$this->service->formData(),
        ]);
    }

    public function store(StoreCbtExamRequest $request): RedirectResponse
    {
        $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.cbt.exams.index')
            ->with('success', 'Paket ujian CBT berhasil ditambahkan.');
    }

    public function edit(CbtExam $exam): Response
    {
        return Inertia::render('Admin/Cbt/Exams/Edit', [
            'exam' => $exam->load(['academicYear', 'program']),
            ...$this->service->formData(),
        ]);
    }

    public function update(UpdateCbtExamRequest $request, CbtExam $exam): RedirectResponse
    {
        $this->service->update($exam, $request->validated());

        return redirect()
            ->route('admin.cbt.exams.index')
            ->with('success', 'Paket ujian CBT berhasil diperbarui.');
    }

    public function destroy(CbtExam $exam): RedirectResponse
    {
        $this->service->delete($exam);

        return redirect()
            ->route('admin.cbt.exams.index')
            ->with('success', 'Paket ujian CBT berhasil dihapus.');
    }

    public function publish(CbtExam $exam): RedirectResponse
    {
        $this->service->publish($exam);

        return back()->with('success', 'Paket ujian CBT berhasil dipublish.');
    }

    public function close(CbtExam $exam): RedirectResponse
    {
        $this->service->close($exam);

        return back()->with('success', 'Paket ujian CBT berhasil ditutup.');
    }
}
