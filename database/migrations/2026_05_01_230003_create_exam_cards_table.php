<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->cascadeOnDelete();
            $table->foreignId('exam_room_assignment_id')->constrained('exam_room_assignments')->cascadeOnDelete();
            $table->string('participant_number');
            $table->string('card_number')->unique();
            $table->string('verification_code')->unique();
            $table->string('qr_code_path')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->unique(['registration_id', 'exam_schedule_id'], 'exam_card_registration_schedule_unique');
            $table->index(['user_id', 'status'], 'exam_card_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_cards');
    }
};
