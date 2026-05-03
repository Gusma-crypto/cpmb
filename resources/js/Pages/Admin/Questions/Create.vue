<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import QuestionForm from './Partials/QuestionForm.vue';

defineProps({
    categories: { type: Array, required: true },
    types: { type: Array, required: true },
    difficulties: { type: Array, required: true },
});
</script>

<template>
    <Head title="Tambah Soal CBT" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Tambah Soal CBT</h1>
                    <Link :href="route('admin.questions.index')" class="text-sm font-medium text-blue-600 hover:text-blue-900">Kembali</Link>
                </div>

                <QuestionForm :categories="categories" :types="types" :difficulties="difficulties" :submit-url="route('admin.questions.store')" />
            </div>
        </div>
    </AdminLayout>
</template>
