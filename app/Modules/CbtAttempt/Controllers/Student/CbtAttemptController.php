<?php

namespace App\Modules\CbtAttempt\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtAttempt\Requests\AutosaveCbtAnswerRequest;
use App\Modules\CbtAttempt\Requests\FlagCbtQuestionRequest;
use App\Modules\CbtAttempt\Requests\HeartbeatCbtAttemptRequest;
use App\Modules\CbtAttempt\Requests\SubmitCbtAttemptRequest;
use App\Modules\CbtAttempt\Services\CbtAttemptService;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CbtAttemptController extends Controller
{
    public function __construct(private readonly CbtAttemptService $service)
    {
    }

    public function start(Request $request, ExamSchedule $schedule): RedirectResponse
    {
        $attempt = $this->service->startAttempt($request->user(), $schedule, $request);

        return redirect()->route('student.cbt.attempt.show', $attempt->uuid);
    }

    public function show(Request $request, CbtAttempt $attempt): Response
    {
        return Inertia::render('Student/Cbt/Exam', $this->service->viewData($request->user(), $attempt));
    }

    public function autosave(AutosaveCbtAnswerRequest $request): JsonResponse
    {
        $answer = $this->service->autosaveAnswer($request->user(), $request->validated());

        return response()->json([
            'saved' => true,
            'answer' => $answer,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function flag(FlagCbtQuestionRequest $request): JsonResponse
    {
        $answer = $this->service->flagQuestion($request->user(), $request->validated());

        return response()->json([
            'saved' => true,
            'answer' => $answer,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    public function submit(SubmitCbtAttemptRequest $request, CbtAttempt $attempt): RedirectResponse
    {
        $this->service->submitAttempt($request->user(), $attempt);

        return redirect()
            ->route('student.exam-card.index')
            ->with('success', 'Ujian CBT berhasil disubmit.');
    }

    public function heartbeat(HeartbeatCbtAttemptRequest $request, CbtAttempt $attempt): JsonResponse
    {
        $attempt = $this->service->heartbeat($request->user(), $attempt, (bool) $request->boolean('tab_switched'));

        return response()->json([
            'status' => $attempt->status,
            'tab_switch_count' => $attempt->browser_tab_switch_count,
            'expires_at' => $attempt->expires_at?->toIso8601String(),
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
