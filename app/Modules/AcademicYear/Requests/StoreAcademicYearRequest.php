<?php

namespace App\Modules\AcademicYear\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:50', 'unique:academic_years,label'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
