<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    assignments: Object,
});

const latestAttempt = (participant) => {
    const attempts = participant.registration?.cbt_attempts || participant.registration?.cbtAttempts || [];
    return attempts[0] || null;
};

const latestAudit = (participant) => {
    const attempt = latestAttempt(participant);
    const logs = attempt?.latest_audit_logs || attempt?.latestAuditLogs || [];
    return logs[0] || null;
};

const attemptClass = (status) => ({
    ongoing: 'bg-blue-100 text-blue-700',
    submitted: 'bg-green-100 text-green-700',
    timed_out: 'bg-red-100 text-red-700',
    pending: 'bg-gray-100 text-gray-700',
    cancelled: 'bg-gray-100 text-gray-700',
}[status] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head title="Ruang Supervisi" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Ruang Supervisi</h1>
                    <p class="mt-1 text-sm text-gray-500">Daftar ruang ujian yang menjadi tanggung jawab pengawas.</p>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Jadwal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ruang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Peserta</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="assignment in assignments.data" :key="assignment.id">
                                    <td class="px-4 py-3 text-sm">{{ assignment.schedule?.title || '-' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ assignment.schedule?.exam_date || '-' }} {{ assignment.schedule?.start_time || '' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ assignment.room?.name || '-' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ assignment.participants_count }} / {{ assignment.max_participants }}</td>
                                    <td class="px-4 py-3 text-sm">{{ assignment.status }}</td>
                                </tr>
                                <tr v-if="assignments.data.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada ruang supervisi.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 grid gap-4">
                    <article v-for="assignment in assignments.data" :key="`monitor-${assignment.id}`" class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">{{ assignment.schedule?.title || '-' }}</h2>
                                <p class="text-sm text-gray-500">{{ assignment.room?.name || '-' }} · {{ assignment.schedule?.cbt_exam?.title || 'Tanpa Paket CBT' }}</p>
                            </div>
                            <span class="w-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Monitoring CBT</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Peserta</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Attempt</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Jawab/Ragu</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Pindah Tab</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Audit Terakhir</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="participant in assignment.participants" :key="participant.id">
                                        <td class="px-4 py-3 text-sm">{{ participant.registration?.user?.name || '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="rounded-full px-2 py-1 text-xs font-medium" :class="attemptClass(latestAttempt(participant)?.status)">
                                                {{ latestAttempt(participant)?.status || 'belum mulai' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ latestAttempt(participant)?.answered_questions || 0 }} / {{ latestAttempt(participant)?.flagged_questions || 0 }}</td>
                                        <td class="px-4 py-3 text-sm">{{ latestAttempt(participant)?.browser_tab_switch_count || 0 }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <div class="font-medium text-gray-800">{{ latestAudit(participant)?.event || '-' }}</div>
                                            <div class="text-xs text-gray-500">{{ latestAudit(participant)?.created_at || '' }}</div>
                                        </td>
                                    </tr>
                                    <tr v-if="assignment.participants.length === 0">
                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada peserta.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
