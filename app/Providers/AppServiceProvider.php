<?php

namespace App\Providers;

use App\Modules\Document\Listeners\ApprovePaymentProofDocument;
use App\Modules\Document\Listeners\RejectPaymentProofDocument;
use App\Modules\Registration\Listeners\MarkRegistrationPaymentPaid;
use App\Modules\Registration\Listeners\MarkRegistrationPaymentPending;
use App\Modules\Registration\Listeners\MarkRegistrationPaymentUnpaid;
use App\Modules\_Shared\Events\PaymentProofSubmitted;
use App\Modules\_Shared\Events\PaymentRejected;
use App\Modules\_Shared\Events\PaymentVerified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(PaymentProofSubmitted::class, MarkRegistrationPaymentPending::class);
        Event::listen(PaymentVerified::class, MarkRegistrationPaymentPaid::class);
        Event::listen(PaymentVerified::class, ApprovePaymentProofDocument::class);
        Event::listen(PaymentRejected::class, MarkRegistrationPaymentUnpaid::class);
        Event::listen(PaymentRejected::class, RejectPaymentProofDocument::class);

        Vite::prefetch(concurrency: 3);
    }
}
