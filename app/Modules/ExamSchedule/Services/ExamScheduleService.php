<?php

namespace App\Modules\ExamSchedule\Services;

use App\Models\User;
use App\Modules\CbtExam\Models\CbtExam;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use App\Modules\ExamSchedule\Models\ExamTimeSlot;
use App\Modules\ExamRoom\Models\ParticipantExamAssignment;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamScheduleService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return ExamSchedule::query()
            ->with('cbtExam')
            ->withCount([
                'roomAssignments',
                'roomParticipants as participants_count',
            ])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('session_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['exam_type'] ?? null, fn ($query, string $type) => $query->where('exam_type', $type))
            ->orderByDesc('exam_date')
            ->orderBy('start_time')
            ->paginate(10)
            ->withQueryString();
    }

    public function create(User $user, array $data): ExamSchedule
    {
        return DB::transaction(function () use ($user, $data): ExamSchedule {
            $schedule = ExamSchedule::create([
                ...$this->payload($data),
                'duration_minutes' => $this->durationMinutes($data['start_time'], $data['end_time']),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            return $schedule->refresh();
        });
    }

    public function update(User $user, ExamSchedule $schedule, array $data): ExamSchedule
    {
        return DB::transaction(function () use ($user, $schedule, $data): ExamSchedule {
            $schedule->update([
                ...$this->payload($data),
                'duration_minutes' => $this->durationMinutes($data['start_time'], $data['end_time']),
                'updated_by' => $user->id,
            ]);

            return $schedule->refresh();
        });
    }

    public function delete(ExamSchedule $schedule): void
    {
        $this->assertNotAssigned($schedule);

        $schedule->delete();
    }

    public function studentSchedules(User $user): Collection
    {
        return ExamSchedule::query()
            ->whereIn('id', ParticipantExamAssignment::query()
                ->select('exam_schedule_id')
                ->where('user_id', $user->id))
            ->whereIn('status', ['draft', 'active', 'finished'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();
    }

    public function supervisorSchedules(User $user): LengthAwarePaginator
    {
        return ExamSchedule::query()
            ->withCount(['roomParticipants as participants_count'])
            ->whereHas('roomAssignments', fn ($query) => $query->where('supervisor_id', $user->id))
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->paginate(10);
    }

    public function timeSlots()
    {
        return ExamTimeSlot::query()->orderBy('start_time')->get();
    }

    public function cbtExams()
    {
        return CbtExam::query()
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'duration_minutes', 'pass_score']);
    }

    private function payload(array $data): array
    {
        return Arr::only($data, [
            'title',
            'code',
            'exam_type',
            'exam_date',
            'start_time',
            'end_time',
            'exam_time_slot_id',
            'session_name',
            'cbt_exam_id',
            'status',
            'description',
        ]);
    }

    private function durationMinutes(string $start, string $end): int
    {
        return (int) Carbon::createFromFormat('H:i', $start)->diffInMinutes(Carbon::createFromFormat('H:i', $end));
    }

    private function assertNotAssigned(ExamSchedule $schedule): void
    {
        if ($schedule->roomAssignments()->exists()) {
            throw ValidationException::withMessages([
                'schedule' => 'Jadwal ujian tidak dapat dihapus karena sudah dipakai di penempatan peserta. Kosongkan atau hapus penempatannya terlebih dahulu.',
            ]);
        }
    }
}
