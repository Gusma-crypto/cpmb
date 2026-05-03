<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Detail Dokumen PMB
            </h2>
            <a href="{{ route('documents.download', $document) }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white">
                Download
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white p-6 shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">No. Registrasi</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->registration->registration_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->registration->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Dokumen</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $types[$document->type] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($document->status) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama File</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->original_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ukuran</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->size_kb }} KB</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">MIME</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->mime_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Diunggah</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $document->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>

                <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6 dark:border-gray-700">
                    <a href="{{ route('documents.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">Kembali</a>
                    <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Hapus dokumen ini?')">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>Hapus</x-danger-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
