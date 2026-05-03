<?php

namespace App\Modules\ExamCard\Services;

use App\Modules\ExamRoom\Models\ParticipantExamAssignment;

class ExamCardEligibilityService
{
    public function eligible(ParticipantExamAssignment $assignment): bool
    {
        $registration = $assignment->registration;

        return $registration
            && $registration->status === 'exam_ready'
            && $assignment->roomAssignment !== null
            && $assignment->schedule !== null;
    }
}
