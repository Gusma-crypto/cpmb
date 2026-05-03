<?php

namespace App\Modules\ExamRoom\Models;

use App\Models\User;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use App\Modules\Registration\Models\Registration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantExamAssignment extends Model
{
    protected $fillable = [
        'registration_id',
        'user_id',
        'exam_schedule_id',
        'exam_room_assignment_id',
        'participant_number',
        'status',
    ];

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

    public function roomAssignment(): BelongsTo
    {
        return $this->belongsTo(ExamRoomAssignment::class, 'exam_room_assignment_id');
    }
}
