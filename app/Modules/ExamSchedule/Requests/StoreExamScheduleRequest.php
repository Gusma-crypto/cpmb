<?php

namespace App\Modules\ExamSchedule\Requests;

use App\Modules\ExamSchedule\Models\ExamSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['admin', 'staff']) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:exam_schedules,code'],
            'exam_type' => ['required', Rule::in(ExamSchedule::EXAM_TYPES)],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'exam_time_slot_id' => ['nullable', 'exists:exam_time_slots,id'],
            'session_name' => ['required', 'string', 'max:100'],
            'cbt_exam_id' => ['nullable', Rule::exists('cbt_exams', 'id')->where('status', 'published')],
            'status' => ['required', Rule::in(ExamSchedule::STATUSES)],
            'description' => ['nullable', 'string'],
        ];
    }
}
