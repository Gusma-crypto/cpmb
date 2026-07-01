<?php

namespace App\Modules\ExamRoom\Services;

use App\Modules\ExamRoom\Models\ExamRoom;
use App\Modules\ExamRoom\Models\ExamRoomAssignment;
use App\Modules\ExamRoom\Models\ParticipantExamAssignment;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use App\Modules\Registration\Models\Registration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamRoomAssignmentService
{
    public function paginate(): LengthAwarePaginator
    {
        return ExamRoomAssignment::query()
            ->with(['room', 'schedule', 'supervisor'])
            ->withCount('participants')
            ->latest()
            ->paginate(10);
    }

    public function create(array $data): ExamRoomAssignment
    {
        $room = ExamRoom::findOrFail($data['exam_room_id']);
        $schedule = ExamSchedule::findOrFail($data['exam_schedule_id']);
        $registrationIds = $data['registration_ids'] ?? [];

        $this->assertRoomIsActive($room);
        $this->assertCapacity($room, (int) $data['max_participants'], count($registrationIds));
        $this->assertParticipantsAreAvailable($registrationIds);
        $this->assertNoConflict($room, $schedule);

        return DB::transaction(function () use ($data, $schedule, $registrationIds): ExamRoomAssignment {
            $assignment = ExamRoomAssignment::create([
                'exam_room_id' => $data['exam_room_id'],
                'exam_schedule_id' => $data['exam_schedule_id'],
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'max_participants' => $data['max_participants'],
                'status' => $data['status'] ?? 'draft',
            ]);

            $this->syncParticipants($assignment, $schedule, $registrationIds);

            return $assignment->refresh();
        });
    }

    public function update(ExamRoomAssignment $assignment, array $data): ExamRoomAssignment
    {
        $room = ExamRoom::findOrFail($data['exam_room_id']);
        $schedule = ExamSchedule::findOrFail($data['exam_schedule_id']);
        $registrationIds = $data['registration_ids'] ?? [];

        $this->assertRoomIsActive($room);
        $this->assertCapacity($room, (int) $data['max_participants'], count($registrationIds));
        $this->assertParticipantsAreAvailable($registrationIds, $assignment);
        $this->assertNoConflict($room, $schedule, $assignment);

        return DB::transaction(function () use ($assignment, $data, $schedule, $registrationIds): ExamRoomAssignment {
            $assignment->update([
                'exam_room_id' => $data['exam_room_id'],
                'exam_schedule_id' => $data['exam_schedule_id'],
                'supervisor_id' => $data['supervisor_id'] ?? null,
                'max_participants' => $data['max_participants'],
                'status' => $data['status'] ?? 'draft',
            ]);

            $this->syncParticipants($assignment, $schedule, $registrationIds);

            return $assignment->refresh();
        });
    }

    public function studentAssignments(int $userId)
    {
        return ParticipantExamAssignment::query()
            ->with(['roomAssignment.room', 'roomAssignment.schedule', 'roomAssignment.supervisor', 'registration'])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function lecturerAssignments(int $userId): LengthAwarePaginator
    {
        return ExamRoomAssignment::query()
            ->with([
                'room',
                'schedule.cbtExam',
                'participants.registration.user',
                'participants.registration.cbtAttempts' => fn ($query) => $query->latest(),
                'participants.registration.cbtAttempts.latestAuditLogs',
            ])
            ->withCount('participants')
            ->where('supervisor_id', $userId)
            ->latest()
            ->paginate(10);
    }

    public function formData(): array
    {
        return [
            'rooms' => ExamRoom::query()->where('status', 'active')->orderBy('name')->get(),
            'schedules' => ExamSchedule::query()->orderByDesc('exam_date')->get(),
            'registrations' => Registration::query()
                ->with('user')
                ->where('status', 'exam_ready')
                ->latest()
                ->get(['id', 'registration_number', 'user_id', 'status']),
            'assignedRegistrationIds' => ParticipantExamAssignment::query()
                ->pluck('registration_id')
                ->values(),
            'supervisors' => \App\Models\User::role('dosen')->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function formDataForEdit(ExamRoomAssignment $assignment): array
    {
        return [
            ...$this->formData(),
            'assignedRegistrationIds' => ParticipantExamAssignment::query()
                ->where('exam_room_assignment_id', '!=', $assignment->id)
                ->pluck('registration_id')
                ->values(),
        ];
    }

    private function syncParticipants(ExamRoomAssignment $assignment, ExamSchedule $schedule, array $registrationIds): void
    {
        $registrationIds = array_values(array_unique($registrationIds));

        ParticipantExamAssignment::query()
            ->where('exam_room_assignment_id', $assignment->id)
            ->whereNotIn('registration_id', $registrationIds)
            ->delete();

        $registrations = Registration::query()
            ->whereIn('id', $registrationIds)
            ->get(['id', 'user_id', 'status', 'registration_number']);

        $this->assertParticipantsAreExamReady($registrations);

        foreach ($registrations as $registration) {
            ParticipantExamAssignment::updateOrCreate(
                ['registration_id' => $registration->id, 'exam_schedule_id' => $schedule->id],
                [
                    'user_id' => $registration->user_id,
                    'exam_room_assignment_id' => $assignment->id,
                    'participant_number' => $this->participantNumber($schedule, $registration->id),
                    'status' => 'assigned',
                ]
            );
        }
    }

    private function participantNumber(ExamSchedule $schedule, int $registrationId): string
    {
        return $schedule->code . '-' . str_pad((string) $registrationId, 5, '0', STR_PAD_LEFT);
    }

    private function assertParticipantsAreAvailable(array $registrationIds, ?ExamRoomAssignment $current = null): void
    {
        $registrationIds = array_values(array_unique($registrationIds));

        if ($registrationIds === []) {
            return;
        }

        $assignedRegistrations = ParticipantExamAssignment::query()
            ->with('registration')
            ->whereIn('registration_id', $registrationIds)
            ->when($current, fn ($query) => $query->where('exam_room_assignment_id', '!=', $current->id))
            ->get();

        if ($assignedRegistrations->isNotEmpty()) {
            $numbers = $assignedRegistrations
                ->map(fn (ParticipantExamAssignment $assignment) => $assignment->registration?->registration_number)
                ->filter()
                ->implode(', ');

            throw ValidationException::withMessages([
                'registration_ids' => "Peserta berikut sudah memiliki penempatan ujian: {$numbers}.",
            ]);
        }
    }

    private function assertParticipantsAreExamReady(Collection $registrations): void
    {
        $notReady = $registrations
            ->filter(fn (Registration $registration): bool => $registration->status !== 'exam_ready')
            ->pluck('registration_number')
            ->implode(', ');

        if ($notReady !== '') {
            throw ValidationException::withMessages([
                'registration_ids' => "Peserta berikut belum siap ujian: {$notReady}.",
            ]);
        }
    }

    private function assertRoomIsActive(ExamRoom $room): void
    {
        if ($room->status !== 'active') {
            throw ValidationException::withMessages(['exam_room_id' => 'Ruangan tidak aktif.']);
        }
    }

    private function assertCapacity(ExamRoom $room, int $maxParticipants, int $participantCount): void
    {
        if ($maxParticipants > $room->capacity) {
            throw ValidationException::withMessages([
                'max_participants' => "Kuota peserta tidak boleh melebihi kapasitas ruang ({$room->capacity}).",
            ]);
        }

        if ($participantCount > $maxParticipants) {
            throw ValidationException::withMessages([
                'registration_ids' => "Jumlah peserta dipilih ({$participantCount}) melebihi kuota peserta ({$maxParticipants}).",
            ]);
        }
    }

    private function assertNoConflict(ExamRoom $room, ExamSchedule $schedule, ?ExamRoomAssignment $current = null): void
    {
        $conflict = ExamRoomAssignment::query()
            ->where('exam_room_id', $room->id)
            ->where('status', '!=', 'cancelled')
            ->whereHas('schedule', function ($query) use ($schedule): void {
                $query->where('exam_date', $schedule->exam_date)
                    ->where('start_time', '<', $schedule->end_time)
                    ->where('end_time', '>', $schedule->start_time);
            })
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages(['exam_room_id' => 'Ruangan sudah dipakai pada jadwal yang bentrok.']);
        }
    }
}
