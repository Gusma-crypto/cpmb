<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    schedules: { type: Array, required: true },
});

const formatDate = (value) => value ? new Date(value).toLocaleDateString('id-ID', { dateStyle: 'full' }) : '-';
const canStartCbt = (schedule) => Boolean(schedule.cbt_exam_id) && schedule.status === 'active';
const cbtMessage = (schedule) => {
    if (!schedule.cbt_exam_id) return 'Jadwal ini belum dihubungkan ke Paket CBT oleh admin.';
    if (schedule.status !== 'active') return 'Jadwal ujian belum aktif.';

    return '';
};
</script>

<template>
    <Head title="Jadwal Ujian Saya" />

    <StudentLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Jadwal Ujian Saya</h1>
                    <p class="mt-1 text-sm text-gray-500">Informasi jadwal ujian PMB yang sudah ditetapkan panitia.</p>
                </div>

                <div v-if="schedules.length === 0" class="rounded-lg bg-white p-8 text-center shadow-sm">
                    <p class="text-sm text-gray-500">Belum ada jadwal ujian untuk akun Anda.</p>
                </div>

                <div v-else class="grid gap-4">
                    <article v-for="schedule in schedules" :key="schedule.id" class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">{{ schedule.title }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ schedule.code }} · {{ schedule.exam_type }}</p>
                            </div>
                            <span class="w-fit rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">{{ schedule.status }}</span>
                        </div>

                        <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <dt class="font-medium text-gray-500">Tanggal</dt>
                                <dd class="mt-1 text-gray-800">{{ formatDate(schedule.exam_date) }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Sesi & Waktu</dt>
                                <dd class="mt-1 text-gray-800">{{ schedule.session_name }}<br>{{ schedule.start_time }} - {{ schedule.end_time }}</dd>
                            </div>
                        </dl>

                        <div v-if="schedule.cbt_exam_id" class="mt-6 flex justify-end">
                            <Link
                                :href="route('student.cbt.start', schedule.id)"
                                class="rounded-md px-4 py-2 text-sm font-semibold"
                                :class="canStartCbt(schedule) ? 'bg-blue-600 text-white hover:bg-blue-700' : 'pointer-events-none bg-gray-100 text-gray-400'"
                            >
                                Mulai CBT
                            </Link>
                        </div>
                        <p v-if="cbtMessage(schedule)" class="mt-6 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ cbtMessage(schedule) }}
                        </p>
                    </article>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
