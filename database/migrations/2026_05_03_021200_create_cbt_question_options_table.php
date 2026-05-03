<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('question_id')->constrained('cbt_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->timestamps();

            $table->index(['question_id', 'is_correct'], 'cbt_question_options_question_correct_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_question_options');
    }
};
