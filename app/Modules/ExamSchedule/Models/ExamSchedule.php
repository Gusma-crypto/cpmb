<?php

namespace App\Modules\ExamSchedule\Models;

use App\Models\User;
use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtExam\Models\CbtExam;
use App\Modules\ExamRoom\Models\ExamRoomAssignment;
use App\Modules\ExamRoom\Models\ParticipantExamAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamSchedule extends Model
{
    use SoftDeletes;

    public const EXAM_TYPES = ['offline', 'online', 'hybrid'];

    public const STATUSES = ['draft', 'active', 'finished', 'cancelled'];

    protected $fillable = [
        'title',
        'code',
        'exam_type',
        'exam_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'exam_time_slot_id',
        'session_name',
        'cbt_exam_id',
        'status',
        'description',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'duration_minutes' => 'integer',
            'cbt_exam_id' => 'integer',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamTimeSlot::class, 'exam_time_slot_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ExamScheduleParticipant::class);
    }

    public function roomAssignments(): HasMany
    {
        return $this->hasMany(ExamRoomAssignment::class);
    }

    public function roomParticipants(): HasMany
    {
        return $this->hasMany(ParticipantExamAssignment::class, 'exam_schedule_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function cbtExam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function cbtAttempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class, 'exam_schedule_id');
    }
}
