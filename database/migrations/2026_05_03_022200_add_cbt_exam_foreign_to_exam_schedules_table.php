<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('exam_schedules')) {
            return;
        }

        if (! Schema::hasColumn('exam_schedules', 'cbt_exam_id')) {
            Schema::table('exam_schedules', function (Blueprint $table): void {
                $table->foreignId('cbt_exam_id')->nullable()->after('session_name')->constrained('cbt_exams')->nullOnDelete();
            });

            return;
        }

        DB::table('exam_schedules')
            ->whereNotNull('cbt_exam_id')
            ->whereNotIn('cbt_exam_id', DB::table('cbt_exams')->select('id'))
            ->update(['cbt_exam_id' => null]);

        Schema::table('exam_schedules', function (Blueprint $table): void {
            $table->foreign('cbt_exam_id', 'exam_schedules_cbt_exam_id_foreign')
                ->references('id')
                ->on('cbt_exams')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('exam_schedules') || ! Schema::hasColumn('exam_schedules', 'cbt_exam_id')) {
            return;
        }

        Schema::table('exam_schedules', function (Blueprint $table): void {
            $table->dropForeign('exam_schedules_cbt_exam_id_foreign');
        });
    }
};
