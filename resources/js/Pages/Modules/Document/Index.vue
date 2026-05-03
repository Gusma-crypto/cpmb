<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    documents: {
        type: Object,
        required: true,
    },
    types: {
        type: Object,
        required: true,
    },
    currentRegistration: {
        type: Object,
        default: null,
    },
});

const canContinueToReview = computed(() => props.currentRegistration && ['draft', 'revision_required'].includes(props.currentRegistration.status));
const backHref = computed(() => props.currentRegistration
    ? route('registrations.show', props.currentRegistration.id)
    : route('registrations.index'));
</script>

<template>
    <Head title="Dokumen PMB" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Dokumen PMB</h1>
                    <div class="flex flex-wrap justify-end gap-2">
                        <Link :href="backHref" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-50 hover:text-gray-900" aria-label="Kembali" title="Kembali">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                                <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </Link>
                        <Link
                            v-if="canContinueToReview"
                            :href="route('registrations.show', currentRegistration.id)"
                            class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-800"
                        >
                            Next: Review & Submit
                        </Link>
                        <Link :href="route('documents.create')" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                            Upload Dokumen
                        </Link>
                    </div>
                </div>

                <div v-if="canContinueToReview" class="mb-6 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Setelah dokumen wajib lengkap, klik <strong>Next: Review & Submit</strong> untuk checklist akhir dan submit pendaftaran.
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">No. Registrasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">File</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="document in documents.data" :key="document.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ document.registration?.registration_number || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ document.registration?.user?.name || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ types[document.type] || document.type }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ document.original_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ document.status }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('documents.show', document.id)" class="font-medium text-blue-600 hover:text-blue-900">Detail</Link>
                                        <a :href="route('documents.download', document.id)" class="ms-4 font-medium text-blue-600 hover:text-blue-900">Download</a>
                                    </td>
                                </tr>
                                <tr v-if="documents.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada dokumen PMB.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link v-for="link in documents.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                        <Link
                            v-if="canContinueToReview"
                            :href="route('registrations.show', currentRegistration.id)"
                            class="ms-auto rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                        >
                            Next: Review & Submit
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
