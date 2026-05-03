<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    card: Object,
    routePrefix: { type: String, default: 'admin' },
});

const r = (name, params) => route(`${props.routePrefix}.exam-cards.${name}`, params);
const printCard = () => {
    router.post(route(`${props.routePrefix}.exam-cards.print`, props.card.id), {}, {
        preserveScroll: true,
        onSuccess: () => window.print(),
    });
};
</script>

<template>
    <Head title="Detail Kartu Ujian" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Detail Kartu Ujian</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ card.card_number }}</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="r('index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm">Kembali</Link>
                        <button class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white" type="button" @click="printCard">Cetak</button>
                    </div>
                </div>

                <div class="bg-white p-8 shadow-sm sm:rounded-lg">
                    <div class="border-b pb-5">
                        <p class="text-sm uppercase tracking-wide text-gray-500">Kartu Peserta Ujian</p>
                        <h2 class="mt-1 text-2xl font-semibold text-gray-900">{{ card.registration?.user?.name || '-' }}</h2>
                        <p class="mt-1 font-mono text-sm text-gray-500">{{ card.participant_number }} · {{ card.card_number }}</p>
                    </div>

                    <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">No Pendaftaran</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ card.registration?.registration_number || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Jadwal</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ card.schedule?.title || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Tanggal</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ card.schedule?.exam_date || '-' }} {{ card.schedule?.start_time || '' }} - {{ card.schedule?.end_time || '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Ruang</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ card.room_assignment?.room?.name || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Lokasi</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ card.room_assignment?.room?.location || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Pengawas</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ card.room_assignment?.supervisor?.name || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Kode Verifikasi</dt>
                            <dd class="mt-1 font-mono text-sm text-gray-900">{{ card.verification_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ card.status }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-6 bg-white p-6 shadow-sm sm:rounded-lg print:hidden">
                    <h3 class="text-base font-semibold text-gray-900">Riwayat Cetak</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="log in card.print_logs" :key="log.id">
                                    <td class="px-3 py-2 text-sm">{{ log.printed_at }}</td>
                                    <td class="px-3 py-2 text-sm">{{ log.user?.name || '-' }}</td>
                                    <td class="px-3 py-2 text-sm text-gray-500">{{ log.ip_address || '-' }}</td>
                                </tr>
                                <tr v-if="card.print_logs.length === 0">
                                    <td class="px-3 py-6 text-center text-sm text-gray-500">Belum pernah dicetak.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
