<?php

namespace App\Modules\CbtAttempt\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HeartbeatCbtAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('student') ?? false;
    }

    public function rules(): array
    {
        return [
            'tab_switched' => ['nullable', 'boolean'],
        ];
    }
}
