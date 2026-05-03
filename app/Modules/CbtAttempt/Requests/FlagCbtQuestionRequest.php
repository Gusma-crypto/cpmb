<?php

namespace App\Modules\CbtAttempt\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FlagCbtQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') ?? false;
    }

    public function rules(): array
    {
        return [
            'attempt_uuid' => ['required', 'uuid', 'exists:cbt_attempts,uuid'],
            'question_id' => ['required', 'integer', 'exists:cbt_questions,id'],
            'is_flagged' => ['required', 'boolean'],
        ];
    }
}
