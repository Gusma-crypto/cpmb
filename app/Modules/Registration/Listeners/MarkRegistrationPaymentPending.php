<?php

namespace App\Modules\Registration\Listeners;

use App\Modules\Registration\Models\Registration;
use App\Modules\_Shared\Events\PaymentProofSubmitted;

class MarkRegistrationPaymentPending
{
    public function handle(PaymentProofSubmitted $event): void
    {
        Registration::query()
            ->whereKey($event->registrationId)
            ->update([
                'payment_status' => 'pending',
                'payment_ref' => $event->paymentRef,
            ]);
    }
}
