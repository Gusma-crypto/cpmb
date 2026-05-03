<?php

namespace App\Modules\CbtExam\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SyncCbtExamQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'questions' => ['array'],
            'questions.*.cbt_question_id' => ['required', 'integer', 'exists:cbt_questions,id'],
            'questions.*.points' => ['required', 'integer', 'min:1'],
            'questions.*.order_index' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $ids = collect($this->input('questions', []))
                ->pluck('cbt_question_id')
                ->filter()
                ->values();

            if ($ids->duplicates()->isNotEmpty()) {
                $validator->errors()->add('questions', 'Soal yang dipilih tidak boleh duplikat.');
            }

            $inactiveCount = \App\Modules\QuestionBank\Models\CbtQuestion::query()
                ->whereIn('id', $ids)
                ->where('is_active', false)
                ->count();

            if ($inactiveCount > 0) {
                $validator->errors()->add('questions', 'Semua soal yang dipilih harus berstatus aktif.');
            }
        });
    }
}
