<?php

namespace App\Modules\_Shared\Events;

class PaymentRejected
{
    public function __construct(
        public readonly int $registrationId,
        public readonly ?int $proofDocumentId,
        public readonly int $reviewedBy,
        public readonly string $notes,
    ) {
    }
}
