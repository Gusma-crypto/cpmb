<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_attempts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->string('token')->nullable();
            $table->enum('status', ['pending', 'ongoing', 'submitted', 'timed_out', 'cancelled'])->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->unsignedSmallInteger('total_questions')->default(0);
            $table->unsignedSmallInteger('answered_questions')->default(0);
            $table->unsignedSmallInteger('flagged_questions')->default(0);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedSmallInteger('browser_tab_switch_count')->default(0);
            $table->boolean('force_submitted')->default(false);
            $table->json('question_order')->nullable();
            $table->json('option_order')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status'], 'cbt_attempts_user_status_idx');
            $table->index(['exam_schedule_id', 'cbt_exam_id'], 'cbt_attempts_schedule_exam_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_attempts');
    }
};
