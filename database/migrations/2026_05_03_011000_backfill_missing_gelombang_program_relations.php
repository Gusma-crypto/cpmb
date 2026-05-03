<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gelombang_program')) {
            return;
        }

        $now = now();
        $waves = DB::table('registration_waves')->get(['id', 'open_at', 'close_at']);
        $programs = DB::table('programs')->get(['id']);

        foreach ($waves as $wave) {
            foreach ($programs as $program) {
                $exists = DB::table('gelombang_program')
                    ->where('registration_wave_id', $wave->id)
                    ->where('program_id', $program->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('gelombang_program')->insert([
                    'registration_wave_id' => $wave->id,
                    'program_id' => $program->id,
                    'status' => 'nonaktif',
                    'is_open' => false,
                    'tanggal_mulai' => $wave->open_at,
                    'tanggal_selesai' => $wave->close_at,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
