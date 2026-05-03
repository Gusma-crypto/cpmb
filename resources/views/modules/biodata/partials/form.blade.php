@if ($canManageAll)
    <div>
        <x-input-label for="registration_id" value="Pendaftaran" />
        <select id="registration_id" name="registration_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            <option value="">Pilih pendaftaran</option>
            @foreach ($registrations->unique('id') as $registration)
                <option value="{{ $registration->id }}" @selected((string) old('registration_id', $studentBiodata->registration_id) === (string) $registration->id)>
                    {{ $registration->registration_number }} - {{ $registration->user->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('registration_id')" class="mt-2" />
    </div>
@endif

<div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="nik" value="NIK" />
        <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full" :value="old('nik', $studentBiodata->nik)" required />
        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="gender" value="Jenis Kelamin" />
        <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" required>
            <option value="">Pilih jenis kelamin</option>
            <option value="male" @selected(old('gender', $studentBiodata->gender) === 'male')>Laki-laki</option>
            <option value="female" @selected(old('gender', $studentBiodata->gender) === 'female')>Perempuan</option>
        </select>
        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="birth_place" value="Tempat Lahir" />
        <x-text-input id="birth_place" name="birth_place" type="text" class="mt-1 block w-full" :value="old('birth_place', $studentBiodata->birth_place)" required />
        <x-input-error :messages="$errors->get('birth_place')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="birth_date" value="Tanggal Lahir" />
        <x-text-input id="birth_date" name="birth_date" type="date" class="mt-1 block w-full" :value="old('birth_date', optional($studentBiodata->birth_date)->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="religion" value="Agama" />
        <x-text-input id="religion" name="religion" type="text" class="mt-1 block w-full" :value="old('religion', $studentBiodata->religion)" required />
        <x-input-error :messages="$errors->get('religion')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="province" value="Provinsi" />
        <x-text-input id="province" name="province" type="text" class="mt-1 block w-full" :value="old('province', $studentBiodata->province)" required />
        <x-input-error :messages="$errors->get('province')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="city" value="Kota/Kabupaten" />
        <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', $studentBiodata->city)" required />
        <x-input-error :messages="$errors->get('city')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="school_name" value="Asal Sekolah" />
        <x-text-input id="school_name" name="school_name" type="text" class="mt-1 block w-full" :value="old('school_name', $studentBiodata->school_name)" required />
        <x-input-error :messages="$errors->get('school_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="school_graduation_year" value="Tahun Lulus" />
        <x-text-input id="school_graduation_year" name="school_graduation_year" type="number" min="1900" max="{{ date('Y') + 1 }}" class="mt-1 block w-full" :value="old('school_graduation_year', $studentBiodata->school_graduation_year)" required />
        <x-input-error :messages="$errors->get('school_graduation_year')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="parent_name" value="Nama Orang Tua" />
        <x-text-input id="parent_name" name="parent_name" type="text" class="mt-1 block w-full" :value="old('parent_name', $studentBiodata->parent_name)" required />
        <x-input-error :messages="$errors->get('parent_name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="parent_phone" value="Telepon Orang Tua" />
        <x-text-input id="parent_phone" name="parent_phone" type="text" class="mt-1 block w-full" :value="old('parent_phone', $studentBiodata->parent_phone)" required />
        <x-input-error :messages="$errors->get('parent_phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="parent_job" value="Pekerjaan Orang Tua" />
        <x-text-input id="parent_job" name="parent_job" type="text" class="mt-1 block w-full" :value="old('parent_job', $studentBiodata->parent_job)" required />
        <x-input-error :messages="$errors->get('parent_job')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="address" value="Alamat" />
        <textarea id="address" name="address" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" required>{{ old('address', $studentBiodata->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="photo" value="Foto" />
        <input id="photo" name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-700 file:me-4 file:rounded-md file:border-0 file:bg-gray-800 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700 dark:text-gray-300" />
        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
    </div>
</div>

<div class="flex items-center justify-end gap-3">
    <a href="{{ route('biodata.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Batal</a>
    <x-primary-button>Simpan</x-primary-button>
</div>
