<?php

namespace App\Modules\ExamCard\Services;

use App\Modules\ExamCard\Models\ExamCard;
use App\Modules\ExamRoom\Models\ParticipantExamAssignment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExamCardService
{
    public function __construct(private readonly ExamCardEligibilityService $eligibility)
    {
    }

    public function paginate(): LengthAwarePaginator
    {
        return ExamCard::query()
            ->with(['registration.user', 'schedule', 'roomAssignment.room'])
            ->latest()
            ->paginate(10);
    }

    public function generateFromAssignment(ParticipantExamAssignment $assignment): ExamCard
    {
        $assignment->load(['registration', 'schedule', 'roomAssignment']);

        if (! $this->eligibility->eligible($assignment)) {
            throw ValidationException::withMessages(['exam_card' => 'Peserta belum memenuhi syarat cetak kartu ujian.']);
        }

        $card = ExamCard::firstOrNew([
            'registration_id' => $assignment->registration_id,
            'exam_schedule_id' => $assignment->exam_schedule_id,
        ]);

        $card->fill([
            'user_id' => $assignment->user_id,
            'exam_room_assignment_id' => $assignment->exam_room_assignment_id,
            'participant_number' => $assignment->participant_number,
            'status' => 'issued',
        ]);

        if (! $card->exists) {
            $card->card_number = $this->cardNumber($assignment);
            $card->verification_code = strtoupper(Str::random(12));
            $card->issued_at = now();
        }

        $card->save();

        return $card;
    }

    public function studentCards(int $userId)
    {
        return ExamCard::query()
            ->with([
                'registration.user',
                'registration.biodata',
                'registration.program',
                'registration.academicYear',
                'schedule',
                'roomAssignment.room',
                'roomAssignment.supervisor',
            ])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function ensureStudentCards(int $userId): void
    {
        ParticipantExamAssignment::query()
            ->with(['registration', 'schedule', 'roomAssignment'])
            ->where('user_id', $userId)
            ->get()
            ->each(function (ParticipantExamAssignment $assignment): void {
                if ($this->eligibility->eligible($assignment)) {
                    $this->generateFromAssignment($assignment);
                }
            });
    }

    private function cardNumber(ParticipantExamAssignment $assignment): string
    {
        return 'KPU-' . date('Y') . '-' . str_pad((string) $assignment->id, 6, '0', STR_PAD_LEFT);
    }
}
