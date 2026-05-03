<?php

namespace App\Modules\Registration\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RevisionRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'staff', 'superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'revision_notes' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'revision_notes.required' => 'Catatan revisi wajib diisi.',
            'revision_notes.max' => 'Catatan revisi maksimal 2000 karakter.',
        ];
    }
}
