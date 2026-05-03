<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    schedules: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    routePrefix: { type: String, default: 'admin' },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const examType = ref(props.filters.exam_type || '');

const routeName = (name, params) => route(`${props.routePrefix}.exam-schedules.${name}`, params);

const submitSearch = () => {
    router.get(routeName('index'), {
        search: search.value,
        status: status.value,
        exam_type: examType.value,
    }, { preserveState: true, replace: true });
};

const destroy = (schedule) => {
    if (scheduleLocked(schedule)) return;

    if (confirm(`Hapus jadwal ${schedule.title}?`)) {
        router.delete(routeName('destroy', schedule.id));
    }
};

const scheduleLocked = (schedule) => Number(schedule.room_assignments_count || 0) > 0;

const formatDate = (value) => value ? new Date(value).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-';

const statusClass = (value) => ({
    draft: 'bg-gray-100 text-gray-700',
    active: 'bg-green-100 text-green-700',
    finished: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-red-100 text-red-700',
}[value] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head title="Jadwal Ujian" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Jadwal Ujian</h1>
                    <Link :href="routeName('create')" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                        Tambah Jadwal
                    </Link>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="border-b border-gray-200 p-4" @submit.prevent="submitSearch">
                        <div class="grid gap-3 md:grid-cols-[1fr_160px_160px_auto]">
                            <input v-model="search" type="search" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari kode, judul, sesi" />
                            <select v-model="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua status</option>
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="finished">Finished</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <select v-model="examType" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua tipe</option>
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Kode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Jadwal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Paket CBT</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Peserta Ditempatkan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="schedule in schedules.data" :key="schedule.id">
                                    <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-700">{{ schedule.code }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div class="font-medium text-gray-900">{{ schedule.title }}</div>
                                        <div class="text-xs text-gray-500">{{ schedule.exam_type }} · {{ schedule.session_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span v-if="schedule.cbt_exam" class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">
                                            {{ schedule.cbt_exam.title }}
                                        </span>
                                        <span v-else class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">
                                            Belum dipilih
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ formatDate(schedule.exam_date) }}<br>{{ schedule.start_time }} - {{ schedule.end_time }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ schedule.participants_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="statusClass(schedule.status)">{{ schedule.status }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="routeName('show', schedule.id)" class="font-medium text-indigo-600 hover:text-indigo-900">Detail</Link>
                                        <Link :href="routeName('edit', schedule.id)" class="ms-4 font-medium text-blue-600 hover:text-blue-900">Edit</Link>
                                        <button type="button" class="ms-4 font-medium" :class="scheduleLocked(schedule) ? 'cursor-not-allowed text-gray-400' : 'text-red-600 hover:text-red-900'" :title="scheduleLocked(schedule) ? 'Jadwal masih dipakai penempatan peserta' : 'Hapus jadwal'" @click="destroy(schedule)">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="schedules.data.length === 0">
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada jadwal ujian.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link v-for="link in schedules.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
