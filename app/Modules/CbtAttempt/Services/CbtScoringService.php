<?php

namespace App\Modules\CbtAttempt\Services;

use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtAttempt\Models\CbtResult;

class CbtScoringService
{
    public function calculate(CbtAttempt $attempt): CbtResult
    {
        $attempt->loadMissing(['cbtExam.questions.options', 'answers']);

        $questions = $attempt->cbtExam->questions;
        $answersByQuestion = $attempt->answers->keyBy('cbt_question_id');
        $maxScore = 0;
        $rawScore = 0;
        $correctAnswers = 0;
        $wrongAnswers = 0;
        $unanswered = 0;

        foreach ($questions as $question) {
            $points = (int) ($question->pivot?->points ?? 1);
            $maxScore += $points;
            $answer = $answersByQuestion->get($question->id);

            if (! $answer || ! $answer->cbt_question_option_id) {
                $unanswered++;
                continue;
            }

            $correctOption = $question->options->firstWhere('is_correct', true);

            if ($correctOption && (int) $answer->cbt_question_option_id === (int) $correctOption->id) {
                $correctAnswers++;
                $rawScore += $points;
                continue;
            }

            $wrongAnswers++;
        }

        $finalScore = $maxScore > 0 ? round(($rawScore / $maxScore) * 100, 2) : 0;
        $passScore = (float) $attempt->cbtExam->pass_score;

        return CbtResult::updateOrCreate(
            ['cbt_attempt_id' => $attempt->id],
            [
                'registration_id' => $attempt->registration_id,
                'user_id' => $attempt->user_id,
                'cbt_exam_id' => $attempt->cbt_exam_id,
                'total_questions' => $questions->count(),
                'correct_answers' => $correctAnswers,
                'wrong_answers' => $wrongAnswers,
                'unanswered' => $unanswered,
                'raw_score' => $rawScore,
                'final_score' => $finalScore,
                'pass_score' => $passScore,
                'is_passed' => $finalScore >= $passScore,
                'calculated_at' => now(),
            ]
        );
    }
}
