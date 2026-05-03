<?php

namespace App\Modules\CbtAttempt\Models;

use App\Modules\QuestionBank\Models\CbtQuestion;
use App\Modules\QuestionBank\Models\CbtQuestionOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtAnswer extends Model
{
    protected $fillable = [
        'cbt_attempt_id',
        'cbt_question_id',
        'cbt_question_option_id',
        'answer_text',
        'is_flagged',
        'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(CbtAttempt::class, 'cbt_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'cbt_question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(CbtQuestionOption::class, 'cbt_question_option_id');
    }
}
