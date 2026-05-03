<?php

use App\Modules\Payment\Controllers\AdminPaymentController;
use App\Modules\Payment\Controllers\StudentPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:student'])
    ->prefix('student/payments')
    ->name('student.payments.')
    ->group(function (): void {
        Route::get('/', [StudentPaymentController::class, 'index'])->name('index');
        Route::post('/', [StudentPaymentController::class, 'store'])->name('store');
    });

Route::middleware(['auth', 'role:admin,superadmin'])
    ->prefix('admin/payments')
    ->name('admin.payments.')
    ->group(function (): void {
        Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
        Route::post('/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('verify');
        Route::post('/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('reject');
    });
