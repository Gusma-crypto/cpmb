<?php

namespace App\Modules\QuestionBank\Requests;

use App\Modules\QuestionBank\Models\CbtQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreCbtQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:cbt_question_categories,id'],
            'type' => ['required', Rule::in(CbtQuestion::TYPES)],
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'difficulty' => ['required', Rule::in(CbtQuestion::DIFFICULTIES)],
            'is_active' => ['boolean'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.option_text' => ['required', 'string'],
            'options.*.is_correct' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $correctCount = collect($this->input('options', []))
                ->filter(fn (array $option): bool => filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN))
                ->count();

            if ($correctCount !== 1) {
                $validator->errors()->add('options', 'Soal wajib memiliki tepat 1 jawaban benar.');
            }
        });
    }
}
