<?php

namespace App\Modules\Program\Services;

use App\Modules\Program\Models\Program;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProgramService
{
    public function paginate(?string $search = null): LengthAwarePaginator
    {
        return Program::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('level', 'like', "%{$search}%")
                        ->orWhere('faculty', 'like', "%{$search}%")
                        ->orWhere('accreditation', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
    }

    public function create(array $data): Program
    {
        return DB::transaction(function () use ($data): Program {
            $program = Program::create($this->normalize($data));
            $this->ensureWaveRelations($program);

            return $program;
        });
    }

    public function update(Program $program, array $data): Program
    {
        DB::transaction(function () use ($program, $data): void {
            $program->update($this->normalize($data));
            $this->ensureWaveRelations($program->refresh());
        });

        return $program->refresh();
    }

    public function delete(Program $program): void
    {
        $program->delete();
    }

    private function normalize(array $data): array
    {
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        return $data;
    }

    private function ensureWaveRelations(Program $program): void
    {
        if (! Schema::hasTable('gelombang_program') || ! Schema::hasTable('registration_waves')) {
            return;
        }

        $now = now();
        $waves = DB::table('registration_waves')->get(['id', 'open_at', 'close_at']);

        foreach ($waves as $wave) {
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
