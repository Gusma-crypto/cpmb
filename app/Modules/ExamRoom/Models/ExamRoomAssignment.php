<?php

namespace App\Modules\ExamRoom\Models;

use App\Models\User;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamRoomAssignment extends Model
{
    protected $fillable = ['exam_room_id', 'exam_schedule_id', 'supervisor_id', 'max_participants', 'status'];

    protected function casts(): array
    {
        return ['max_participants' => 'integer'];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ExamRoom::class, 'exam_room_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ParticipantExamAssignment::class);
    }
}
