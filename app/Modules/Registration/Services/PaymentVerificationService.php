<?php

namespace App\Modules\Registration\Services;

use App\Models\User;
use App\Modules\Document\Models\Document;
use App\Modules\Registration\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentVerificationService
{
    public function approve(User $user, Document $document): void
    {
        $this->authorize($user);
        $this->assertPaymentProof($document);

        $registration = $this->registrationFrom($document);

        DB::transaction(function () use ($user, $document, $registration) {
            $document->update([
                'status'      => 'approved',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'notes'       => null,
            ]);

            $registration->update([
                'payment_status' => 'paid',
                'status' => 'exam_ready',
            ]);
        });
    }

    public function reject(User $user, Document $document, string $notes): void
    {
        $this->authorize($user);
        $this->assertPaymentProof($document);

        $registration = $this->registrationFrom($document);

        DB::transaction(function () use ($user, $document, $registration, $notes) {
            $document->update([
                'status'      => 'rejected',
                'notes'       => $notes,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            $registration->update([
                'payment_status' => 'unpaid',
                'status' => 'verified',
            ]);
        });
    }

    public function authorize(User $user): void
    {
        abort_unless(
            $user->hasAnyRole(['admin', 'staff', 'superadmin']),
            403
        );
    }

    private function assertPaymentProof(Document $document): void
    {
        if ($document->type !== 'payment_proof') {
            throw ValidationException::withMessages([
                'document' => 'Dokumen bukan bukti pembayaran.',
            ]);
        }
    }

    private function registrationFrom(Document $document): Registration
    {
        return Registration::findOrFail($document->registration_id);
    }
}
