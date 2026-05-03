<?php

namespace App\Modules\Registration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Document\Models\Document;
use App\Modules\Registration\Requests\ReviewPaymentRequest;
use App\Modules\Registration\Services\PaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentVerificationController extends Controller
{
    public function __construct(private readonly PaymentVerificationService $service)
    {
    }

    public function approve(Request $request, Document $document): RedirectResponse
    {
        $this->service->approve($request->user(), $document);

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Bukti pembayaran berhasil disetujui.');
    }

    public function reject(ReviewPaymentRequest $request, Document $document): RedirectResponse
    {
        $this->service->reject($request->user(), $document, $request->validated('notes'));

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Bukti pembayaran ditolak.');
    }
}
