<?php

namespace App\Modules\ExamCard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamCard\Models\ExamCard;
use App\Modules\ExamCard\Services\ExamCardService;
use App\Modules\ExamRoom\Models\ParticipantExamAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExamCardController extends Controller
{
    public function __construct(private readonly ExamCardService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/ExamCards/Index', [
            'cards' => $this->service->paginate(),
            'assignments' => ParticipantExamAssignment::with(['registration.user', 'schedule', 'roomAssignment.room'])->latest()->get(),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['participant_assignment_id' => ['required', 'exists:participant_exam_assignments,id']]);
        $this->service->generateFromAssignment(ParticipantExamAssignment::findOrFail($data['participant_assignment_id']));

        return redirect()->route($this->routePrefix($request) . '.exam-cards.index')->with('success', 'Kartu ujian berhasil dibuat.');
    }

    public function show(Request $request, ExamCard $examCard): Response
    {
        return Inertia::render('Admin/ExamCards/Show', [
            'card' => $examCard->load(['registration.user', 'schedule', 'roomAssignment.room', 'roomAssignment.supervisor', 'printLogs.user']),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    private function routePrefix(Request $request): string
    {
        return str_starts_with((string) $request->route()?->getName(), 'staff.') ? 'staff' : 'admin';
    }
}
