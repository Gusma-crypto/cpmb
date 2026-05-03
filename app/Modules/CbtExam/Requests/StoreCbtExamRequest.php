<?php

namespace App\Modules\CbtExam\Requests;

use App\Modules\CbtExam\Models\CbtExam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCbtExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'pass_score' => ['required', 'integer', 'min:0', 'max:100'],
            'total_questions' => ['nullable', 'integer', 'min:1'],
            'randomize_questions' => ['boolean'],
            'randomize_options' => ['boolean'],
            'max_attempts' => ['required', 'integer', 'min:1'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],
            'status' => ['required', Rule::in(CbtExam::STATUSES)],
        ];
    }
}
