<?php

namespace App\Modules\CbtAttempt\Services;

use App\Models\User;
use App\Modules\CbtAttempt\Models\CbtAnswer;
use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtAttempt\Models\CbtAuditLog;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use App\Modules\QuestionBank\Models\CbtQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CbtAttemptService
{
    public function __construct(private readonly CbtScoringService $scoring)
    {
    }

    public function startAttempt(User $user, ExamSchedule $schedule, Request $request): CbtAttempt
    {
        return DB::transaction(function () use ($user, $schedule, $request): CbtAttempt {
            $schedule = ExamSchedule::query()
                ->with(['cbtExam.questions.options', 'roomParticipants.registration'])
                ->lockForUpdate()
                ->findOrFail($schedule->id);

            $registration = $this->validateEligibility($user, $schedule);

            $existing = CbtAttempt::query()
                ->where('user_id', $user->id)
                ->where('exam_schedule_id', $schedule->id)
                ->where('cbt_exam_id', $schedule->cbt_exam_id)
                ->where('status', 'ongoing')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $questions = $schedule->cbtExam->questions->where('is_active', true)->values();

            if ($questions->isEmpty()) {
                throw ValidationException::withMessages(['exam' => 'Paket CBT belum memiliki soal aktif.']);
            }

            $questionOrder = $this->questionOrder($questions, (bool) $schedule->cbtExam->randomize_questions);
            $optionOrder = $this->optionOrder($questions, (bool) $schedule->cbtExam->randomize_options);

            $attempt = CbtAttempt::create([
                'uuid' => (string) Str::uuid(),
                'registration_id' => $registration->id,
                'user_id' => $user->id,
                'exam_schedule_id' => $schedule->id,
                'cbt_exam_id' => $schedule->cbt_exam_id,
                'token' => Str::random(64),
                'status' => 'ongoing',
                'started_at' => now(),
                'expires_at' => now()->addMinutes((int) $schedule->cbtExam->duration_minutes),
                'total_questions' => $questions->count(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                'question_order' => $questionOrder,
                'option_order' => $optionOrder,
            ]);

            $this->audit($attempt, 'started', ['schedule_id' => $schedule->id], $request);

            return $attempt;
        });
    }

    public function validateEligibility(User $user, ExamSchedule $schedule)
    {
        $schedule->loadMissing(['cbtExam', 'roomParticipants.registration']);
        $registration = $user->registration;

        if (! $registration) {
            throw ValidationException::withMessages(['registration' => 'Data pendaftaran tidak ditemukan.']);
        }

        if ($registration->status !== 'exam_ready') {
            throw ValidationException::withMessages(['registration' => 'Pendaftaran harus berstatus siap ujian sebelum mengikuti CBT.']);
        }

        if ($schedule->status !== 'active') {
            throw ValidationException::withMessages(['schedule' => 'Jadwal ujian belum aktif.']);
        }

        if (! $schedule->cbt_exam_id || ! $schedule->cbtExam || $schedule->cbtExam->status !== 'published') {
            throw ValidationException::withMessages(['exam' => 'Jadwal ujian belum memiliki paket CBT aktif.']);
        }

        $isAssigned = $schedule->roomParticipants()
            ->where('user_id', $user->id)
            ->where('registration_id', $registration->id)
            ->exists();

        if (! $isAssigned) {
            throw ValidationException::withMessages(['schedule' => 'Anda belum ditempatkan pada jadwal ujian ini.']);
        }

        $start = $schedule->exam_date->copy()->setTimeFromTimeString($schedule->start_time->format('H:i:s'));
        $end = $schedule->exam_date->copy()->setTimeFromTimeString($schedule->end_time->format('H:i:s'));

        if (now()->lt($start) || now()->gt($end)) {
            throw ValidationException::withMessages(['schedule' => 'CBT hanya dapat dimulai pada rentang waktu jadwal ujian.']);
        }

        if ($schedule->cbtExam->start_at && now()->lt($schedule->cbtExam->start_at)) {
            throw ValidationException::withMessages(['exam' => 'Paket CBT belum dibuka.']);
        }

        if ($schedule->cbtExam->end_at && now()->gt($schedule->cbtExam->end_at)) {
            throw ValidationException::withMessages(['exam' => 'Paket CBT sudah ditutup.']);
        }

        $attemptCount = CbtAttempt::query()
            ->where('registration_id', $registration->id)
            ->where('exam_schedule_id', $schedule->id)
            ->where('cbt_exam_id', $schedule->cbt_exam_id)
            ->whereIn('status', ['ongoing', 'submitted', 'timed_out'])
            ->count();

        if ($attemptCount >= $schedule->cbtExam->max_attempts) {
            throw ValidationException::withMessages(['attempt' => 'Kesempatan ujian CBT sudah habis.']);
        }

        $hasOngoing = CbtAttempt::query()
            ->where('user_id', $user->id)
            ->where('status', 'ongoing')
            ->exists();

        if ($hasOngoing) {
            throw ValidationException::withMessages(['attempt' => 'Anda masih memiliki sesi CBT yang sedang berjalan.']);
        }

        return $registration;
    }

    public function viewData(User $user, CbtAttempt $attempt): array
    {
        $this->assertOwner($user, $attempt);
        $this->autoTimeoutIfExpired($attempt);
        $attempt->refresh()->load(['cbtExam.questions.options', 'answers']);

        return [
            'attempt' => $attempt,
            'exam' => $attempt->cbtExam,
            'questions' => $this->orderedQuestions($attempt),
            'answers' => $attempt->answers->keyBy('cbt_question_id')->map(fn (CbtAnswer $answer): array => [
                'question_id' => $answer->cbt_question_id,
                'option_id' => $answer->cbt_question_option_id,
                'answer_text' => $answer->answer_text,
                'is_flagged' => $answer->is_flagged,
            ])->values(),
            'serverTime' => now()->toIso8601String(),
        ];
    }

    public function autosaveAnswer(User $user, array $data): CbtAnswer
    {
        return DB::transaction(function () use ($user, $data): CbtAnswer {
            $attempt = $this->ongoingAttemptForUuid($user, $data['attempt_uuid'], true);
            $this->assertQuestionBelongsToAttempt($attempt, (int) $data['question_id']);

            $optionId = $data['option_id'] ?? null;

            if ($optionId) {
                $belongs = CbtQuestionOption::query()
                    ->where('id', $optionId)
                    ->where('question_id', $data['question_id'])
                    ->exists();

                if (! $belongs) {
                    throw ValidationException::withMessages(['option_id' => 'Pilihan jawaban tidak valid untuk soal ini.']);
                }
            }

            $answer = CbtAnswer::updateOrCreate(
                [
                    'cbt_attempt_id' => $attempt->id,
                    'cbt_question_id' => $data['question_id'],
                ],
                [
                    'cbt_question_option_id' => $optionId,
                    'answer_text' => $data['answer_text'] ?? null,
                    'answered_at' => $optionId || ($data['answer_text'] ?? null) ? now() : null,
                ]
            );

            $this->refreshCounters($attempt);
            $this->audit($attempt, 'autosaved', ['question_id' => $data['question_id']]);

            return $answer->refresh();
        });
    }

    public function flagQuestion(User $user, array $data): CbtAnswer
    {
        return DB::transaction(function () use ($user, $data): CbtAnswer {
            $attempt = $this->ongoingAttemptForUuid($user, $data['attempt_uuid'], true);
            $this->assertQuestionBelongsToAttempt($attempt, (int) $data['question_id']);

            $answer = CbtAnswer::firstOrCreate(
                [
                    'cbt_attempt_id' => $attempt->id,
                    'cbt_question_id' => $data['question_id'],
                ],
                ['answered_at' => null]
            );

            $answer->update(['is_flagged' => (bool) $data['is_flagged']]);
            $this->refreshCounters($attempt);
            $this->audit($attempt, 'flagged', ['question_id' => $data['question_id'], 'is_flagged' => (bool) $data['is_flagged']]);

            return $answer->refresh();
        });
    }

    public function submitAttempt(User $user, CbtAttempt $attempt, bool $force = false): CbtAttempt
    {
        $this->assertOwner($user, $attempt);

        return $this->finishAttempt($attempt, $force);
    }

    public function heartbeat(User $user, CbtAttempt $attempt, bool $tabSwitched = false): CbtAttempt
    {
        $this->assertOwner($user, $attempt);

        if ($attempt->status === 'ongoing' && $this->isExpired($attempt)) {
            return $this->finishAttempt($attempt, true);
        }

        if ($tabSwitched) {
            $attempt->increment('browser_tab_switch_count');
            $attempt->refresh();
            $this->audit($attempt, 'tab_switched', ['count' => $attempt->browser_tab_switch_count]);
        }

        return $attempt;
    }

    public function forceSubmitExpiredAttempts(): int
    {
        $count = 0;

        CbtAttempt::query()
            ->where('status', 'ongoing')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->chunkById(50, function (Collection $attempts) use (&$count): void {
                foreach ($attempts as $attempt) {
                    $this->finishAttempt($attempt, true);
                    $count++;
                }
            });

        return $count;
    }

    private function finishAttempt(CbtAttempt $attempt, bool $force): CbtAttempt
    {
        return DB::transaction(function () use ($attempt, $force): CbtAttempt {
            $attempt = CbtAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if (! in_array($attempt->status, ['ongoing', 'pending'], true)) {
                return $attempt;
            }

            $timedOut = $force || $this->isExpired($attempt);

            $attempt->update([
                'status' => $timedOut ? 'timed_out' : 'submitted',
                'submitted_at' => now(),
                'force_submitted' => $timedOut,
            ]);

            $this->refreshCounters($attempt);
            $this->scoring->calculate($attempt->refresh());
            $this->audit($attempt, $timedOut ? 'timed_out' : 'submitted');

            return $attempt->refresh();
        });
    }

    private function ongoingAttemptForUuid(User $user, string $uuid, bool $lock = false): CbtAttempt
    {
        $query = CbtAttempt::query()->where('uuid', $uuid);

        if ($lock) {
            $query->lockForUpdate();
        }

        $attempt = $query->firstOrFail();
        $this->assertOwner($user, $attempt);

        if ($attempt->status !== 'ongoing') {
            throw ValidationException::withMessages(['attempt' => 'Sesi CBT tidak sedang berjalan.']);
        }

        if ($this->isExpired($attempt)) {
            $this->finishAttempt($attempt, true);
            throw ValidationException::withMessages(['attempt' => 'Waktu ujian sudah habis.']);
        }

        return $attempt;
    }

    private function autoTimeoutIfExpired(CbtAttempt $attempt): void
    {
        if ($attempt->status === 'ongoing' && $this->isExpired($attempt)) {
            $this->finishAttempt($attempt, true);
        }
    }

    private function isExpired(CbtAttempt $attempt): bool
    {
        return $attempt->expires_at && now()->greaterThanOrEqualTo($attempt->expires_at);
    }

    private function assertOwner(User $user, CbtAttempt $attempt): void
    {
        if ((int) $attempt->user_id !== (int) $user->id) {
            abort(403, 'Anda tidak memiliki akses ke sesi CBT ini.');
        }
    }

    private function assertQuestionBelongsToAttempt(CbtAttempt $attempt, int $questionId): void
    {
        $exists = $attempt->cbtExam()
            ->whereHas('questions', fn ($query) => $query->where('cbt_questions.id', $questionId))
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages(['question_id' => 'Soal tidak termasuk dalam paket ujian ini.']);
        }
    }

    private function refreshCounters(CbtAttempt $attempt): void
    {
        $attempt->update([
            'answered_questions' => $attempt->answers()->whereNotNull('answered_at')->count(),
            'flagged_questions' => $attempt->answers()->where('is_flagged', true)->count(),
        ]);
    }

    private function questionOrder(Collection $questions, bool $randomize): array
    {
        $ordered = $questions->sortBy(fn ($question) => (int) ($question->pivot?->order_index ?? 0))->values();

        if ($randomize) {
            $ordered = $ordered->shuffle()->values();
        }

        return $ordered->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    private function optionOrder(Collection $questions, bool $randomize): array
    {
        return $questions->mapWithKeys(function ($question) use ($randomize): array {
            $options = $question->options->sortBy('order_index')->values();

            if ($randomize) {
                $options = $options->shuffle()->values();
            }

            return [(string) $question->id => $options->pluck('id')->map(fn ($id) => (int) $id)->all()];
        })->all();
    }

    private function orderedQuestions(CbtAttempt $attempt): array
    {
        $questionMap = $attempt->cbtExam->questions->keyBy('id');
        $orders = $attempt->question_order ?: $questionMap->keys()->all();
        $optionOrders = $attempt->option_order ?: [];

        return collect($orders)
            ->map(function ($questionId) use ($questionMap, $optionOrders): ?array {
                $question = $questionMap->get($questionId);

                if (! $question) {
                    return null;
                }

                $optionMap = $question->options->keyBy('id');
                $orderedOptionIds = $optionOrders[(string) $question->id] ?? $optionMap->keys()->all();

                return [
                    'id' => $question->id,
                    'type' => $question->type,
                    'question_text' => $question->question_text,
                    'options' => collect($orderedOptionIds)
                        ->map(fn ($optionId) => $optionMap->get($optionId))
                        ->filter()
                        ->map(fn ($option) => [
                            'id' => $option->id,
                            'option_text' => $option->option_text,
                        ])
                        ->values(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function audit(CbtAttempt $attempt, string $event, array $payload = [], ?Request $request = null): void
    {
        CbtAuditLog::create([
            'cbt_attempt_id' => $attempt->id,
            'user_id' => $attempt->user_id,
            'event' => $event,
            'payload' => $payload ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 1000) : null,
        ]);
    }
}
