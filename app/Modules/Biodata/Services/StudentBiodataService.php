<?php

namespace App\Modules\Biodata\Services;

use App\Models\User;
use App\Modules\Biodata\Models\StudentBiodata;
use App\Modules\Registration\Models\Registration;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class StudentBiodataService
{
    public function paginateFor(User $user): LengthAwarePaginator
    {
        $query = StudentBiodata::query()
            ->with(['registration.user'])
            ->latest();

        if (!$this->canManageAll($user)) {
            $query->whereHas('registration', fn ($registration) => $registration->where('user_id', $user->id));
        }

        return $query->paginate(10);
    }

    public function create(User $user, array $data): StudentBiodata
    {
        $this->assertStudentDoesNotHaveBiodata($user);

        $registration = $this->resolveRegistration($user, $data);

        $this->assertRegistrationWaveIsOpen($user);
        $this->assertRegistrationDoesNotHaveBiodata($registration);

        $payload = $this->payload($data);
        $payload['registration_id'] = $registration->id;

        return StudentBiodata::create($payload);
    }

    public function update(User $user, StudentBiodata $biodata, array $data): StudentBiodata
    {
        $this->authorize($user, $biodata);

        if (! $this->canManageAll($user) && ! in_array($biodata->registration?->status, ['draft', 'revision_required'], true)) {
            throw ValidationException::withMessages([
                'biodata' => 'Biodata inti hanya dapat diubah saat pendaftaran draft atau perlu revisi.',
            ]);
        }

        $payload = $this->payload($data);

        if ($this->canManageAll($user) && isset($data['registration_id'])) {
            $registration = Registration::query()->findOrFail($data['registration_id']);
            $this->assertRegistrationDoesNotHaveBiodata($registration, $biodata);

            $payload['registration_id'] = $data['registration_id'];
        }

        $biodata->update($payload);

        return $biodata->refresh();
    }

    public function delete(User $user, StudentBiodata $biodata): void
    {
        $this->authorize($user, $biodata);

        if (! $this->canManageAll($user)) {
            throw ValidationException::withMessages([
                'biodata' => 'Biodata yang sudah diisi tidak dapat dihapus. Silakan gunakan tombol Edit untuk memperbaiki data.',
            ]);
        }

        $this->deletePhoto($biodata);
        $biodata->delete();
    }

    public function authorize(User $user, StudentBiodata $biodata): void
    {
        if ($this->canManageAll($user)) {
            return;
        }

        abort_unless($biodata->registration()->where('user_id', $user->id)->exists(), 403);
    }

    public function availableRegistrationsFor(User $user)
    {
        $query = Registration::query()
            ->with('user')
            ->whereDoesntHave('biodata')
            ->orderBy('registration_number');

        if (!$this->canManageAll($user)) {
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }

    public function canManageAll(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'superadmin']);
    }

    private function resolveRegistration(User $user, array $data): Registration
    {
        if ($this->canManageAll($user)) {
            return Registration::query()->findOrFail($data['registration_id'] ?? null);
        }

        return Registration::query()->where('user_id', $user->id)->firstOrFail();
    }

    public function hasBiodata(User $user): bool
    {
        return StudentBiodata::query()
            ->whereHas('registration', fn ($registration) => $registration->where('user_id', $user->id))
            ->exists();
    }

    public function firstFor(User $user): ?StudentBiodata
    {
        return StudentBiodata::query()
            ->whereHas('registration', fn ($registration) => $registration->where('user_id', $user->id))
            ->first();
    }

    private function assertStudentDoesNotHaveBiodata(User $user): void
    {
        if ($this->canManageAll($user)) {
            return;
        }

        if ($this->hasBiodata($user)) {
            throw ValidationException::withMessages([
                'biodata' => 'Anda sudah pernah mengisi biodata. Satu mahasiswa hanya boleh mengisi biodata satu kali.',
            ]);
        }
    }

    private function assertRegistrationDoesNotHaveBiodata(Registration $registration, ?StudentBiodata $current = null): void
    {
        $exists = $registration->biodata()
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'registration_id' => 'Biodata untuk pendaftaran ini sudah pernah diisi.',
            ]);
        }
    }

    private function assertRegistrationWaveIsOpen(User $user): void
    {
        if ($this->canManageAll($user)) {
            return;
        }

        $isOpen = RegistrationWave::query()
            ->where('is_active', true)
            ->where('open_at', '<=', now())
            ->where('close_at', '>=', now())
            ->exists();

        if (! $isOpen) {
            throw ValidationException::withMessages([
                'biodata' => 'Gelombang pendaftaran belum dibuka. Hubungi panitia PMB untuk informasi lebih lanjut.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $data): array
    {
        return Arr::only($data, [
            'nik',
            'birth_place',
            'birth_date',
            'gender',
            'religion',
            'address',
            'province',
            'city',
            'school_name',
            'school_graduation_year',
            'parent_name',
            'parent_phone',
            'parent_job',
        ]);
    }

    private function deletePhoto(StudentBiodata $biodata): void
    {
        if ($biodata->photo) {
            Storage::disk('public')->delete($biodata->photo);
        }
    }
}
