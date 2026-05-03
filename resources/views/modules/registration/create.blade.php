<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Formulir Pendaftaran PMB
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($academicYears->isEmpty() || $programs->isEmpty())
                <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Pendaftaran belum dibuka. Hubungi panitia PMB untuk informasi lebih lanjut.
                </div>
            @else
                <div class="bg-white p-6 shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <form method="POST" action="{{ route('registrations.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="academic_year_id" value="Tahun Akademik" />
                            <select id="academic_year_id" name="academic_year_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:text-sm">
                                <option value="">-- Pilih Tahun Akademik --</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" @selected(old('academic_year_id') == $year->id)>
                                        {{ $year->label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="program_id" value="Program Studi" />
                            <select id="program_id" name="program_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:text-sm">
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach ($programs as $program)
                                    <option value="{{ $program->id }}" @selected(old('program_id') == $program->id)>
                                        {{ $program->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('program_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                            <a href="{{ route('registrations.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">
                                Batal
                            </a>
                            <x-primary-button>Daftar</x-primary-button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
