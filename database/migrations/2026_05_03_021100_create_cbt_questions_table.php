<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('cbt_question_categories')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['multiple_choice', 'true_false'])->default('multiple_choice');
            $table->longText('question_text');
            $table->text('explanation')->nullable();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'type', 'is_active'], 'cbt_questions_category_type_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_questions');
    }
};
