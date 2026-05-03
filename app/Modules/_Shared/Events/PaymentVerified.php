<?php

namespace App\Modules\_Shared\Events;

class PaymentVerified
{
    public function __construct(
        public readonly int $registrationId,
        public readonly ?int $proofDocumentId,
        public readonly int $verifiedBy,
    ) {
    }
}
