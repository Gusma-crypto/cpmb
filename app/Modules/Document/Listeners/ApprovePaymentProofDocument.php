<?php

namespace App\Modules\Document\Listeners;

use App\Modules\Document\Models\Document;
use App\Modules\_Shared\Events\PaymentVerified;

class ApprovePaymentProofDocument
{
    public function handle(PaymentVerified $event): void
    {
        if (! $event->proofDocumentId) {
            return;
        }

        Document::query()
            ->whereKey($event->proofDocumentId)
            ->update([
                'status' => 'approved',
                'notes' => null,
                'reviewed_by' => $event->verifiedBy,
                'reviewed_at' => now(),
            ]);
    }
}
