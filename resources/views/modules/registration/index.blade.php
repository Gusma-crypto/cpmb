<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Data Pendaftaran (PMB)
            </h2>
            <a href="{{ route('registrations.create') }}" class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700 dark:bg-gray-200 dark:text-gray-800 dark:hover:bg-white">
                Daftar Sekarang
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">No. Registrasi</th>
                                @if ($canManageAll)
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Didaftarkan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            @forelse ($registrations as $reg)
                                @php
                                    $statusColor = match ($reg->status) {
                                        'draft'     => 'bg-gray-100 text-gray-700',
                                        'submitted' => 'bg-blue-100 text-blue-700',
                                        'verified'  => 'bg-yellow-100 text-yellow-700',
                                        'accepted'  => 'bg-green-100 text-green-700',
                                        'rejected'  => 'bg-red-100 text-red-700',
                                        default     => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-700 dark:text-gray-200">
                                        {{ $reg->registration_number }}
                                    </td>
                                    @if ($canManageAll)
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-200">
                                            {{ $reg->user->name }}
                                        </td>
                                    @endif
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusColor }}">
                                            {{ ucfirst($reg->status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ $reg->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <a href="{{ route('registrations.show', $reg) }}" class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageAll ? 5 : 4 }}" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Belum ada data pendaftaran.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                    {{ $registrations->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
