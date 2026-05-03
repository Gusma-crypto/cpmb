<?php

namespace App\Modules\Registration\Listeners;

use App\Modules\Registration\Models\Registration;
use App\Modules\_Shared\Events\PaymentVerified;

class MarkRegistrationPaymentPaid
{
    public function handle(PaymentVerified $event): void
    {
        Registration::query()
            ->whereKey($event->registrationId)
            ->update([
                'payment_status' => 'paid',
                'status' => 'exam_ready',
            ]);
    }
}
