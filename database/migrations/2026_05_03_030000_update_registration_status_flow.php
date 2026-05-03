<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            if (! Schema::hasColumn('registrations', 'revision_notes')) {
                $table->text('revision_notes')->nullable()->after('payment_ref');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY status ENUM('draft','submitted','under_review','revision_required','verified','accepted','rejected','exam_ready') NOT NULL DEFAULT 'draft'");
        }

        DB::table('registrations')
            ->where('status', 'accepted')
            ->update(['status' => 'exam_ready']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY status ENUM('draft','submitted','under_review','revision_required','verified','rejected','exam_ready') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        DB::table('registrations')
            ->whereIn('status', ['under_review', 'revision_required'])
            ->update(['status' => 'submitted']);

        DB::table('registrations')
            ->where('status', 'exam_ready')
            ->update(['status' => 'accepted']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE registrations MODIFY status ENUM('draft','submitted','verified','accepted','rejected') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('registrations', function (Blueprint $table): void {
            if (Schema::hasColumn('registrations', 'revision_notes')) {
                $table->dropColumn('revision_notes');
            }
        });
    }
};
