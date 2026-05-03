<?php

namespace App\Modules\Payment\Services;

use App\Models\User;
use App\Modules\Document\Models\Document;
use App\Modules\Payment\Models\PaymentVerification;
use App\Modules\Registration\Models\Registration;
use App\Modules\_Shared\Events\PaymentProofSubmitted;
use App\Modules\_Shared\Events\PaymentRejected;
use App\Modules\_Shared\Events\PaymentVerified;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentVerificationService
{
    public function studentData(User $user): array
    {
        $registration = Registration::query()
            ->with(['paymentVerification.proofDocument', 'paymentVerification.verifier', 'documents'])
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        return [
            'registration' => $registration,
            'paymentVerification' => $registration?->paymentVerification,
        ];
    }

    public function uploadProof(User $user, array $data): PaymentVerification
    {
        $registration = Registration::query()
            ->with('paymentVerification.proofDocument')
            ->where('user_id', $user->id)
            ->latest()
            ->firstOrFail();

        $this->assertStudentCanUpload($registration);

        $paymentRef = 'PAY-' . $registration->registration_number;

        $verification = DB::transaction(function () use ($registration, $data): PaymentVerification {
            $registration->documents()
                ->where('type', 'payment_proof')
                ->get()
                ->each(function (Document $document): void {
                    Storage::disk('local')->delete($document->file_path);
                    $document->delete();
                });

            /** @var UploadedFile $file */
            $file = $data['file'];
            $path = $file->store("pmb-documents/{$registration->id}", 'local');

            $document = Document::create([
                'registration_id' => $registration->id,
                'type' => 'payment_proof',
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
                'size_kb' => (int) ceil($file->getSize() / 1024),
                'status' => 'pending',
            ]);

            $verification = PaymentVerification::updateOrCreate(
                ['registration_id' => $registration->id],
                [
                    'bank_name' => $data['bank_name'],
                    'account_name' => $data['account_name'],
                    'transfer_amount' => $data['transfer_amount'],
                    'transfer_date' => $data['transfer_date'],
                    'proof_document_id' => $document->id,
                    'status' => 'pending',
                    'notes' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                ]
            );

            return $verification->refresh()->load(['registration.user', 'proofDocument']);
        });

        event(new PaymentProofSubmitted($registration->id, $paymentRef));

        return $verification;
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        return PaymentVerification::query()
            ->with(['registration.user', 'registration.program', 'proofDocument', 'verifier'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('bank_name', 'like', "%{$search}%")
                        ->orWhere('account_name', 'like', "%{$search}%")
                        ->orWhereHas('registration', fn (Builder $query) => $query->where('registration_number', 'like', "%{$search}%"))
                        ->orWhereHas('registration.user', function (Builder $query) use ($search): void {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(($filters['status'] ?? '') !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function verify(User $admin, PaymentVerification $payment): PaymentVerification
    {
        $this->authorizeAdmin($admin);

        $payment = DB::transaction(function () use ($admin, $payment): PaymentVerification {
            $payment = PaymentVerification::query()
                ->with(['registration', 'proofDocument'])
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $payment->update([
                'status' => 'verified',
                'notes' => null,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            return $payment->refresh();
        });

        event(new PaymentVerified($payment->registration_id, $payment->proof_document_id, $admin->id));

        return $payment->refresh();
    }

    public function reject(User $admin, PaymentVerification $payment, string $notes): PaymentVerification
    {
        $this->authorizeAdmin($admin);

        $payment = DB::transaction(function () use ($admin, $payment, $notes): PaymentVerification {
            $payment = PaymentVerification::query()
                ->with(['registration', 'proofDocument'])
                ->lockForUpdate()
                ->findOrFail($payment->id);

            $payment->update([
                'status' => 'rejected',
                'notes' => $notes,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            return $payment->refresh();
        });

        event(new PaymentRejected($payment->registration_id, $payment->proof_document_id, $admin->id, $notes));

        return $payment->refresh();
    }

    private function assertStudentCanUpload(Registration $registration): void
    {
        if ($registration->status !== 'verified') {
            throw ValidationException::withMessages([
                'registration' => 'Bukti pembayaran hanya dapat diunggah setelah pendaftaran diverifikasi panitia.',
            ]);
        }

        if ($registration->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'payment' => 'Pembayaran sudah diverifikasi.',
            ]);
        }

        if ($registration->paymentVerification?->status === 'pending') {
            throw ValidationException::withMessages([
                'payment' => 'Bukti pembayaran sedang menunggu verifikasi admin.',
            ]);
        }
    }

    private function authorizeAdmin(User $user): void
    {
        abort_unless($user->hasAnyRole(['admin', 'superadmin']), 403);
    }
}
