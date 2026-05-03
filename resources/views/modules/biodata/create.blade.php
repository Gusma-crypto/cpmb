<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Tambah Biodata Mahasiswa
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if (!$canManageAll && $registrations->isEmpty())
                <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Anda belum memiliki data pendaftaran yang dapat diisi biodatanya.
                    <a href="{{ route('registrations.create') }}" class="font-semibold underline">
                        Buat pendaftaran terlebih dahulu.
                    </a>
                </div>
            @else
                <div class="bg-white p-6 shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <form method="POST" action="{{ route('biodata.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @include('modules.biodata.partials.form')
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
