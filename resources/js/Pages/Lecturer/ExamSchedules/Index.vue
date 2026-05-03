<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    schedules: { type: Object, required: true },
});

const formatDate = (value) => value ? new Date(value).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '-';
</script>

<template>
    <Head title="Jadwal Supervisi Ujian" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Jadwal Supervisi Ujian</h1>
                    <p class="mt-1 text-sm text-gray-500">Daftar jadwal ujian yang ditugaskan kepada Anda sebagai pengawas.</p>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Kode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Jadwal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Peserta</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="schedule in schedules.data" :key="schedule.id">
                                    <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-700">{{ schedule.code }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div class="font-medium text-gray-900">{{ schedule.title }}</div>
                                        <div class="text-xs text-gray-500">{{ schedule.exam_type }} · {{ schedule.session_name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ formatDate(schedule.exam_date) }}<br>{{ schedule.start_time }} - {{ schedule.end_time }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ schedule.participants_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ schedule.status }}</td>
                                </tr>
                                <tr v-if="schedules.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada jadwal supervisi.</td>
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
