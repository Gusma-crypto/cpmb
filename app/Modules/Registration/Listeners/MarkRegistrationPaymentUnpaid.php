<?php

namespace App\Modules\Registration\Listeners;

use App\Modules\Registration\Models\Registration;
use App\Modules\_Shared\Events\PaymentRejected;

class MarkRegistrationPaymentUnpaid
{
    public function handle(PaymentRejected $event): void
    {
        Registration::query()
            ->whereKey($event->registrationId)
            ->update([
                'payment_status' => 'unpaid',
                'status' => 'verified',
            ]);
    }
}
