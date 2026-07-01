<?php

namespace Tests\Feature\CbtAttempt;

use App\Models\User;
use App\Modules\AcademicYear\Models\AcademicYear;
use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtAttempt\Models\CbtResult;
use App\Modules\CbtExam\Models\CbtExam;
use App\Modules\CbtResult\Services\CbtResultService;
use App\Modules\ExamRoom\Models\ExamRoom;
use App\Modules\ExamRoom\Models\ExamRoomAssignment;
use App\Modules\ExamRoom\Models\ParticipantExamAssignment;
use App\Modules\ExamSchedule\Models\ExamSchedule;
use App\Modules\ExamSchedule\Models\ExamTimeSlot;
use App\Modules\Program\Models\Program;
use App\Modules\QuestionBank\Models\CbtQuestion;
use App\Modules\QuestionBank\Models\CbtQuestionCategory;
use App\Modules\Registration\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class CbtAttemptFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_start_cbt_exam(): void
    {
        Carbon::setTestNow('2026-05-03 09:00:00');
        [$user, $schedule] = $this->fixture();

        $response = $this->actingAs($user)->get(route('student.cbt.start', $schedule));

        $attempt = CbtAttempt::first();
        $response->assertRedirect(route('student.cbt.attempt.show', $attempt->uuid));
        $this->assertSame('ongoing', $attempt->status);
        $this->assertSame(1, $attempt->total_questions);
    }

    public function test_student_cannot_start_if_registration_not_exam_ready(): void
    {
        Carbon::setTestNow('2026-05-03 09:00:00');
        [$user, $schedule, $registration] = $this->fixture(['registration_status' => 'verified']);

        $response = $this->actingAs($user)->get(route('student.cbt.start', $schedule));

        $response->assertSessionHasErrors('registration');
        $this->assertDatabaseCount('cbt_attempts', 0);
    }

    public function test_autosave_answer_successfully(): void
    {
        Carbon::setTestNow('2026-05-03 09:00:00');
        [$user, $schedule, $registration, $exam, $question, $correctOption] = $this->fixture();
        $attempt = $this->attempt($user, $registration, $schedule, $exam, [$question->id]);

        $response = $this->actingAs($user)->postJson(route('student.cbt.autosave'), [
            'attempt_uuid' => $attempt->uuid,
            'question_id' => $question->id,
            'option_id' => $correctOption->id,
        ]);

        $response->assertOk()->assertJson(['saved' => true]);
        $this->assertDatabaseHas('cbt_answers', [
            'cbt_attempt_id' => $attempt->id,
            'cbt_question_id' => $question->id,
            'cbt_question_option_id' => $correctOption->id,
        ]);
    }

    public function test_submit_scores_attempt(): void
    {
        Carbon::setTestNow('2026-05-03 09:00:00');
        [$user, $schedule, $registration, $exam, $question, $correctOption] = $this->fixture();
        $attempt = $this->attempt($user, $registration, $schedule, $exam, [$question->id]);

        $this->actingAs($user)->postJson(route('student.cbt.autosave'), [
            'attempt_uuid' => $attempt->uuid,
            'question_id' => $question->id,
            'option_id' => $correctOption->id,
        ])->assertOk();

        $this->actingAs($user)->post(route('student.cbt.submit', $attempt->uuid));

        $this->assertDatabaseHas('cbt_attempts', ['id' => $attempt->id, 'status' => 'submitted']);
        $this->assertDatabaseHas('cbt_results', [
            'cbt_attempt_id' => $attempt->id,
            'correct_answers' => 1,
            'is_passed' => true,
        ]);

        app(CbtResultService::class)->publish(CbtResult::firstOrFail());

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'accepted',
        ]);
    }

    public function test_expired_attempt_is_force_submitted_by_command(): void
    {
        Carbon::setTestNow('2026-05-03 10:31:00');
        [$user, $schedule, $registration, $exam, $question] = $this->fixture();
        $attempt = $this->attempt($user, $registration, $schedule, $exam, [$question->id], [
            'started_at' => now()->subMinutes(31),
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('cbt:close-expired-attempts')->assertExitCode(0);

        $this->assertDatabaseHas('cbt_attempts', [
            'id' => $attempt->id,
            'status' => 'timed_out',
            'force_submitted' => true,
        ]);
        $this->assertDatabaseHas('cbt_results', ['cbt_attempt_id' => $attempt->id]);
    }

    private function fixture(array $overrides = []): array
    {
        $user = User::factory()->create(['role' => 'student', 'is_active' => true]);
        $user->syncLegacyRoleToSpatie();
        $academicYear = AcademicYear::create(['label' => '2026/2027', 'is_active' => true]);
        $program = Program::create(['code' => 'TI', 'name' => 'Teknik Informatika', 'level' => 'D3', 'faculty' => 'Program Diploma', 'is_active' => true]);
        $registration = Registration::create([
            'user_id' => $user->id,
            'academic_year_id' => $academicYear->id,
            'registration_number' => 'REG-' . Str::random(8),
            'status' => $overrides['registration_status'] ?? 'exam_ready',
            'program_id' => $program->id,
            'verified_at' => now(),
        ]);

        $category = CbtQuestionCategory::create(['name' => 'TPA', 'is_active' => true]);
        $question = CbtQuestion::create([
            'category_id' => $category->id,
            'created_by' => $user->id,
            'type' => 'multiple_choice',
            'question_text' => '2 + 2 = ?',
            'difficulty' => 'easy',
            'is_active' => true,
        ]);
        $correctOption = $question->options()->create(['option_text' => '4', 'is_correct' => true, 'order_index' => 1]);
        $question->options()->create(['option_text' => '5', 'is_correct' => false, 'order_index' => 2]);

        $exam = CbtExam::create([
            'title' => 'Tes CBT',
            'academic_year_id' => $academicYear->id,
            'program_id' => $program->id,
            'duration_minutes' => 30,
            'pass_score' => 60,
            'max_attempts' => 1,
            'status' => 'published',
            'created_by' => $user->id,
        ]);
        $exam->questions()->attach($question->id, ['order_index' => 1, 'points' => 1]);

        $timeSlot = ExamTimeSlot::create(['name' => 'Sesi Test', 'start_time' => '08:00', 'end_time' => '10:30']);
        $schedule = ExamSchedule::create([
            'title' => 'Jadwal CBT',
            'code' => 'CBT-01',
            'exam_type' => 'online',
            'exam_date' => '2026-05-03',
            'start_time' => '08:00',
            'end_time' => '10:30',
            'duration_minutes' => 150,
            'exam_time_slot_id' => $timeSlot->id,
            'session_name' => 'Sesi Test',
            'cbt_exam_id' => $exam->id,
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $room = ExamRoom::create(['code' => 'LAB1', 'name' => 'Lab 1', 'capacity' => 30, 'status' => 'active']);
        $assignment = ExamRoomAssignment::create([
            'exam_room_id' => $room->id,
            'exam_schedule_id' => $schedule->id,
            'max_participants' => 30,
            'status' => 'active',
        ]);
        ParticipantExamAssignment::create([
            'registration_id' => $registration->id,
            'user_id' => $user->id,
            'exam_schedule_id' => $schedule->id,
            'exam_room_assignment_id' => $assignment->id,
            'participant_number' => 'CBT-00001',
            'status' => 'assigned',
        ]);

        return [$user, $schedule, $registration, $exam, $question, $correctOption];
    }

    private function attempt(User $user, Registration $registration, ExamSchedule $schedule, CbtExam $exam, array $questionOrder, array $overrides = []): CbtAttempt
    {
        return CbtAttempt::create([
            'uuid' => (string) Str::uuid(),
            'registration_id' => $registration->id,
            'user_id' => $user->id,
            'exam_schedule_id' => $schedule->id,
            'cbt_exam_id' => $exam->id,
            'token' => Str::random(64),
            'status' => 'ongoing',
            'started_at' => now(),
            'expires_at' => now()->addMinutes(30),
            'total_questions' => count($questionOrder),
            'question_order' => $questionOrder,
            ...$overrides,
        ]);
    }
}
