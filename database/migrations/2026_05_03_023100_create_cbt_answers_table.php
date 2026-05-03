<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cbt_attempt_id')->constrained('cbt_attempts')->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->foreignId('cbt_question_option_id')->nullable()->constrained('cbt_question_options')->nullOnDelete();
            $table->text('answer_text')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->dateTime('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['cbt_attempt_id', 'cbt_question_id'], 'cbt_attempt_question_unique');
            $table->index(['cbt_attempt_id', 'is_flagged'], 'cbt_answers_attempt_flag_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_answers');
    }
};
