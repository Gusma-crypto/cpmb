<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('exam_sessions') && ! Schema::hasTable('exam_time_slots')) {
            Schema::rename('exam_sessions', 'exam_time_slots');
        }

        if (Schema::hasTable('exam_schedules') && Schema::hasColumn('exam_schedules', 'exam_session_id')) {
            Schema::table('exam_schedules', function (Blueprint $table): void {
                $table->dropForeign(['exam_session_id']);
            });

            Schema::table('exam_schedules', function (Blueprint $table): void {
                $table->renameColumn('exam_session_id', 'exam_time_slot_id');
            });

            Schema::table('exam_schedules', function (Blueprint $table): void {
                $table->foreign('exam_time_slot_id')->references('id')->on('exam_time_slots')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('exam_schedules') && Schema::hasColumn('exam_schedules', 'exam_time_slot_id')) {
            Schema::table('exam_schedules', function (Blueprint $table): void {
                $table->dropForeign(['exam_time_slot_id']);
            });

            Schema::table('exam_schedules', function (Blueprint $table): void {
                $table->renameColumn('exam_time_slot_id', 'exam_session_id');
            });

            Schema::table('exam_schedules', function (Blueprint $table): void {
                $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->nullOnDelete();
            });
        }

        if (Schema::hasTable('exam_time_slots') && ! Schema::hasTable('exam_sessions')) {
            Schema::rename('exam_time_slots', 'exam_sessions');
        }
    }
};
