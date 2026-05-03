<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    registrationWave: { type: Object, required: true },
    academicYears: { type: Array, required: true },
    programs: { type: Array, required: true },
});

const toInputDate = (value) => value ? value.slice(0, 16) : '';
const toBoolean = (value, fallback = true) => {
    if (value === undefined || value === null) return fallback;
    return value === true || value === 1 || value === '1';
};
const pivotFor = (program) => props.registrationWave.programs?.find((item) => item.id === program.id)?.pivot || null;

const form = useForm({
    academic_year_id: props.registrationWave.academic_year_id,
    wave_number: props.registrationWave.wave_number,
    label: props.registrationWave.label,
    open_at: toInputDate(props.registrationWave.open_at),
    close_at: toInputDate(props.registrationWave.close_at),
    is_active: props.registrationWave.is_active,
    description: props.registrationWave.description || '',
    programs: props.programs.map((program) => {
        const pivot = pivotFor(program);

        return {
            program_id: program.id,
            name: program.name,
            code: program.code,
            status: pivot?.status ?? (program.is_active ? 'aktif' : 'nonaktif'),
            is_open: toBoolean(pivot?.is_open, true),
        };
    }),
});

const submit = () => {
    form
        .transform((data) => ({
            ...data,
            academic_year_id: Number(data.academic_year_id),
            wave_number: Number(data.wave_number),
            is_active: Boolean(data.is_active),
            programs: data.programs.map((program) => ({
                program_id: Number(program.program_id),
                status: program.status,
                is_open: Boolean(program.is_open),
            })),
        }))
        .put(route('admin.registration-waves.update', props.registrationWave.id));
};
</script>

<template>
    <Head title="Edit Gelombang" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Gelombang</h1>
                    <Link :href="route('admin.registration-waves.index')" class="text-sm font-medium text-blue-600 hover:text-blue-900">Kembali</Link>
                </div>

                <form class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <select v-model="form.academic_year_id" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option v-for="academicYear in academicYears" :key="academicYear.id" :value="academicYear.id">{{ academicYear.label }}</option>
                        </select>
                        <p v-if="form.errors.academic_year_id" class="mt-1 text-sm text-red-600">{{ form.errors.academic_year_id }}</p>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Nomor</label>
                            <input v-model="form.wave_number" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.wave_number" class="mt-1 text-sm text-red-600">{{ form.errors.wave_number }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Label</label>
                            <input v-model="form.label" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.label" class="mt-1 text-sm text-red-600">{{ form.errors.label }}</p>
                        </div>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Dibuka</label>
                            <input v-model="form.open_at" type="datetime-local" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.open_at" class="mt-1 text-sm text-red-600">{{ form.errors.open_at }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Ditutup</label>
                            <input v-model="form.close_at" type="datetime-local" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            <p v-if="form.errors.close_at" class="mt-1 text-sm text-red-600">{{ form.errors.close_at }}</p>
                        </div>
                    </div>
                    <div class="rounded-md border border-gray-200">
                        <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900">Prodi per Gelombang</h2>
                                <p class="mt-1 text-xs text-gray-500">Atur status prodi. Periode pendaftaran prodi otomatis mengikuti tanggal dibuka dan ditutup gelombang.</p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Prodi</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Buka</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="(program, index) in form.programs" :key="program.program_id">
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            <p class="font-medium text-gray-900">{{ program.name }}</p>
                                            <p class="text-xs text-gray-500">{{ program.code }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <select v-model="program.status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="aktif">Aktif</option>
                                                <option value="nonaktif">Nonaktif</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input v-model="program.is_open" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p v-if="form.errors.programs" class="px-4 py-3 text-sm text-red-600">{{ form.errors.programs }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea v-model="form.description" rows="4" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                        Aktif
                    </label>
                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
                        <Link :href="route('admin.registration-waves.index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Batal</Link>
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50" :disabled="form.processing">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
