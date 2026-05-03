<?php

namespace App\Modules\Payment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') ?? false;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:100'],
            'account_name' => ['required', 'string', 'max:150'],
            'transfer_amount' => ['required', 'numeric', 'min:1'],
            'transfer_date' => ['required', 'date', 'before_or_equal:today'],
            'file' => [
                'required',
                'file',
                'mimetypes:application/pdf,image/jpeg,image/jpg',
                'extensions:pdf,jpg,jpeg',
                'max:2048',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.required' => 'Nama bank wajib diisi.',
            'account_name.required' => 'Nama pemilik rekening wajib diisi.',
            'transfer_amount.required' => 'Nominal transfer wajib diisi.',
            'transfer_amount.min' => 'Nominal transfer tidak valid.',
            'transfer_date.required' => 'Tanggal transfer wajib diisi.',
            'transfer_date.before_or_equal' => 'Tanggal transfer tidak boleh melebihi hari ini.',
            'file.required' => 'Bukti pembayaran wajib diunggah.',
            'file.mimetypes' => 'File harus berupa PDF atau JPG.',
            'file.extensions' => 'Ekstensi file harus .pdf, .jpg, atau .jpeg.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ];
    }
}
