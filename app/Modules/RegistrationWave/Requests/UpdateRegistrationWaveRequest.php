<?php

namespace App\Modules\RegistrationWave\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegistrationWaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'wave_number' => ['required', 'integer', 'min:1'],
            'label' => ['required', 'string', 'max:100'],
            'open_at' => ['required', 'date'],
            'close_at' => ['required', 'date', 'after:open_at'],
            'is_active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'programs' => ['required', 'array', 'min:1'],
            'programs.*.program_id' => ['required', 'integer', 'exists:programs,id'],
            'programs.*.status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'programs.*.is_open' => ['required', 'boolean'],
        ];
    }
}
