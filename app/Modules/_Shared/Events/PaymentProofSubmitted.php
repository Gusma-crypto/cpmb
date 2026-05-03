<?php

namespace App\Modules\_Shared\Events;

class PaymentProofSubmitted
{
    public function __construct(
        public readonly int $registrationId,
        public readonly string $paymentRef,
    ) {
    }
}
