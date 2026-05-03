<?php

namespace App\Modules\ExamSchedule\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamTimeSlot extends Model
{
    protected $table = 'exam_time_slots';

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class, 'exam_time_slot_id');
    }
}
