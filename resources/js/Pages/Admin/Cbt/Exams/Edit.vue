<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ExamForm from './Partials/ExamForm.vue';

const props = defineProps({
    exam: { type: Object, required: true },
    academicYears: { type: Array, required: true },
    programs: { type: Array, required: true },
    statuses: { type: Array, required: true },
});
</script>

<template>
    <Head title="Edit Paket CBT" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Edit Paket CBT</h1>
                        <p v-if="exam.status !== 'draft'" class="mt-1 text-sm text-amber-700">Paket yang sudah publish/closed tidak dapat diedit. Gunakan aksi close dari halaman list.</p>
                    </div>
                    <Link :href="route('admin.cbt.exams.index')" class="text-sm font-medium text-blue-600 hover:text-blue-900">Kembali</Link>
                </div>

                <ExamForm :exam="props.exam" :academic-years="academicYears" :programs="programs" :statuses="statuses" :submit-url="route('admin.cbt.exams.update', props.exam.id)" method="put" />
            </div>
        </div>
    </AdminLayout>
</template>
