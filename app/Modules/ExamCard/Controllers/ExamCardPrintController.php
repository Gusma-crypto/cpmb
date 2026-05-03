<?php

namespace App\Modules\ExamCard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamCard\Models\ExamCard;
use App\Modules\ExamCard\Services\ExamCardPrintService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExamCardPrintController extends Controller
{
    public function __construct(private readonly ExamCardPrintService $service)
    {
    }

    public function store(Request $request, ExamCard $examCard): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'staff']) || $examCard->user_id === $request->user()->id, 403);

        $this->service->log($examCard, $request->user(), $request);

        return back()->with('success', 'Log cetak kartu ujian tersimpan.');
    }
}
