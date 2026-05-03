<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    questions: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    categories: { type: Array, required: true },
    types: { type: Array, required: true },
});

const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');
const type = ref(props.filters.type || '');

const submitSearch = () => {
    router.get(route('admin.questions.index'), {
        search: search.value,
        category_id: categoryId.value,
        type: type.value,
    }, { preserveState: true, replace: true });
};

const destroy = (question) => {
    if (confirm('Hapus soal ini?')) {
        router.delete(route('admin.questions.destroy', question.id));
    }
};

const shortText = (value) => {
    if (!value) return '-';
    const text = value.replace(/<[^>]*>/g, '');
    return text.length > 120 ? `${text.slice(0, 120)}...` : text;
};
</script>

<template>
    <Head title="Bank Soal CBT" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Bank Soal CBT</h1>
                        <p class="mt-1 text-sm text-gray-500">Kelola soal dan pilihan jawaban untuk tahap CBT berikutnya.</p>
                    </div>
                    <Link :href="route('admin.questions.create')" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                        Tambah Soal
                    </Link>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="border-b border-gray-200 p-4" @submit.prevent="submitSearch">
                        <div class="grid gap-3 md:grid-cols-[1fr_180px_180px_auto]">
                            <input v-model="search" type="search" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari soal atau kategori" />
                            <select v-model="categoryId" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua kategori</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                            </select>
                            <select v-model="type" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua tipe</option>
                                <option v-for="item in types" :key="item" :value="item">{{ item }}</option>
                            </select>
                            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Soal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Tipe</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Opsi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="question in questions.data" :key="question.id">
                                    <td class="max-w-xl px-6 py-4 text-sm text-gray-700">
                                        <div class="font-medium text-gray-900">{{ shortText(question.question_text) }}</div>
                                        <div class="mt-1 text-xs text-gray-500">Kesulitan: {{ question.difficulty }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ question.category?.name || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ question.type }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ question.options_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="question.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                            {{ question.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('admin.questions.show', question.id)" class="font-medium text-indigo-600 hover:text-indigo-900">Detail</Link>
                                        <Link :href="route('admin.questions.edit', question.id)" class="ms-4 font-medium text-blue-600 hover:text-blue-900">Edit</Link>
                                        <button type="button" class="ms-4 font-medium text-red-600 hover:text-red-900" @click="destroy(question)">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="questions.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada soal CBT.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link v-for="link in questions.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
