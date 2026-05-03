<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cbt:close-expired-attempts', function () {
    $count = app(\App\Modules\CbtAttempt\Services\CbtAttemptService::class)->forceSubmitExpiredAttempts();

    $this->info("Closed {$count} expired CBT attempt(s).");
})->purpose('Close expired ongoing CBT attempts');

Schedule::command('cbt:close-expired-attempts')->everyMinute();
