<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    cards: Object,
    assignments: Array,
    routePrefix: { type: String, default: 'admin' },
});

const form = useForm({
    participant_assignment_id: '',
});

const r = (name, params) => route(`${props.routePrefix}.exam-cards.${name}`, params);
const submit = () => form.post(r('store'), { preserveScroll: true });
</script>

<template>
    <Head title="Kartu Ujian" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-6xl px-4">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Kartu Ujian</h1>
                    <p class="mt-1 text-sm text-gray-500">Generate dan pantau kartu ujian peserta.</p>
                </div>

                <form class="mb-6 flex flex-col gap-3 bg-white p-4 shadow-sm sm:rounded-lg md:flex-row md:items-end" @submit.prevent="submit">
                    <div class="flex-1">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Peserta Terjadwal</label>
                        <select v-model="form.participant_assignment_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">Pilih peserta</option>
                            <option v-for="assignment in assignments" :key="assignment.id" :value="assignment.id">
                                {{ assignment.participant_number }} - {{ assignment.registration?.user?.name || '-' }} - {{ assignment.schedule?.title || '-' }}
                            </option>
                        </select>
                        <p v-if="form.errors.participant_assignment_id" class="mt-1 text-sm text-red-600">{{ form.errors.participant_assignment_id }}</p>
                        <p v-if="form.errors.exam_card" class="mt-1 text-sm text-red-600">{{ form.errors.exam_card }}</p>
                    </div>
                    <button class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white" :disabled="form.processing">Generate Kartu</button>
                </form>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">No Kartu</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Peserta</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Jadwal</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Ruang</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="card in cards.data" :key="card.id">
                                    <td class="px-4 py-3 font-mono text-sm">{{ card.card_number }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ card.registration?.user?.name || '-' }}</div>
                                        <div class="text-gray-500">{{ card.registration?.registration_number || '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ card.schedule?.title || '-' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ card.room_assignment?.room?.name || '-' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ card.status }}</td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <Link :href="r('show', card.id)" class="text-blue-600">Detail</Link>
                                    </td>
                                </tr>
                                <tr v-if="cards.data.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada kartu ujian.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
