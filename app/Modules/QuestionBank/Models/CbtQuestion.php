<?php

namespace App\Modules\QuestionBank\Models;

use App\Models\User;
use App\Modules\CbtExam\Models\CbtExam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtQuestion extends Model
{
    public const TYPES = ['multiple_choice', 'true_false'];

    public const DIFFICULTIES = ['easy', 'medium', 'hard'];

    protected $fillable = [
        'category_id',
        'created_by',
        'type',
        'question_text',
        'explanation',
        'difficulty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CbtQuestionCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function options(): HasMany
    {
        return $this->hasMany(CbtQuestionOption::class, 'question_id')->orderBy('order_index');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(CbtExam::class, 'cbt_exam_questions', 'cbt_question_id', 'cbt_exam_id')
            ->withPivot(['id', 'order_index', 'points'])
            ->withTimestamps();
    }
}
