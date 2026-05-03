<?php

namespace App\Modules\ExamRoom\Services;

use App\Models\User;
use App\Modules\ExamRoom\Models\ExamRoom;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ExamRoomService
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return ExamRoom::query()
            ->when($search, function ($query, string $search): void {
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%"));
            })
            ->withCount('assignments')
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function delete(ExamRoom $room): void
    {
        $this->assertNotAssigned($room);

        $room->delete();
    }

    public function create(User $user, array $data): ExamRoom
    {
        return ExamRoom::create([
            ...Arr::only($data, ['name', 'code', 'location', 'capacity', 'status']),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function update(User $user, ExamRoom $room, array $data): ExamRoom
    {
        $this->assertCapacityCanBeChanged($room, (int) $data['capacity']);

        $room->update([
            ...Arr::only($data, ['name', 'code', 'location', 'capacity', 'status']),
            'updated_by' => $user->id,
        ]);

        return $room->refresh();
    }

    private function assertCapacityCanBeChanged(ExamRoom $room, int $newCapacity): void
    {
        $minimumCapacity = $room->assignments()
            ->where('status', '!=', 'cancelled')
            ->withCount('participants')
            ->get()
            ->max(fn ($assignment) => max((int) $assignment->max_participants, (int) $assignment->participants_count)) ?? 0;

        if ($newCapacity < $minimumCapacity) {
            throw ValidationException::withMessages([
                'capacity' => "Kapasitas tidak boleh lebih kecil dari kuota/total peserta aktif ({$minimumCapacity}).",
            ]);
        }
    }

    private function assertNotAssigned(ExamRoom $room): void
    {
        if ($room->assignments()->exists()) {
            throw ValidationException::withMessages([
                'room' => 'Ruang ujian tidak dapat dihapus karena sudah dipakai di penempatan peserta. Kosongkan atau hapus penempatannya terlebih dahulu.',
            ]);
        }
    }
}
