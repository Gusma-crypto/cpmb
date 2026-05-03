<?php

namespace App\Modules\QuestionBank\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCbtQuestionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'superadmin']) ?? false;
    }

    public function rules(): array
    {
        $category = $this->route('questionCategory');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('cbt_question_categories', 'name')->ignore($category)],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
