<?php

namespace App\Modules\CbtAttempt\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutosaveCbtAnswerRequest extends FormRequest
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
            'option_id' => ['nullable', 'integer', 'exists:cbt_question_options,id'],
            'answer_text' => ['nullable', 'string'],
        ];
    }
}
