<?php

namespace App\Modules\Registration\Services;

use App\Models\User;
use App\Modules\Registration\Models\Registration;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrationService
{
    public const REQUIRED_DOCUMENTS = ['ijazah', 'ktp', 'photo'];

    public const REGISTRATION_STATUSES = ['draft', 'submitted', 'under_review', 'revision_required', 'verified', 'rejected', 'exam_ready'];

    public function paginateFor(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->filteredQueryFor($user, $filters)
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function exportFor(User $user, array $filters = []): Collection
    {
        return $this->filteredQueryFor($user, $filters)
            ->latest()
            ->get();
    }

    public function filtersFrom(array $input): array
    {
        $search = trim((string) ($input['search'] ?? ''));
        $status = (string) ($input['status'] ?? '');
        return [
            'search' => $search,
            'status' => in_array($status, self::REGISTRATION_STATUSES, true) ? $status : '',
        ];
    }

    public function canViewRegistrationList(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin']) || $user->can('registration.view');
    }

    public function canExport(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'superadmin']) || $user->can('registration.export');
    }

    public function filteredQueryFor(User $user, array $filters = []): Builder
    {
        $filters = $this->filtersFrom($filters);

        $query = Registration::query()
            ->with(['user', 'biodata', 'documents', 'program']);

        if (! $this->canManageAll($user)) {
            $query->where('user_id', $user->id);
        }

        $query
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('registration_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $query) use ($search): void {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('program', function (Builder $query) use ($search): void {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] !== '', fn (Builder $query) => $query->where('status', $filters['status']));

        return $query;
    }

    public function create(User $user, array $data): Registration
    {
        if (! $this->canManageAll($user) && $user->registration()->exists()) {
            throw ValidationException::withMessages([
                'registration' => 'Anda sudah memiliki data pendaftaran. Silakan lengkapi biodata dan dokumen.',
            ]);
        }

        return DB::transaction(function () use ($user, $data): Registration {
            $wave = RegistrationWave::query()
                ->whereKey($data['registration_wave_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $pivot = DB::table('gelombang_program')
                ->where('registration_wave_id', $wave->id)
                ->where('program_id', (int) $data['program_id'])
                ->lockForUpdate()
                ->first();

            $this->assertRegistrationIsOpen($wave, $pivot);

            return Registration::create([
                'user_id'             => $user->id,
                'academic_year_id'    => $wave->academic_year_id,
                'registration_wave_id' => $wave->id,
                'program_id'          => $data['program_id'],
                'wave'                => $wave->wave_number,
                'registration_number' => $this->generateRegistrationNumber(),
                'status'              => 'draft',
            ]);
        });
    }

    public function submit(User $user, Registration $registration): Registration
    {
        $this->authorize($user, $registration);

        if (! in_array($registration->status, ['draft', 'revision_required'], true)) {
            throw ValidationException::withMessages([
                'registration' => 'Pendaftaran hanya dapat disubmit saat berstatus draft atau revision required.',
            ]);
        }

        $this->assertComplete($registration);

        $registration->update([
            'status'       => 'submitted',
            'revision_notes' => null,
            'submitted_at' => now(),
        ]);

        return $registration->refresh();
    }

    public function verify(User $user, Registration $registration): Registration
    {
        abort_unless($this->canManageAll($user), 403);

        if (! in_array($registration->status, ['submitted', 'under_review'], true)) {
            throw ValidationException::withMessages([
                'registration' => 'Hanya registrasi berstatus submitted atau under review yang dapat diverifikasi.',
            ]);
        }

        $this->assertRequiredDocumentsApproved($registration);

        $registration->update([
            'status'      => 'exam_ready',
            'revision_notes' => null,
            'verified_at' => now(),
        ]);

        return $registration->refresh();
    }

    public function startReview(User $user, Registration $registration): Registration
    {
        abort_unless($this->canManageAll($user), 403);

        if ($registration->status !== 'submitted') {
            throw ValidationException::withMessages([
                'registration' => 'Hanya registrasi berstatus submitted yang dapat diproses review.',
            ]);
        }

        $registration->update(['status' => 'under_review']);

        return $registration->refresh();
    }

    public function requestRevision(User $user, Registration $registration, string $notes): Registration
    {
        abort_unless($this->canManageAll($user), 403);

        if (! in_array($registration->status, ['submitted', 'under_review'], true)) {
            throw ValidationException::withMessages([
                'registration' => 'Revisi hanya dapat diminta saat pendaftaran submitted atau under review.',
            ]);
        }

        $registration->update([
            'status' => 'revision_required',
            'revision_notes' => $notes,
        ]);

        return $registration->refresh();
    }

    public function reject(User $user, Registration $registration): Registration
    {
        abort_unless($this->canManageAll($user), 403);

        if ($registration->status === 'exam_ready') {
            throw ValidationException::withMessages([
                'registration' => 'Registrasi yang sudah siap ujian tidak dapat ditolak dari menu pendaftaran.',
            ]);
        }

        $registration->update(['status' => 'rejected']);

        return $registration->refresh();
    }

    public function authorize(User $user, Registration $registration): void
    {
        if ($this->canManageAll($user)) {
            return;
        }

        abort_unless($registration->user_id === $user->id, 403);
    }

    public function canManageAll(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'superadmin']);
    }

    private function generateRegistrationNumber(): string
    {
        do {
            $number = 'PMB-' . date('Y') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Registration::where('registration_number', $number)->exists());

        return $number;
    }

    private function assertRegistrationIsOpen(RegistrationWave $wave, ?object $pivot): void
    {
        if (! $wave->is_active || $wave->open_at->isFuture() || $wave->close_at->isPast()) {
            throw ValidationException::withMessages([
                'registration_wave_id' => 'Gelombang pendaftaran belum dibuka atau sudah ditutup.',
            ]);
        }

        if (! $pivot || $pivot->status !== 'aktif' || ! (bool) $pivot->is_open) {
            throw ValidationException::withMessages([
                'program_id' => 'Program studi tidak dibuka pada gelombang ini.',
            ]);
        }

        $now = now();

        if (($pivot->tanggal_mulai && $now->lt(Carbon::parse($pivot->tanggal_mulai))) || ($pivot->tanggal_selesai && $now->gt(Carbon::parse($pivot->tanggal_selesai)))) {
            throw ValidationException::withMessages([
                'program_id' => 'Periode pendaftaran program studi belum dibuka atau sudah ditutup.',
            ]);
        }
    }

    private function assertComplete(Registration $registration): void
    {
        if (! $registration->biodata()->exists()) {
            throw ValidationException::withMessages([
                'biodata' => 'Biodata belum diisi.',
            ]);
        }

        $uploaded = $registration->documents()->pluck('type')->all();
        $missing  = array_diff(self::REQUIRED_DOCUMENTS, $uploaded);

        if (! empty($missing)) {
            throw ValidationException::withMessages([
                'documents' => 'Dokumen belum lengkap: ' . implode(', ', $missing) . '.',
            ]);
        }
    }

    private function assertRequiredDocumentsApproved(Registration $registration): void
    {
        $documents = $registration->documents()
            ->whereIn('type', self::REQUIRED_DOCUMENTS)
            ->get()
            ->keyBy('type');

        $notApproved = collect(self::REQUIRED_DOCUMENTS)
            ->filter(fn (string $type): bool => $documents->get($type)?->status !== 'approved')
            ->values()
            ->all();

        if ($notApproved !== []) {
            throw ValidationException::withMessages([
                'documents' => 'Dokumen wajib belum disetujui: ' . implode(', ', $notApproved) . '.',
            ]);
        }
    }

}
