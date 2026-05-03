<?php

namespace App\Modules\QuestionBank\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCbtQuestionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'superadmin']) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:cbt_question_categories,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
