<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ExamForm from './Partials/ExamForm.vue';

defineProps({
    academicYears: { type: Array, required: true },
    programs: { type: Array, required: true },
    statuses: { type: Array, required: true },
});
</script>

<template>
    <Head title="Tambah Paket CBT" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Tambah Paket CBT</h1>
                    <Link :href="route('admin.cbt.exams.index')" class="text-sm font-medium text-blue-600 hover:text-blue-900">Kembali</Link>
                </div>

                <ExamForm :academic-years="academicYears" :programs="programs" :statuses="statuses" :submit-url="route('admin.cbt.exams.store')" />
            </div>
        </div>
    </AdminLayout>
</template>
