<?php

namespace App\Modules\ExamCard\Services;

use App\Models\User;
use App\Modules\ExamCard\Models\ExamCard;
use App\Modules\ExamCard\Models\ExamCardPrintLog;
use Illuminate\Http\Request;

class ExamCardPrintService
{
    public function log(ExamCard $card, ?User $user, Request $request): void
    {
        ExamCardPrintLog::create([
            'exam_card_id' => $card->id,
            'user_id' => $user?->id,
            'printed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }
}
