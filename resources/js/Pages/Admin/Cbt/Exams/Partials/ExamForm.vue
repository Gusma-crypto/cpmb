<script setup>
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    exam: { type: Object, default: null },
    academicYears: { type: Array, required: true },
    programs: { type: Array, required: true },
    statuses: { type: Array, required: true },
    submitUrl: { type: String, required: true },
    method: { type: String, default: 'post' },
});

const toLocalInput = (value) => {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';
    const offsetDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    return offsetDate.toISOString().slice(0, 16);
};

const form = useForm({
    title: props.exam?.title || '',
    description: props.exam?.description || '',
    academic_year_id: props.exam?.academic_year_id || '',
    program_id: props.exam?.program_id || '',
    duration_minutes: props.exam?.duration_minutes || 90,
    pass_score: props.exam?.pass_score ?? 60,
    total_questions: props.exam?.total_questions || '',
    randomize_questions: props.exam?.randomize_questions ?? false,
    randomize_options: props.exam?.randomize_options ?? false,
    max_attempts: props.exam?.max_attempts || 1,
    start_at: toLocalInput(props.exam?.start_at),
    end_at: toLocalInput(props.exam?.end_at),
    status: props.exam?.status || 'draft',
});

const submit = () => {
    if (props.method === 'put') {
        form.put(props.submitUrl);
        return;
    }

    form.post(props.submitUrl);
};
</script>

<template>
    <form class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Judul Paket</label>
                <input v-model="form.title" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select v-model="form.status" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
                </select>
                <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
            <textarea v-model="form.description" rows="4" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tahun Akademik</label>
                <select v-model="form.academic_year_id" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Umum</option>
                    <option v-for="year in academicYears" :key="year.id" :value="year.id">{{ year.label }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Program Studi</label>
                <select v-model="form.program_id" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Program</option>
                    <option v-for="program in programs" :key="program.id" :value="program.id">{{ program.name }}{{ program.code ? ` (${program.code})` : '' }}</option>
                </select>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Durasi Menit</label>
                <input v-model="form.duration_minutes" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <p v-if="form.errors.duration_minutes" class="mt-1 text-sm text-red-600">{{ form.errors.duration_minutes }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Passing Grade</label>
                <input v-model="form.pass_score" type="number" min="0" max="100" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <p v-if="form.errors.pass_score" class="mt-1 text-sm text-red-600">{{ form.errors.pass_score }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Total Soal</label>
                <input v-model="form.total_questions" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Maks. Attempt</label>
                <input v-model="form.max_attempts" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <p v-if="form.errors.max_attempts" class="mt-1 text-sm text-red-600">{{ form.errors.max_attempts }}</p>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Mulai Akses</label>
                <input v-model="form.start_at" type="datetime-local" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <p v-if="form.errors.start_at" class="mt-1 text-sm text-red-600">{{ form.errors.start_at }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Selesai Akses</label>
                <input v-model="form.end_at" type="datetime-local" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                <p v-if="form.errors.end_at" class="mt-1 text-sm text-red-600">{{ form.errors.end_at }}</p>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <label class="flex items-center gap-2 rounded-md border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700">
                <input v-model="form.randomize_questions" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                Acak urutan soal
            </label>
            <label class="flex items-center gap-2 rounded-md border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700">
                <input v-model="form.randomize_options" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                Acak pilihan jawaban
            </label>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
            <Link :href="route('admin.cbt.exams.index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Batal</Link>
            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white" :disabled="form.processing">Simpan</button>
        </div>
    </form>
</template>
