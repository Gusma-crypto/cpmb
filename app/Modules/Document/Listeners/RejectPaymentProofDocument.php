<?php

namespace App\Modules\Document\Listeners;

use App\Modules\Document\Models\Document;
use App\Modules\_Shared\Events\PaymentRejected;

class RejectPaymentProofDocument
{
    public function handle(PaymentRejected $event): void
    {
        if (! $event->proofDocumentId) {
            return;
        }

        Document::query()
            ->whereKey($event->proofDocumentId)
            ->update([
                'status' => 'rejected',
                'notes' => $event->notes,
                'reviewed_by' => $event->reviewedBy,
                'reviewed_at' => now(),
            ]);
    }
}
