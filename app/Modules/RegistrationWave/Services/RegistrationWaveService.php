<?php

namespace App\Modules\RegistrationWave\Services;

use App\Modules\Registration\Models\Registration;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationWaveService
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return RegistrationWave::query()
            ->select('registration_waves.*')
            ->selectSub(
                Registration::query()
                    ->selectRaw('count(*)')
                    ->whereColumn('registrations.registration_wave_id', 'registration_waves.id'),
                'registrations_count'
            )
            ->with('academicYear')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('label', 'like', "%{$search}%")
                        ->orWhere('wave_number', $search)
                        ->orWhereHas('academicYear', fn ($query) => $query->where('label', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function create(array $data): RegistrationWave
    {
        $this->assertUniqueWithinAcademicYear($data);

        return DB::transaction(function () use ($data): RegistrationWave {
            $registrationWave = RegistrationWave::create($this->normalize($data));
            $this->syncPrograms($registrationWave, $data['programs'] ?? []);

            return $registrationWave->refresh();
        });
    }

    public function update(RegistrationWave $registrationWave, array $data): RegistrationWave
    {
        $this->assertUniqueWithinAcademicYear($data, $registrationWave);

        return DB::transaction(function () use ($registrationWave, $data): RegistrationWave {
            $registrationWave->update($this->normalize($data));
            $this->syncPrograms($registrationWave, $data['programs'] ?? []);

            return $registrationWave->refresh();
        });
    }

    public function delete(RegistrationWave $registrationWave): void
    {
        if ($this->isUsedByRegistration($registrationWave)) {
            throw ValidationException::withMessages([
                'registration_wave' => 'Gelombang pendaftaran tidak dapat dihapus karena sudah digunakan oleh calon mahasiswa.',
            ]);
        }

        $registrationWave->delete();
    }

    private function isUsedByRegistration(RegistrationWave $registrationWave): bool
    {
        return Registration::query()
            ->where('registration_wave_id', $registrationWave->id)
            ->exists();
    }

    private function normalize(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return Arr::only($data, [
            'academic_year_id',
            'wave_number',
            'label',
            'open_at',
            'close_at',
            'is_active',
            'description',
        ]);
    }

    private function syncPrograms(RegistrationWave $registrationWave, array $programs): void
    {
        $programIds = [];

        foreach ($programs as $program) {
            if (! isset($program['program_id'])) {
                continue;
            }

            $programId = (int) $program['program_id'];
            $programIds[] = $programId;

            $attributes = [
                'registration_wave_id' => $registrationWave->id,
                'program_id' => $programId,
            ];
            $values = [
                'status' => $program['status'] ?? 'nonaktif',
                'is_open' => filter_var($program['is_open'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'tanggal_mulai' => $registrationWave->open_at,
                'tanggal_selesai' => $registrationWave->close_at,
                'updated_at' => now(),
            ];

            $exists = DB::table('gelombang_program')
                ->where($attributes)
                ->exists();

            if ($exists) {
                DB::table('gelombang_program')
                    ->where($attributes)
                    ->update($values);
            } else {
                DB::table('gelombang_program')->insert([
                    ...$attributes,
                    ...$values,
                    'created_at' => now(),
                ]);
            }
        }

        DB::table('gelombang_program')
            ->where('registration_wave_id', $registrationWave->id)
            ->when($programIds !== [], fn ($query) => $query->whereNotIn('program_id', $programIds))
            ->delete();
    }

    private function assertUniqueWithinAcademicYear(array $data, ?RegistrationWave $current = null): void
    {
        $duplicateNumber = RegistrationWave::query()
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('wave_number', $data['wave_number'])
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->exists();

        $duplicateLabel = RegistrationWave::query()
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('label', $data['label'])
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->exists();

        $messages = [];

        if ($duplicateNumber) {
            $messages['wave_number'] = 'Nomor gelombang sudah digunakan pada tahun ajaran ini.';
        }

        if ($duplicateLabel) {
            $messages['label'] = 'Label gelombang sudah digunakan pada tahun ajaran ini.';
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }
}
