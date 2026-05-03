<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Detail Biodata Mahasiswa
            </h2>
            <a href="{{ route('biodata.edit', $studentBiodata) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white">
                Edit
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    @foreach ([
                        'No. Registrasi' => $studentBiodata->registration->registration_number,
                        'Nama' => $studentBiodata->registration->user->name,
                        'NIK' => $studentBiodata->nik,
                        'Tempat, Tanggal Lahir' => $studentBiodata->birth_place.', '.$studentBiodata->birth_date->format('d/m/Y'),
                        'Jenis Kelamin' => $studentBiodata->gender === 'male' ? 'Laki-laki' : 'Perempuan',
                        'Agama' => $studentBiodata->religion,
                        'Provinsi' => $studentBiodata->province,
                        'Kota' => $studentBiodata->city,
                        'Sekolah' => $studentBiodata->school_name,
                        'Tahun Lulus' => $studentBiodata->school_graduation_year,
                        'Nama Orang Tua' => $studentBiodata->parent_name,
                        'Telepon Orang Tua' => $studentBiodata->parent_phone,
                        'Pekerjaan Orang Tua' => $studentBiodata->parent_job,
                    ] as $label => $value)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $value }}</dd>
                        </div>
                    @endforeach

                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $studentBiodata->address }}</dd>
                    </div>
                </dl>

                <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6 dark:border-gray-700">
                    <a href="{{ route('biodata.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Kembali</a>
                    <form method="POST" action="{{ route('biodata.destroy', $studentBiodata) }}" onsubmit="return confirm('Hapus biodata ini?')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>Hapus</x-danger-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
