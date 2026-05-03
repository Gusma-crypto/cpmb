<?php

namespace App\Modules\CbtExam\Models;

use App\Modules\QuestionBank\Models\CbtQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtExamQuestion extends Model
{
    protected $fillable = [
        'cbt_exam_id',
        'cbt_question_id',
        'order_index',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'order_index' => 'integer',
            'points' => 'integer',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'cbt_question_id');
    }
}
