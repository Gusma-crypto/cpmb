<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_room_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_room_id')->constrained('exam_rooms')->cascadeOnDelete();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('max_participants');
            $table->enum('status', ['draft', 'active', 'finished', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->unique(['exam_room_id', 'exam_schedule_id'], 'exam_room_assignment_unique');
            $table->index(['exam_schedule_id', 'status'], 'exam_room_assignment_schedule_status_idx');
            $table->index(['supervisor_id', 'status'], 'exam_room_assignment_supervisor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_room_assignments');
    }
};
