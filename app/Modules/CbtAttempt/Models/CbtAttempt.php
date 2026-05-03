<?php

namespace App\Modules\CbtAttempt\Models;

use App\Models\User;
use App\Modules\CbtExam\Models\CbtExam;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use App\Modules\Registration\Models\Registration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CbtAttempt extends Model
{
    use SoftDeletes;

    public const STATUSES = ['pending', 'ongoing', 'submitted', 'timed_out', 'cancelled'];

    protected $fillable = [
        'uuid',
        'registration_id',
        'user_id',
        'exam_schedule_id',
        'cbt_exam_id',
        'token',
        'status',
        'started_at',
        'submitted_at',
        'expires_at',
        'total_questions',
        'answered_questions',
        'flagged_questions',
        'ip_address',
        'user_agent',
        'browser_tab_switch_count',
        'force_submitted',
        'question_order',
        'option_order',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'expires_at' => 'datetime',
            'total_questions' => 'integer',
            'answered_questions' => 'integer',
            'flagged_questions' => 'integer',
            'browser_tab_switch_count' => 'integer',
            'force_submitted' => 'boolean',
            'question_order' => 'array',
            'option_order' => 'array',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function cbtExam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CbtAnswer::class, 'cbt_attempt_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(CbtResult::class, 'cbt_attempt_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(CbtAuditLog::class, 'cbt_attempt_id');
    }

    public function latestAuditLogs(): HasMany
    {
        return $this->hasMany(CbtAuditLog::class, 'cbt_attempt_id')->latest();
    }
}
