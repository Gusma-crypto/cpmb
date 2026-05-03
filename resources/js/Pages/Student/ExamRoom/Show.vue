<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    assignments: Array,
});
</script>

<template>
    <Head title="Ruang Ujian" />

    <StudentLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Ruang Ujian</h1>
                    <p class="mt-1 text-sm text-gray-500">Informasi ruang dan jadwal ujian Anda.</p>
                </div>

                <div class="grid gap-4">
                    <div v-for="assignment in assignments" :key="assignment.id" class="bg-white p-6 shadow-sm sm:rounded-lg">
                        <div class="flex flex-wrap justify-between gap-4">
                            <div>
                                <p class="font-mono text-sm text-blue-600">{{ assignment.participant_number }}</p>
                                <h2 class="mt-1 text-lg font-semibold text-gray-900">{{ assignment.room_assignment?.schedule?.title || '-' }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ assignment.registration?.registration_number || '-' }}</p>
                            </div>
                            <span class="h-fit rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ assignment.status }}</span>
                        </div>

                        <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-500">Tanggal</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ assignment.room_assignment?.schedule?.exam_date || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-500">Waktu</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ assignment.room_assignment?.schedule?.start_time || '-' }} - {{ assignment.room_assignment?.schedule?.end_time || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-500">Ruang</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ assignment.room_assignment?.room?.name || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase text-gray-500">Lokasi</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ assignment.room_assignment?.room?.location || '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div v-if="assignments.length === 0" class="bg-white p-10 text-center shadow-sm sm:rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900">Belum Ada Penempatan Ruang</h2>
                        <p class="mt-2 text-sm text-gray-500">Ruang ujian akan tampil setelah admin menempatkan peserta ke jadwal ujian.</p>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
