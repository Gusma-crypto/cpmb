<?php

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Requests\StorePaymentProofRequest;
use App\Modules\Payment\Services\PaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentPaymentController extends Controller
{
    public function __construct(private readonly PaymentVerificationService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Student/Payment/Index', $this->service->studentData($request->user()));
    }

    public function store(StorePaymentProofRequest $request): RedirectResponse
    {
        $this->service->uploadProof($request->user(), $request->validated());

        return redirect()
            ->route('student.payments.index')
            ->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi admin.');
    }
}
