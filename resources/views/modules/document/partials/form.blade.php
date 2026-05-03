@if ($canManageAll)
    <div>
        <x-input-label for="registration_id" value="Pendaftaran" />
        <select id="registration_id" name="registration_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            <option value="">Pilih pendaftaran</option>
            @foreach ($registrations as $registration)
                <option value="{{ $registration->id }}" @selected((string) old('registration_id') === (string) $registration->id)>
                    {{ $registration->registration_number }} - {{ $registration->user->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('registration_id')" class="mt-2" />
    </div>
@endif

<div>
    <x-input-label for="type" value="Jenis Dokumen" />
    <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300" required>
        <option value="">Pilih jenis dokumen</option>
        @foreach ($types as $value => $label)
            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('type')" class="mt-2" />
</div>

<div>
    <x-input-label for="file" value="File PDF/JPG" />
    <input id="file" name="file" type="file" accept=".pdf,.jpg,.jpeg,application/pdf,image/jpeg" class="mt-1 block w-full text-sm text-gray-700 file:me-4 file:rounded-md file:border-0 file:bg-gray-800 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-700 dark:text-gray-300" required />
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Format PDF/JPG, maksimal 2 MB.</p>
    <x-input-error :messages="$errors->get('file')" class="mt-2" />
</div>

<div class="flex items-center justify-end gap-3">
    <a href="{{ route('documents.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Batal</a>
    <x-primary-button>Upload</x-primary-button>
</div>
