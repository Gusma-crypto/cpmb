<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    assignment: Object,
    rooms: Array,
    schedules: Array,
    registrations: Array,
    supervisors: Array,
    assignedRegistrationIds: { type: Array, default: () => [] },
    routePrefix: { type: String, default: 'admin' },
});

const r = (name, params) => route(`${props.routePrefix}.exam-room-assignments.${name}`, params);
const participantIds = props.assignment.participants?.map((participant) => participant.registration_id) || [];

const form = useForm({
    exam_room_id: props.assignment.exam_room_id,
    exam_schedule_id: props.assignment.exam_schedule_id,
    supervisor_id: props.assignment.supervisor_id || '',
    max_participants: props.assignment.max_participants,
    status: props.assignment.status,
    registration_ids: participantIds,
});

const selectedRoom = computed(() => props.rooms.find((room) => room.id === Number(form.exam_room_id)));
const roomCapacity = computed(() => selectedRoom.value?.capacity || 0);
const selectedParticipantCount = computed(() => form.registration_ids.length);
const quotaExceeded = computed(() => Number(form.max_participants || 0) > 0 && selectedParticipantCount.value > Number(form.max_participants));
const isParticipantAssigned = (registrationId) => props.assignedRegistrationIds.includes(registrationId);
const isParticipantDisabled = (registrationId) => isParticipantAssigned(registrationId)
    || (!form.registration_ids.includes(registrationId)
        && Number(form.max_participants || 0) > 0
        && selectedParticipantCount.value >= Number(form.max_participants));

watch(() => form.exam_room_id, () => {
    if (!selectedRoom.value) return;

    const quota = Number(form.max_participants || 0);
    if (!quota || quota > selectedRoom.value.capacity) {
        form.max_participants = selectedRoom.value.capacity;
    }
});

watch(() => form.max_participants, (value) => {
    if (!selectedRoom.value) return;

    const quota = Number(value || 0);
    if (quota > selectedRoom.value.capacity) {
        form.max_participants = selectedRoom.value.capacity;
    }
});

const submit = () => form.put(r('update', props.assignment.id));
</script>

<template>
    <Head title="Edit Penempatan Peserta" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4">
                <div class="mb-6 flex justify-between">
                    <h1 class="text-2xl font-semibold">Edit Penempatan Peserta</h1>
                    <Link :href="r('index')" class="text-sm text-blue-600">Kembali</Link>
                </div>

                <form class="space-y-6 bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Jadwal</label>
                            <select v-model="form.exam_schedule_id" class="w-full rounded-md border-gray-300 text-sm">
                                <option v-for="schedule in schedules" :key="schedule.id" :value="schedule.id">
                                    {{ schedule.title }} - {{ schedule.exam_date }}
                                </option>
                            </select>
                            <p v-if="form.errors.exam_schedule_id" class="text-sm text-red-600">{{ form.errors.exam_schedule_id }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium">Ruang</label>
                            <select v-model="form.exam_room_id" class="w-full rounded-md border-gray-300 text-sm">
                                <option v-for="room in rooms" :key="room.id" :value="room.id">
                                    {{ room.name }} - kapasitas {{ room.capacity }}
                                </option>
                            </select>
                            <p v-if="form.errors.exam_room_id" class="text-sm text-red-600">{{ form.errors.exam_room_id }}</p>
                        </div>
                    </div>

                    <div v-if="selectedRoom" class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                        Kapasitas ruang {{ selectedRoom.name }} adalah {{ roomCapacity }} peserta. Kuota penempatan boleh lebih kecil, tetapi tidak boleh melebihi kapasitas ruang.
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Pengawas</label>
                            <select v-model="form.supervisor_id" class="w-full rounded-md border-gray-300 text-sm">
                                <option value="">Tanpa pengawas</option>
                                <option v-for="supervisor in supervisors" :key="supervisor.id" :value="supervisor.id">{{ supervisor.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium">Kuota Peserta</label>
                            <input v-model.number="form.max_participants" type="number" min="1" :max="roomCapacity || null" class="w-full rounded-md border-gray-300 text-sm" />
                            <p class="mt-1 text-xs text-gray-500">Maksimal mengikuti kapasitas ruang.</p>
                            <p v-if="form.errors.max_participants" class="text-sm text-red-600">{{ form.errors.max_participants }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium">Status</label>
                            <select v-model="form.status" class="w-full rounded-md border-gray-300 text-sm">
                                <option>draft</option>
                                <option>active</option>
                                <option>finished</option>
                                <option>cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <label class="block text-sm font-medium">Peserta</label>
                            <span class="text-xs text-gray-500">{{ selectedParticipantCount }} / {{ form.max_participants || 0 }} peserta</span>
                        </div>
                        <div class="max-h-60 overflow-y-auto rounded-md border p-3">
                            <label v-for="registration in registrations" :key="registration.id" class="mb-2 flex gap-2 text-sm" :class="{ 'text-gray-400': isParticipantDisabled(registration.id) }">
                                <input v-model="form.registration_ids" type="checkbox" :value="registration.id" :disabled="isParticipantDisabled(registration.id)" />
                                <span>
                                    {{ registration.registration_number }} - {{ registration.user?.name || '-' }}
                                    <span v-if="isParticipantAssigned(registration.id)" class="text-xs text-red-600">(sudah ditempatkan)</span>
                                </span>
                            </label>
                        </div>
                        <p v-if="quotaExceeded" class="mt-1 text-sm text-red-600">Jumlah peserta melebihi kuota penempatan.</p>
                        <p v-if="form.errors.registration_ids" class="mt-1 text-sm text-red-600">{{ form.errors.registration_ids }}</p>
                    </div>

                    <div class="flex justify-end gap-3 border-t pt-6">
                        <Link :href="r('index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm">Batal</Link>
                        <button class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white" :disabled="form.processing || quotaExceeded">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
