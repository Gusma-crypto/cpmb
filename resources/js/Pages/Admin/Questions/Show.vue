<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    question: { type: Object, required: true },
});
</script>

<template>
    <Head title="Detail Soal CBT" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Detail Soal CBT</h1>
                    <div class="flex gap-3">
                        <Link :href="route('admin.questions.edit', question.id)" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Edit</Link>
                        <Link :href="route('admin.questions.index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Kembali</Link>
                    </div>
                </div>

                <div class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="grid gap-4 sm:grid-cols-4">
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">Kategori</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ question.category?.name || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">Tipe</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ question.type }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">Kesulitan</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ question.difficulty }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-gray-500">Status</p>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ question.is_active ? 'Aktif' : 'Nonaktif' }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Pertanyaan</p>
                        <div class="mt-2 whitespace-pre-line rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-900">{{ question.question_text }}</div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase text-gray-500">Pilihan Jawaban</p>
                        <div class="mt-2 divide-y divide-gray-200 rounded-lg border border-gray-200">
                            <div v-for="option in question.options" :key="option.id" class="flex items-start justify-between gap-4 p-4">
                                <p class="text-sm text-gray-900">{{ option.option_text }}</p>
                                <span class="shrink-0 rounded-full px-2 py-1 text-xs font-medium" :class="option.is_correct ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                    {{ option.is_correct ? 'Benar' : 'Salah' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="question.explanation">
                        <p class="text-xs font-semibold uppercase text-gray-500">Pembahasan</p>
                        <div class="mt-2 whitespace-pre-line rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-900">{{ question.explanation }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
