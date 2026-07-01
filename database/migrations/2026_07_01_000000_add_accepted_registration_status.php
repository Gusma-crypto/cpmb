<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY status ENUM('draft','submitted','under_review','revision_required','verified','rejected','exam_ready','accepted') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        DB::table('registrations')
            ->where('status', 'accepted')
            ->update(['status' => 'exam_ready']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY status ENUM('draft','submitted','under_review','revision_required','verified','rejected','exam_ready') NOT NULL DEFAULT 'draft'");
        }
    }
};
