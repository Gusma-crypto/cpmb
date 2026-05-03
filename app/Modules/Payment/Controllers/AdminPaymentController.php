<?php

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Models\PaymentVerification;
use App\Modules\Payment\Requests\RejectPaymentVerificationRequest;
use App\Modules\Payment\Services\PaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPaymentController extends Controller
{
    public function __construct(private readonly PaymentVerificationService $service)
    {
    }

    public function index(Request $request): Response
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => in_array($request->query('status'), PaymentVerification::STATUSES, true)
                ? $request->query('status')
                : '',
        ];

        return Inertia::render('Admin/Payments/Index', [
            'payments' => $this->service->list($filters),
            'filters' => $filters,
            'statusOptions' => PaymentVerification::STATUSES,
        ]);
    }

    public function verify(Request $request, PaymentVerification $payment): RedirectResponse
    {
        $this->service->verify($request->user(), $payment);

        return back()->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function reject(RejectPaymentVerificationRequest $request, PaymentVerification $payment): RedirectResponse
    {
        $this->service->reject($request->user(), $payment, $request->validated('notes'));

        return back()->with('success', 'Pembayaran ditolak.');
    }
}
