<?php

namespace App\Modules\CbtExam\Models;

use App\Models\User;
use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtAttempt\Models\CbtResult;
use App\Modules\AcademicYear\Models\AcademicYear;
use App\Modules\Program\Models\Program;
use App\Modules\QuestionBank\Models\CbtQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CbtExam extends Model
{
    use SoftDeletes;

    public const STATUSES = ['draft', 'published', 'closed'];

    protected $fillable = [
        'title',
        'description',
        'academic_year_id',
        'program_id',
        'duration_minutes',
        'pass_score',
        'total_questions',
        'randomize_questions',
        'randomize_options',
        'max_attempts',
        'start_at',
        'end_at',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'pass_score' => 'integer',
            'total_questions' => 'integer',
            'randomize_questions' => 'boolean',
            'randomize_options' => 'boolean',
            'max_attempts' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(CbtQuestion::class, 'cbt_exam_questions', 'cbt_exam_id', 'cbt_question_id')
            ->withPivot(['id', 'order_index', 'points'])
            ->withTimestamps()
            ->orderBy('cbt_exam_questions.order_index')
            ->orderBy('cbt_exam_questions.id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(CbtResult::class);
    }
}
