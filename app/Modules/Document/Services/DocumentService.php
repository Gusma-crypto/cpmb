<?php

namespace App\Modules\Document\Services;

use App\Models\User;
use App\Modules\Document\Models\Document;
use App\Modules\Registration\Models\Registration;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    public const TYPES = [
        'ijazah' => 'Ijazah',
        'ktp' => 'KTP',
        'photo' => 'Pasphoto 3x4 Background Merah',
        'skhun' => 'SKHUN',
        'etc' => 'Lainnya',
    ];

    public function paginateFor(User $user): LengthAwarePaginator
    {
        $query = Document::query()
            ->with(['registration.user'])
            ->latest();

        if (!$this->canManageAll($user)) {
            $query->whereHas('registration', fn ($registration) => $registration->where('user_id', $user->id));
        }

        return $query->paginate(10);
    }

    public function store(User $user, array $data): Document
    {
        $registration = $this->resolveRegistration($user, $data);

        $this->assertRegistrationWaveIsOpen($user);

        if (! $this->canManageAll($user) && ! in_array($registration->status, ['draft', 'revision_required'], true)) {
            throw ValidationException::withMessages([
                'type' => 'Dokumen inti hanya dapat diunggah saat pendaftaran draft atau perlu revisi.',
            ]);
        }

        if ($registration->documents()->where('type', $data['type'])->exists()) {
            throw ValidationException::withMessages([
                'type' => 'Dokumen untuk jenis ini sudah pernah diunggah. Hapus dokumen lama sebelum mengunggah ulang.',
            ]);
        }

        /** @var UploadedFile $file */
        $file = $data['file'];
        $path = $file->store("pmb-documents/{$registration->id}", 'local');

        return Document::create([
            'registration_id' => $registration->id,
            'type' => $data['type'],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
            'size_kb' => (int) ceil($file->getSize() / 1024),
            'status' => 'pending',
        ]);
    }

    public function delete(User $user, Document $document): void
    {
        $this->authorize($user, $document);

        if (! $this->canManageAll($user) && ! in_array($document->registration?->status, ['draft', 'revision_required'], true)) {
            throw ValidationException::withMessages([
                'document' => 'Dokumen hanya dapat dihapus saat pendaftaran draft atau perlu revisi.',
            ]);
        }

        Storage::disk('local')->delete($document->file_path);
        $document->delete();
    }

    public function approve(User $user, Document $document): Document
    {
        $this->authorizeReviewer($user, $document);

        $document->update([
            'status' => 'approved',
            'notes' => null,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        return $document->refresh();
    }

    public function reject(User $user, Document $document, string $notes): Document
    {
        $this->authorizeReviewer($user, $document);

        $document->update([
            'status' => 'rejected',
            'notes' => $notes,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        return $document->refresh();
    }

    public function authorize(User $user, Document $document): void
    {
        if ($this->canManageAll($user)) {
            return;
        }

        abort_unless($document->registration()->where('user_id', $user->id)->exists(), 403);
    }

    public function availableRegistrationsFor(User $user)
    {
        $query = Registration::query()
            ->with('user')
            ->orderBy('registration_number');

        if (!$this->canManageAll($user)) {
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }

    public function currentRegistrationFor(User $user): ?Registration
    {
        if ($this->canManageAll($user)) {
            return null;
        }

        return Registration::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();
    }

    public function canManageAll(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'staff', 'superadmin']);
    }

    private function authorizeReviewer(User $user, Document $document): void
    {
        abort_unless($this->canManageAll($user), 403);

    }

    private function resolveRegistration(User $user, array $data): Registration
    {
        if ($this->canManageAll($user)) {
            return Registration::query()->findOrFail($data['registration_id'] ?? null);
        }

        return Registration::query()->where('user_id', $user->id)->firstOrFail();
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
                'document' => 'Gelombang pendaftaran belum dibuka. Hubungi panitia PMB untuk informasi lebih lanjut.',
            ]);
        }
    }
}
