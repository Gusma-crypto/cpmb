<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    exam: { type: Object, required: true },
    selectedQuestions: { type: Array, default: () => [] },
    questionBank: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    categories: { type: Array, required: true },
    types: { type: Array, required: true },
    difficulties: { type: Array, required: true },
});

const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');
const difficulty = ref(props.filters.difficulty || '');
const type = ref(props.filters.type || '');

const initialQuestions = props.selectedQuestions.map((question, index) => ({
    cbt_question_id: question.id,
    question_text: question.question_text,
    category_name: question.category?.name || '-',
    type: question.type,
    difficulty: question.difficulty,
    points: question.pivot?.points || 1,
    order_index: question.pivot?.order_index ?? index + 1,
}));

const form = useForm({
    questions: initialQuestions,
});

const selectedIds = computed(() => new Set(form.questions.map((question) => Number(question.cbt_question_id))));
const canEdit = computed(() => props.exam.status === 'draft');

const submitFilter = () => {
    router.get(route('admin.cbt.exams.questions.edit', props.exam.id), {
        search: search.value,
        category_id: categoryId.value,
        difficulty: difficulty.value,
        type: type.value,
    }, { preserveState: true, replace: true });
};

const addQuestion = (question) => {
    if (!canEdit.value || selectedIds.value.has(Number(question.id))) return;

    form.questions.push({
        cbt_question_id: question.id,
        question_text: question.question_text,
        category_name: question.category?.name || '-',
        type: question.type,
        difficulty: question.difficulty,
        points: 1,
        order_index: form.questions.length + 1,
    });
};

const removeQuestion = (questionId) => {
    if (!canEdit.value) return;

    form.questions = form.questions.filter((question) => Number(question.cbt_question_id) !== Number(questionId));
};

const save = () => {
    form.put(route('admin.cbt.exams.questions.update', props.exam.id), { preserveScroll: true });
};

const shortText = (value) => {
    if (!value) return '-';
    const text = value.replace(/<[^>]*>/g, '');
    return text.length > 100 ? `${text.slice(0, 100)}...` : text;
};
</script>

<template>
    <Head title="Kelola Soal Paket CBT" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Kelola Soal Paket CBT</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ exam.title }} · Status {{ exam.status }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:bg-gray-300" :disabled="!canEdit || form.processing" @click="save">
                            Simpan Soal
                        </button>
                        <Link :href="route('admin.cbt.exams.index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Kembali</Link>
                    </div>
                </div>

                <div v-if="!canEdit" class="mb-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Paket ujian yang sudah publish atau closed tidak dapat mengubah daftar soal.
                </div>

                <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 px-5 py-4">
                            <h2 class="text-base font-semibold text-gray-900">Soal Terpilih</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ form.questions.length }} soal di paket ini.</p>
                            <p v-if="form.errors.questions" class="mt-2 text-sm text-red-600">{{ form.errors.questions }}</p>
                        </div>

                        <div class="max-h-[680px] overflow-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="sticky top-0 bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Soal</th>
                                        <th class="w-24 px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Order</th>
                                        <th class="w-24 px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Poin</th>
                                        <th class="w-20 px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="(question, index) in form.questions" :key="question.cbt_question_id">
                                        <td class="px-4 py-4 text-sm text-gray-700">
                                            <div class="font-medium text-gray-900">{{ shortText(question.question_text) }}</div>
                                            <div class="mt-1 text-xs text-gray-500">{{ question.category_name }} · {{ question.type }} · {{ question.difficulty }}</div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <input v-model="question.order_index" :disabled="!canEdit" type="number" min="0" class="w-20 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100" />
                                            <p v-if="form.errors[`questions.${index}.order_index`]" class="mt-1 text-xs text-red-600">{{ form.errors[`questions.${index}.order_index`] }}</p>
                                        </td>
                                        <td class="px-4 py-4">
                                            <input v-model="question.points" :disabled="!canEdit" type="number" min="1" class="w-20 rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100" />
                                            <p v-if="form.errors[`questions.${index}.points`]" class="mt-1 text-xs text-red-600">{{ form.errors[`questions.${index}.points`] }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <button type="button" class="text-sm font-medium" :class="canEdit ? 'text-red-600 hover:text-red-900' : 'text-gray-400'" @click="removeQuestion(question.cbt_question_id)">Hapus</button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.questions.length === 0">
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada soal dipilih.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <form class="border-b border-gray-200 p-4" @submit.prevent="submitFilter">
                            <div class="grid gap-3 lg:grid-cols-2">
                                <input v-model="search" type="search" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari teks soal" />
                                <select v-model="categoryId" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Semua kategori</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                                <select v-model="difficulty" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Semua difficulty</option>
                                    <option v-for="item in difficulties" :key="item" :value="item">{{ item }}</option>
                                </select>
                                <select v-model="type" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Semua tipe</option>
                                    <option v-for="item in types" :key="item" :value="item">{{ item }}</option>
                                </select>
                                <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white lg:col-span-2">Cari Soal</button>
                            </div>
                        </form>

                        <div class="divide-y divide-gray-200">
                            <div v-for="question in questionBank.data" :key="question.id" class="flex gap-4 px-5 py-4">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ shortText(question.question_text) }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ question.category?.name || '-' }} · {{ question.type }} · {{ question.difficulty }} · {{ question.options_count }} opsi</p>
                                </div>
                                <button type="button" class="shrink-0 rounded-md px-3 py-2 text-sm font-semibold" :class="selectedIds.has(Number(question.id)) || !canEdit ? 'cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-blue-600 text-white hover:bg-blue-700'" @click="addQuestion(question)">
                                    {{ selectedIds.has(Number(question.id)) ? 'Dipilih' : 'Tambah' }}
                                </button>
                            </div>
                            <div v-if="questionBank.data.length === 0" class="px-5 py-8 text-center text-sm text-gray-500">Tidak ada soal aktif sesuai filter.</div>
                        </div>

                        <div class="flex flex-wrap gap-2 border-t border-gray-200 px-5 py-4">
                            <Link v-for="link in questionBank.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
