<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cbt_attempt_id')->unique()->constrained('cbt_attempts')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->unsignedSmallInteger('total_questions');
            $table->unsignedSmallInteger('correct_answers');
            $table->unsignedSmallInteger('wrong_answers');
            $table->unsignedSmallInteger('unanswered');
            $table->decimal('raw_score', 8, 2);
            $table->decimal('final_score', 8, 2);
            $table->decimal('pass_score', 8, 2);
            $table->boolean('is_passed')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->dateTime('calculated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'cbt_exam_id'], 'cbt_results_user_exam_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_results');
    }
};
