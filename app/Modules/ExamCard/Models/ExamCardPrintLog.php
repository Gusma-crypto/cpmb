<?php

namespace App\Modules\ExamCard\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamCardPrintLog extends Model
{
    protected $fillable = ['exam_card_id', 'user_id', 'printed_at', 'ip_address', 'user_agent'];

    protected function casts(): array
    {
        return ['printed_at' => 'datetime'];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(ExamCard::class, 'exam_card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
