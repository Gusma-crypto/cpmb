<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programs') && Schema::hasColumn('programs', 'quota')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->dropColumn('quota');
            });
        }

        if (Schema::hasTable('registration_waves') && Schema::hasColumn('registration_waves', 'quota')) {
            Schema::table('registration_waves', function (Blueprint $table): void {
                $table->dropColumn('quota');
            });
        }

        if (Schema::hasTable('gelombang_program') && Schema::hasColumn('gelombang_program', 'kuota')) {
            Schema::table('gelombang_program', function (Blueprint $table): void {
                $table->dropColumn('kuota');
            });
        }

        if (Schema::hasTable('registration_waves') && Schema::hasColumn('registration_waves', 'kapasitas_total')) {
            Schema::table('registration_waves', function (Blueprint $table): void {
                $table->dropColumn('kapasitas_total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('programs') && ! Schema::hasColumn('programs', 'quota')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->unsignedInteger('quota')->default(0)->after('name');
            });
        }

        if (Schema::hasTable('registration_waves') && ! Schema::hasColumn('registration_waves', 'quota')) {
            Schema::table('registration_waves', function (Blueprint $table): void {
                $table->unsignedInteger('quota')->nullable()->after('close_at');
            });
        }

        if (Schema::hasTable('registration_waves') && ! Schema::hasColumn('registration_waves', 'kapasitas_total')) {
            Schema::table('registration_waves', function (Blueprint $table): void {
                $table->unsignedInteger('kapasitas_total')->nullable()->after('quota');
            });
        }

        if (Schema::hasTable('gelombang_program') && ! Schema::hasColumn('gelombang_program', 'kuota')) {
            Schema::table('gelombang_program', function (Blueprint $table): void {
                $table->unsignedInteger('kuota')->default(0)->after('program_id');
            });
        }
    }
};
