<?php

namespace App\Modules\AcademicYear\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $academicYear = $this->route('academicYear');

        return [
            'label' => ['required', 'string', 'max:50', Rule::unique('academic_years', 'label')->ignore($academicYear)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
