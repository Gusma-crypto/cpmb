<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    registrationWaves: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '' }) },
});

const search = ref(props.filters.search || '');

const submitSearch = () => {
    router.get(route('admin.registration-waves.index'), { search: search.value }, { preserveState: true, replace: true });
};

const formatDate = (value) => value ? new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';

const destroy = (registrationWave) => {
    if (registrationWave.registrations_count > 0) {
        return;
    }

    if (confirm(`Hapus ${registrationWave.label}?`)) {
        router.delete(route('admin.registration-waves.destroy', registrationWave.id));
    }
};
</script>

<template>
    <Head title="Registration Waves" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Registration Waves</h1>
                    <Link :href="route('admin.registration-waves.create')" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                        Tambah Gelombang
                    </Link>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="border-b border-gray-200 p-4" @submit.prevent="submitSearch">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input v-model="search" type="search" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari label, nomor, atau tahun ajaran" />
                            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Tahun Ajaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Gelombang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Periode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Dipakai</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="registrationWave in registrationWaves.data" :key="registrationWave.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ registrationWave.academic_year?.label || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        <span class="font-medium text-gray-900">{{ registrationWave.label }}</span>
                                        <span class="ms-2 text-gray-500">#{{ registrationWave.wave_number }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ formatDate(registrationWave.open_at) }} - {{ formatDate(registrationWave.close_at) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ registrationWave.registrations_count || 0 }} pendaftar</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="registrationWave.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                            {{ registrationWave.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('admin.registration-waves.edit', registrationWave.id)" class="font-medium text-blue-600 hover:text-blue-900">Edit</Link>
                                        <button
                                            type="button"
                                            class="ms-4 font-medium"
                                            :class="registrationWave.registrations_count > 0 ? 'cursor-not-allowed text-gray-400' : 'text-red-600 hover:text-red-900'"
                                            :disabled="registrationWave.registrations_count > 0"
                                            :title="registrationWave.registrations_count > 0 ? 'Tidak dapat dihapus karena sudah digunakan calon mahasiswa' : 'Hapus gelombang'"
                                            @click="destroy(registrationWave)"
                                        >
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="registrationWaves.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada gelombang pendaftaran.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link v-for="link in registrationWaves.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
