<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_exam_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->cascadeOnDelete();
            $table->foreignId('exam_room_assignment_id')->constrained('exam_room_assignments')->cascadeOnDelete();
            $table->string('participant_number')->unique();
            $table->enum('status', ['assigned', 'present', 'absent', 'completed'])->default('assigned');
            $table->timestamps();

            $table->unique(['registration_id', 'exam_schedule_id'], 'participant_exam_schedule_unique');
            $table->index(['user_id', 'status'], 'participant_exam_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_exam_assignments');
    }
};
