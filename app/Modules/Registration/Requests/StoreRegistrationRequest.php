<?php

namespace App\Modules\Registration\Requests;

use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

class StoreRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'registration_wave_id' => ['required', 'integer', 'exists:registration_waves,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'program_id'       => ['required', 'integer', 'exists:programs,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $wave = RegistrationWave::query()->find($this->integer('registration_wave_id'));

            if (! $wave) {
                return;
            }

            if (! $wave->is_active || $wave->open_at->isFuture() || $wave->close_at->isPast()) {
                $validator->errors()->add('registration_wave_id', 'Gelombang pendaftaran belum dibuka atau sudah ditutup.');
            }

            if ((int) $this->input('academic_year_id') !== (int) $wave->academic_year_id) {
                $validator->errors()->add('academic_year_id', 'Tahun akademik tidak sesuai dengan gelombang pendaftaran.');
            }

            $pivot = DB::table('gelombang_program')
                ->where('registration_wave_id', $wave->id)
                ->where('program_id', $this->integer('program_id'))
                ->first();

            if (! $pivot || $pivot->status !== 'aktif' || ! (bool) $pivot->is_open) {
                $validator->errors()->add('program_id', 'Program studi tidak dibuka pada gelombang ini.');

                return;
            }

            if (($pivot->tanggal_mulai && now()->lt(Carbon::parse($pivot->tanggal_mulai))) || ($pivot->tanggal_selesai && now()->gt(Carbon::parse($pivot->tanggal_selesai)))) {
                $validator->errors()->add('program_id', 'Periode pendaftaran program studi belum dibuka atau sudah ditutup.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'registration_wave_id.required' => 'Gelombang pendaftaran harus dipilih.',
            'registration_wave_id.exists' => 'Gelombang pendaftaran tidak valid.',
            'academic_year_id.required' => 'Tahun akademik harus dipilih.',
            'academic_year_id.exists'   => 'Tahun akademik tidak valid.',
            'program_id.required'       => 'Program studi harus dipilih.',
            'program_id.exists'         => 'Program studi tidak valid.',
        ];
    }
}
