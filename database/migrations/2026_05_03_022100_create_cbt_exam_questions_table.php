<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_exam_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cbt_exam_id')->constrained('cbt_exams')->cascadeOnDelete();
            $table->foreignId('cbt_question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->unsignedSmallInteger('points')->default(1);
            $table->timestamps();

            $table->unique(['cbt_exam_id', 'cbt_question_id'], 'cbt_exam_question_unique');
            $table->index(['cbt_exam_id', 'order_index'], 'cbt_exam_questions_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_exam_questions');
    }
};
