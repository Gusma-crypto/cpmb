<?php

namespace App\Modules\ExamSchedule\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use App\Modules\ExamSchedule\Requests\StoreExamScheduleRequest;
use App\Modules\ExamSchedule\Requests\UpdateExamScheduleRequest;
use App\Modules\ExamSchedule\Services\ExamScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamScheduleController extends Controller
{
    public function __construct(private readonly ExamScheduleService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/ExamSchedules/Index', [
            'schedules' => $this->service->paginate($request->only(['search', 'status', 'exam_type'])),
            'filters' => $request->only(['search', 'status', 'exam_type']),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/ExamSchedules/Create', [
            ...$this->formData(),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function store(StoreExamScheduleRequest $request): RedirectResponse
    {
        $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route($this->routePrefix($request) . '.exam-schedules.index')
            ->with('success', 'Jadwal ujian berhasil ditambahkan.');
    }

    public function show(Request $request, ExamSchedule $examSchedule): Response
    {
        return Inertia::render('Admin/ExamSchedules/Show', [
            'schedule' => $examSchedule->load(['creator', 'updater']),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function edit(Request $request, ExamSchedule $examSchedule): Response
    {
        return Inertia::render('Admin/ExamSchedules/Edit', [
            'schedule' => $examSchedule,
            ...$this->formData(),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function update(UpdateExamScheduleRequest $request, ExamSchedule $examSchedule): RedirectResponse
    {
        $this->service->update($request->user(), $examSchedule, $request->validated());

        return redirect()
            ->route($this->routePrefix($request) . '.exam-schedules.index')
            ->with('success', 'Jadwal ujian berhasil diperbarui.');
    }

    public function destroy(Request $request, ExamSchedule $examSchedule): RedirectResponse
    {
        $this->service->delete($examSchedule);

        return redirect()
            ->route($this->routePrefix($request) . '.exam-schedules.index')
            ->with('success', 'Jadwal ujian berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'timeSlots' => $this->service->timeSlots(),
            'cbtExams' => $this->service->cbtExams(),
            'examTypes' => ExamSchedule::EXAM_TYPES,
            'statuses' => ExamSchedule::STATUSES,
        ];
    }

    private function routePrefix(Request $request): string
    {
        return str_starts_with((string) $request->route()?->getName(), 'staff.') ? 'staff' : 'admin';
    }
}
