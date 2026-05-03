<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Upload Dokumen PMB
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if (!$canManageAll && $registrations->isEmpty())
                <div class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Anda belum memiliki data pendaftaran untuk upload dokumen.
                </div>
            @else
                <div class="bg-white p-6 shadow-sm dark:bg-gray-800 sm:rounded-lg">
                    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @include('modules.document.partials.form')
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
