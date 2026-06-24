<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Detail Pendaftaran
            </h2>
            <a href="{{ route('registrations.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- INFO PENDAFTARAN --}}
            <div class="bg-white p-6 shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Informasi Pendaftaran
                </h3>

                @php
                    $statusColor = match ($registration->status) {
                        'draft'     => 'bg-gray-100 text-gray-700',
                        'submitted' => 'bg-blue-100 text-blue-700',
                        'verified'  => 'bg-yellow-100 text-yellow-700',
                        'accepted'  => 'bg-green-100 text-green-700',
                        'rejected'  => 'bg-red-100 text-red-700',
                        default     => 'bg-gray-100 text-gray-700',
                    };
                @endphp

                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">No. Registrasi</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-gray-100">{{ $registration->registration_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Pendaftar</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $registration->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($registration->status) }}
                            </span>
                        </dd>
                    </div>
                    @if ($registration->submitted_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Disubmit</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $registration->submitted_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if ($registration->verified_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Diverifikasi</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $registration->verified_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- CHECKLIST DOKUMEN AKADEMIK --}}
            <div class="bg-white p-6 shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    Kelengkapan Dokumen
                </h3>

                @php
                    $docLabels = [
                        'ijazah' => 'Ijazah',
                        'ktp'    => 'KTP',
                        'photo'  => 'Foto',
                    ];
                    $uploadedDocs = $registration->documents->keyBy('type');
                @endphp

                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($requiredDocs as $type)
                        @php $doc = $uploadedDocs->get($type); @endphp
                        <li class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                @if ($doc)
                                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                @else
                                    <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                                @endif
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $docLabels[$type] }}</span>
                            </div>

                            @if ($doc)
                                @php
                                    $docStatusColor = match ($doc->status) {
                                        'approved' => 'bg-green-100 text-green-700',
                                        'rejected' => 'bg-red-100 text-red-700',
                                        default    => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <div class="flex items-center gap-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $docStatusColor }}">
                                        {{ ucfirst($doc->status) }}
                                    </span>
                                    <a href="{{ route('documents.show', $doc) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                        Lihat
                                    </a>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Belum diunggah</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- AKSI UTAMA --}}
            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">

                {{-- Mahasiswa: submit jika draft --}}
                @if (! $canManageAll && $registration->status === 'draft')
                    <form method="POST" action="{{ route('registrations.submit', $registration) }}"
                        onsubmit="return confirm('Submit pendaftaran? Pastikan semua dokumen sudah diunggah.')">
                        @csrf
                        <x-primary-button>Submit Pendaftaran</x-primary-button>
                    </form>
                @endif

                {{-- Admin/staff: verifikasi --}}
                @if ($canManageAll && $registration->status === 'submitted')
                    <form method="POST" action="{{ route('registrations.verify', $registration) }}">
                        @csrf
                        <x-primary-button>Verifikasi</x-primary-button>
                    </form>
                @endif

                {{-- Admin/staff: tolak (kecuali sudah accepted/rejected) --}}
                @if ($canManageAll && ! in_array($registration->status, ['accepted', 'rejected']))
                    <form method="POST" action="{{ route('registrations.reject', $registration) }}"
                        onsubmit="return confirm('Tolak pendaftaran ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        <x-danger-button>Tolak Pendaftaran</x-danger-button>
                    </form>
                @endif

                {{-- Tidak ada aksi --}}
                @if (in_array($registration->status, ['accepted', 'rejected']))
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pendaftaran telah <strong>{{ $registration->status === 'accepted' ? 'diterima' : 'ditolak' }}</strong>. Tidak ada aksi lebih lanjut.
                    </p>
                @endif

                @if (! $canManageAll && $registration->status !== 'draft')
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pendaftaran sedang diproses oleh panitia.
                    </p>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
