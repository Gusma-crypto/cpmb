<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    exams: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    programs: { type: Array, required: true },
    statuses: { type: Array, required: true },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const programId = ref(props.filters.program_id || '');

const submitSearch = () => {
    router.get(route('admin.cbt.exams.index'), {
        search: search.value,
        status: status.value,
        program_id: programId.value,
    }, { preserveState: true, replace: true });
};

const statusClass = (value) => ({
    draft: 'bg-gray-100 text-gray-700',
    published: 'bg-green-100 text-green-700',
    closed: 'bg-red-100 text-red-700',
}[value] || 'bg-gray-100 text-gray-700');

const destroy = (exam) => {
    if (exam.status !== 'draft') return;
    if (confirm(`Hapus paket ujian "${exam.title}"?`)) {
        router.delete(route('admin.cbt.exams.destroy', exam.id));
    }
};

const publish = (exam) => {
    if (exam.status === 'closed') return;
    if (confirm(`Publish paket ujian "${exam.title}"?`)) {
        router.post(route('admin.cbt.exams.publish', exam.id), {}, { preserveScroll: true });
    }
};

const closeExam = (exam) => {
    if (exam.status === 'closed') return;
    if (confirm(`Tutup paket ujian "${exam.title}"?`)) {
        router.post(route('admin.cbt.exams.close', exam.id), {}, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Paket CBT" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Paket CBT</h1>
                        <p class="mt-1 text-sm text-gray-500">Kelola paket ujian, durasi, passing grade, dan soal dari bank soal.</p>
                    </div>
                    <Link :href="route('admin.cbt.exams.create')" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                        Tambah Paket
                    </Link>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="border-b border-gray-200 p-4" @submit.prevent="submitSearch">
                        <div class="grid gap-3 md:grid-cols-[1fr_170px_190px_auto]">
                            <input v-model="search" type="search" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari paket CBT" />
                            <select v-model="status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua status</option>
                                <option v-for="item in statuses" :key="item" :value="item">{{ item }}</option>
                            </select>
                            <select v-model="programId" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua program</option>
                                <option v-for="program in programs" :key="program.id" :value="program.id">{{ program.name }}</option>
                            </select>
                            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Paket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Program</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Durasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Soal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="exam in exams.data" :key="exam.id">
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div class="font-semibold text-gray-900">{{ exam.title }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ exam.academic_year?.label || 'Semua tahun' }} · Passing {{ exam.pass_score }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ exam.program?.name || 'Semua Program' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ exam.duration_minutes }} menit</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ exam.questions_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="statusClass(exam.status)">{{ exam.status }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('admin.cbt.exams.questions.edit', exam.id)" class="font-medium text-indigo-600 hover:text-indigo-900">Soal</Link>
                                        <Link :href="route('admin.cbt.exams.edit', exam.id)" class="ms-4 font-medium" :class="exam.status === 'draft' ? 'text-blue-600 hover:text-blue-900' : 'text-gray-400'">Edit</Link>
                                        <button type="button" class="ms-4 font-medium" :class="exam.status === 'draft' ? 'text-green-700 hover:text-green-900' : 'text-gray-400'" @click="publish(exam)">Publish</button>
                                        <button type="button" class="ms-4 font-medium" :class="exam.status !== 'closed' ? 'text-amber-700 hover:text-amber-900' : 'text-gray-400'" @click="closeExam(exam)">Close</button>
                                        <button type="button" class="ms-4 font-medium" :class="exam.status === 'draft' ? 'text-red-600 hover:text-red-900' : 'text-gray-400'" @click="destroy(exam)">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="exams.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada paket CBT.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link v-for="link in exams.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
