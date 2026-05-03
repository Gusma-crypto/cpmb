<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    timeSlots: { type: Array, required: true },
    cbtExams: { type: Array, required: true },
    examTypes: { type: Array, required: true },
    statuses: { type: Array, required: true },
    routePrefix: { type: String, default: 'admin' },
});

const routeName = (name, params) => route(`${props.routePrefix}.exam-schedules.${name}`, params);

const form = useForm({
    title: '',
    code: '',
    exam_type: 'offline',
    exam_date: '',
    start_time: '',
    end_time: '',
    exam_time_slot_id: '',
    session_name: '',
    cbt_exam_id: '',
    status: 'draft',
    description: '',
});

const applySession = () => {
    const timeSlot = props.timeSlots.find((item) => item.id === Number(form.exam_time_slot_id));

    if (!timeSlot) return;

    form.session_name = timeSlot.name;
    form.start_time = timeSlot.start_time;
    form.end_time = timeSlot.end_time;
};

const submit = () => form.post(routeName('store'));
</script>

<template>
    <Head title="Tambah Jadwal Ujian" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Tambah Jadwal Ujian</h1>
                    <Link :href="routeName('index')" class="text-sm font-medium text-blue-600 hover:text-blue-900">Kembali</Link>
                </div>

                <form class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Judul</label>
                            <input v-model="form.title" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Kode</label>
                            <input v-model="form.code" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Tipe Ujian</label>
                            <select v-model="form.exam_type" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option v-for="type in examTypes" :key="type" :value="type">{{ type }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Tanggal</label>
                            <input v-model="form.exam_date" type="date" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.exam_date" class="mt-1 text-sm text-red-600">{{ form.errors.exam_date }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                            <select v-model="form.status" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Sesi</label>
                            <select v-model="form.exam_time_slot_id" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" @change="applySession">
                                <option value="">Manual</option>
                                <option v-for="timeSlot in timeSlots" :key="timeSlot.id" :value="timeSlot.id">{{ timeSlot.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Nama Sesi</label>
                            <input v-model="form.session_name" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.session_name" class="mt-1 text-sm text-red-600">{{ form.errors.session_name }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Paket CBT</label>
                        <select v-model="form.cbt_exam_id" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Tanpa paket CBT</option>
                            <option v-for="exam in cbtExams" :key="exam.id" :value="exam.id">{{ exam.title }} · {{ exam.duration_minutes }} menit · Passing {{ exam.pass_score }}</option>
                        </select>
                        <p v-if="form.errors.cbt_exam_id" class="mt-1 text-sm text-red-600">{{ form.errors.cbt_exam_id }}</p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Mulai</label>
                            <input v-model="form.start_time" type="time" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">{{ form.errors.start_time }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Selesai</label>
                            <input v-model="form.end_time" type="time" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.end_time" class="mt-1 text-sm text-red-600">{{ form.errors.end_time }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea v-model="form.description" rows="4" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
                        <Link :href="routeName('index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Batal</Link>
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white" :disabled="form.processing">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
