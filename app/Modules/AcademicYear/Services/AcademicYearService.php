<?php

namespace App\Modules\AcademicYear\Services;

use App\Modules\AcademicYear\Models\AcademicYear;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return AcademicYear::query()
            ->when($search, fn ($query, string $search) => $query->where('label', 'like', "%{$search}%"))
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();
    }

    public function create(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data): AcademicYear {
            $data = $this->normalize($data);

            if ($data['is_active']) {
                AcademicYear::query()->update(['is_active' => false]);
            }

            return AcademicYear::create($data);
        });
    }

    public function update(AcademicYear $academicYear, array $data): AcademicYear
    {
        return DB::transaction(function () use ($academicYear, $data): AcademicYear {
            $data = $this->normalize($data);

            if ($data['is_active']) {
                AcademicYear::query()
                    ->whereKeyNot($academicYear->id)
                    ->update(['is_active' => false]);
            }

            $academicYear->update($data);

            return $academicYear->refresh();
        });
    }

    public function delete(AcademicYear $academicYear): bool
    {
        if ($academicYear->registrations()->exists()) {
            return false;
        }

        $academicYear->delete();

        return true;
    }

    public function deactivate(AcademicYear $academicYear): AcademicYear
    {
        $academicYear->update(['is_active' => false]);

        return $academicYear->refresh();
    }

    private function normalize(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }
}
