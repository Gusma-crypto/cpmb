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
            $table->foreignId('registration_wave_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained('registration_waves')
                ->nullOnDelete();
        });

        Schema::create('gelombang_program', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_wave_id')->constrained('registration_waves')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->boolean('is_open')->default(true);
            $table->dateTime('tanggal_mulai')->nullable();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->unique(['registration_wave_id', 'program_id'], 'wave_program_unique');
            $table->index(['status', 'is_open'], 'wave_program_status_open_idx');
        });

        $waves = DB::table('registration_waves')->get();
        $programs = DB::table('programs')->get();
        $now = now();

        foreach ($waves as $wave) {
            foreach ($programs as $program) {
                DB::table('gelombang_program')->updateOrInsert(
                    [
                        'registration_wave_id' => $wave->id,
                        'program_id' => $program->id,
                    ],
                    [
                        'status' => ($program->is_active ?? true) ? 'aktif' : 'nonaktif',
                        'is_open' => true,
                        'tanggal_mulai' => $wave->open_at,
                        'tanggal_selesai' => $wave->close_at,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        foreach ($waves as $wave) {
            DB::table('registrations')
                ->where('academic_year_id', $wave->academic_year_id)
                ->where('wave', $wave->wave_number)
                ->whereNull('registration_wave_id')
                ->update(['registration_wave_id' => $wave->id]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gelombang_program');

        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('registration_wave_id');
        });

        if (Schema::hasColumn('registration_waves', 'kapasitas_total')) {
            Schema::table('registration_waves', function (Blueprint $table): void {
                $table->dropColumn('kapasitas_total');
            });
        }
    }
};
