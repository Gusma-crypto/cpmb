<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gelombang_program') || ! Schema::hasTable('registration_waves')) {
            return;
        }

        DB::table('gelombang_program')
            ->join('registration_waves', 'registration_waves.id', '=', 'gelombang_program.registration_wave_id')
            ->update([
                'gelombang_program.tanggal_mulai' => DB::raw('registration_waves.open_at'),
                'gelombang_program.tanggal_selesai' => DB::raw('registration_waves.close_at'),
                'gelombang_program.updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
