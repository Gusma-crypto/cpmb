<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    schedule: { type: Object, required: true },
    routePrefix: { type: String, default: 'admin' },
});

const routeName = (name, params) => route(`${props.routePrefix}.exam-schedules.${name}`, params);
const formatDate = (value) => value ? new Date(value).toLocaleDateString('id-ID', { dateStyle: 'full' }) : '-';
</script>

<template>
    <Head title="Detail Jadwal Ujian" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Detail Jadwal Ujian</h1>
                    <div class="flex gap-3">
                        <Link :href="routeName('edit', schedule.id)" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Edit</Link>
                        <Link :href="routeName('index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Kembali</Link>
                    </div>
                </div>

                <div class="grid gap-6">
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ schedule.title }}</h2>
                        <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                            <div><dt class="font-medium text-gray-500">Kode</dt><dd class="font-mono text-gray-800">{{ schedule.code }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Tanggal</dt><dd class="text-gray-800">{{ formatDate(schedule.exam_date) }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Waktu</dt><dd class="text-gray-800">{{ schedule.start_time }} - {{ schedule.end_time }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Sesi</dt><dd class="text-gray-800">{{ schedule.session_name }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Tipe Ujian</dt><dd class="text-gray-800">{{ schedule.exam_type }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Status</dt><dd class="text-gray-800">{{ schedule.status }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
