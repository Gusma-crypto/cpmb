<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    results: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Hasil CBT" />

    <StudentLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Hasil CBT</h1>
                    <p class="mt-1 text-sm text-gray-500">Hasil hanya tampil setelah dipublish oleh admin.</p>
                </div>

                <div v-if="results.length === 0" class="rounded-lg bg-white p-8 text-center shadow-sm">
                    <p class="text-sm text-gray-500">Belum ada hasil CBT yang dipublish.</p>
                </div>

                <div v-else class="grid gap-4">
                    <article v-for="result in results" :key="result.id" class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">{{ result.cbt_exam?.title || 'CBT' }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ result.attempt?.schedule?.title || '-' }}</p>
                            </div>
                            <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="result.is_passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                                {{ result.is_passed ? 'Lulus' : 'Tidak Lulus' }}
                            </span>
                        </div>

                        <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-4">
                            <div>
                                <dt class="font-medium text-gray-500">Nilai Akhir</dt>
                                <dd class="mt-1 text-2xl font-bold text-gray-900">{{ result.final_score }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Passing Grade</dt>
                                <dd class="mt-1 text-gray-800">{{ result.pass_score }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Benar</dt>
                                <dd class="mt-1 text-gray-800">{{ result.correct_answers }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Kosong</dt>
                                <dd class="mt-1 text-gray-800">{{ result.unanswered }}</dd>
                            </div>
                        </dl>
                    </article>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
