<?php

namespace App\Modules\ExamRoom\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamRoom\Models\ExamRoomAssignment;
use App\Modules\ExamRoom\Services\ExamRoomAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ExamRoomAssignmentController extends Controller
{
    public function __construct(private readonly ExamRoomAssignmentService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/ExamRoomAssignments/Index', [
            'assignments' => $this->service->paginate(),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/ExamRoomAssignments/Create', [...$this->service->formData(), 'routePrefix' => $this->routePrefix($request)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->service->create($request->validate($this->rules()));

        return redirect()->route($this->routePrefix($request) . '.exam-room-assignments.index')->with('success', 'Penempatan peserta berhasil dibuat.');
    }

    public function show(Request $request, ExamRoomAssignment $examRoomAssignment): Response
    {
        return Inertia::render('Admin/ExamRoomAssignments/Show', [
            'assignment' => $examRoomAssignment->load(['room', 'schedule', 'supervisor', 'participants.registration.user']),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function edit(Request $request, ExamRoomAssignment $examRoomAssignment): Response
    {
        return Inertia::render('Admin/ExamRoomAssignments/Edit', [
            'assignment' => $examRoomAssignment->load('participants'),
            ...$this->service->formDataForEdit($examRoomAssignment),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function update(Request $request, ExamRoomAssignment $examRoomAssignment): RedirectResponse
    {
        $this->service->update($examRoomAssignment, $request->validate($this->rules()));

        return redirect()->route($this->routePrefix($request) . '.exam-room-assignments.index')->with('success', 'Penempatan peserta berhasil diperbarui.');
    }

    public function destroy(Request $request, ExamRoomAssignment $examRoomAssignment): RedirectResponse
    {
        $examRoomAssignment->delete();

        return redirect()->route($this->routePrefix($request) . '.exam-room-assignments.index')->with('success', 'Penempatan peserta berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'exam_room_id' => ['required', 'exists:exam_rooms,id'],
            'exam_schedule_id' => ['required', 'exists:exam_schedules,id'],
            'supervisor_id' => ['nullable', 'exists:users,id'],
            'max_participants' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['draft', 'active', 'finished', 'cancelled'])],
            'registration_ids' => ['nullable', 'array'],
            'registration_ids.*' => ['integer', 'exists:registrations,id'],
        ];
    }

    private function routePrefix(Request $request): string
    {
        return str_starts_with((string) $request->route()?->getName(), 'staff.') ? 'staff' : 'admin';
    }
}
