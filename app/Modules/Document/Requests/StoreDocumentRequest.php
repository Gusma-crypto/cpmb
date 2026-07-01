<?php

namespace App\Modules\Document\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'registration_id' => [
                Rule::requiredIf(fn () => $this->user()?->hasAnyRole(['admin', 'staff', 'superadmin']) ?? false),
                'nullable',
                'integer',
                'exists:registrations,id',
            ],
            'type' => ['required', Rule::in(['ijazah', 'ktp', 'photo', 'skhun', 'etc'])],
            'file' => [
                'required',
                'file',
                'extensions:pdf,jpg,jpeg',
                'max:2048',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.extensions' => 'Ekstensi file harus .pdf, .jpg, atau .jpeg.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ];
    }
}
