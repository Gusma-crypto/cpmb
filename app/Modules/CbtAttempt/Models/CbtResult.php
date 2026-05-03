<?php

namespace App\Modules\CbtAttempt\Models;

use App\Models\User;
use App\Modules\CbtExam\Models\CbtExam;
use App\Modules\Registration\Models\Registration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtResult extends Model
{
    protected $fillable = [
        'cbt_attempt_id',
        'registration_id',
        'user_id',
        'cbt_exam_id',
        'total_questions',
        'correct_answers',
        'wrong_answers',
        'unanswered',
        'raw_score',
        'final_score',
        'pass_score',
        'is_passed',
        'published_at',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_score' => 'decimal:2',
            'final_score' => 'decimal:2',
            'pass_score' => 'decimal:2',
            'is_passed' => 'boolean',
            'published_at' => 'datetime',
            'calculated_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(CbtAttempt::class, 'cbt_attempt_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cbtExam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }
}
