<?php

namespace App\Modules\Biodata\Requests;

use App\Modules\Biodata\Models\StudentBiodata;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentBiodataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $biodata = $this->route('student_biodata');
        $biodataId = $biodata instanceof StudentBiodata ? $biodata->id : $biodata;

        return [
            'registration_id' => [
                Rule::requiredIf(fn () => $this->user()?->hasAnyRole(['admin', 'staff', 'superadmin']) ?? false),
                'nullable',
                'integer',
                'exists:registrations,id',
                Rule::unique('student_biodata', 'registration_id')->ignore($biodataId),
            ],
            'nik' => ['required', 'string', 'max:20', Rule::unique('student_biodata', 'nik')->ignore($biodataId)],
            'birth_place' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'religion' => ['required', 'string', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])],
            'address' => ['required', 'string'],
            'province' => ['required', 'string', Rule::in([
                'Aceh',
                'Sumatera Utara',
                'Sumatera Barat',
                'Riau',
                'Kepulauan Riau',
                'Jambi',
                'Sumatera Selatan',
                'Bangka Belitung',
                'Bengkulu',
                'Lampung',
                'DKI Jakarta',
                'Banten',
                'Jawa Barat',
                'Jawa Tengah',
                'DI Yogyakarta',
                'Jawa Timur',
                'Bali',
                'Nusa Tenggara Barat',
                'Nusa Tenggara Timur',
                'Kalimantan Barat',
                'Kalimantan Tengah',
                'Kalimantan Selatan',
                'Kalimantan Timur',
                'Kalimantan Utara',
                'Sulawesi Utara',
                'Gorontalo',
                'Sulawesi Tengah',
                'Sulawesi Barat',
                'Sulawesi Selatan',
                'Sulawesi Tenggara',
                'Maluku',
                'Maluku Utara',
                'Papua',
                'Papua Barat',
                'Papua Barat Daya',
                'Papua Pegunungan',
                'Papua Selatan',
                'Papua Tengah',
            ])],
            'city' => ['required', 'string', 'max:255'],
            'school_name' => ['required', 'string', 'max:255'],
            'school_graduation_year' => ['required', 'integer', 'digits:4', 'min:'.((int) date('Y') - 9), 'max:'.((int) date('Y') + 1)],
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_phone' => ['required', 'string', 'max:30', 'regex:/^(-|08[0-9]{7,13}|07[0-9]{6,12}|\+62[0-9]{8,13})$/'],
            'parent_job' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'parent_phone.required' => 'Telepon orang tua wajib diisi. Isi - jika tidak ada nomor.',
            'parent_phone.regex' => 'Telepon orang tua boleh diisi -, nomor HP harus diawali 08 atau +62, dan nomor telepon rumah harus diawali 07.',
        ];
    }
}
