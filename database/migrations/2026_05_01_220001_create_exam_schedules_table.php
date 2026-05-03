<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code')->unique();
            $table->enum('exam_type', ['offline', 'online', 'hybrid'])->default('offline');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('duration_minutes');
            $table->foreignId('exam_time_slot_id')->nullable()->constrained('exam_time_slots')->nullOnDelete();
            $table->string('session_name');
            $table->string('room_name');
            $table->string('room_location')->nullable();
            $table->unsignedBigInteger('cbt_exam_id')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'active', 'finished', 'cancelled'])->default('draft');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['exam_date', 'room_name', 'start_time', 'end_time'], 'exam_sched_room_time_idx');
            $table->index(['exam_date', 'supervisor_id', 'start_time', 'end_time'], 'exam_sched_supervisor_time_idx');
            $table->index(['exam_type', 'status'], 'exam_sched_type_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
