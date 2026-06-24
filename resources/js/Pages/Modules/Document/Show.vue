<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    document: {
        type: Object,
        required: true,
    },
    types: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const isImage = computed(() => props.document.mime_type?.startsWith('image/'));
const isPdf = computed(() => props.document.mime_type === 'application/pdf');
const roles = computed(() => page.props.auth?.roles || []);
const canManageAll = computed(() => roles.value.some((role) => ['admin', 'staff', 'superadmin'].includes(role)));
const canDelete = computed(() => canManageAll.value || ['draft', 'revision_required'].includes(props.document.registration?.status));
const canReview = computed(() => canManageAll.value);
const returnRegistrationId = computed(() => new URLSearchParams(page.url.split('?')[1] || '').get('registration'));
const backHref = computed(() => returnRegistrationId.value
    ? route('registrations.show', returnRegistrationId.value)
    : route('documents.index'));
const rows = computed(() => [
    ['No. Registrasi', props.document.registration?.registration_number || '-'],
    ['Nama', props.document.registration?.user?.name || '-'],
    ['Jenis Dokumen', props.types[props.document.type] || props.document.type],
    ['Status', props.document.status],
    ['Nama File', props.document.original_name],
    ['Ukuran', `${props.document.size_kb} KB`],
    ['MIME', props.document.mime_type],
    ['Catatan Review', props.document.notes || '-'],
    ['Direview Pada', props.document.reviewed_at ? new Date(props.document.reviewed_at).toLocaleString('id-ID') : '-'],
    ['Diunggah', new Date(props.document.created_at).toLocaleString('id-ID')],
]);

const approve = () => {
    if (confirm('Setujui dokumen ini?')) {
        router.post(route('documents.approve', props.document.id), {}, { preserveScroll: true });
    }
};

const reject = () => {
    const notes = prompt('Catatan penolakan dokumen:');

    if (!notes) return;

    router.post(route('documents.reject', props.document.id), { notes }, { preserveScroll: true });
};

const destroy = () => {
    if (confirm('Hapus dokumen ini?')) {
        router.delete(route('documents.destroy', props.document.id));
    }
};
</script>

<template>
    <Head title="Detail Dokumen" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Detail Dokumen</h1>
                    <div class="flex flex-wrap justify-end gap-3">
                        <Link :href="backHref" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-50 hover:text-gray-900" aria-label="Kembali" title="Kembali">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                                <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </Link>
                        <a :href="route('documents.download', props.document.id)" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                            Download
                        </a>
                    </div>
                </div>

                <div v-if="!canDelete" class="mb-6 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Dokumen sudah terkunci karena pendaftaran telah disubmit.
                </div>

                <div v-if="canReview" class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-md border border-gray-200 bg-white px-4 py-3 shadow-sm">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Review Dokumen</p>
                        <p class="mt-1 text-sm text-gray-600">Setujui dokumen jika valid, atau tolak dengan catatan perbaikan.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <PrimaryButton @click="approve">Setujui</PrimaryButton>
                        <SecondaryButton @click="reject">Tolak</SecondaryButton>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div v-if="isImage" class="mb-8 overflow-hidden rounded-md border border-gray-200 bg-gray-50">
                        <img :src="route('documents.view', props.document.id)" :alt="props.document.original_name" class="max-h-[560px] w-full object-contain" />
                    </div>
                    <div v-else-if="isPdf" class="mb-8 overflow-hidden rounded-md border border-gray-200 bg-gray-50">
                        <iframe :src="route('documents.view', props.document.id)" class="h-[560px] w-full" :title="props.document.original_name"></iframe>
                    </div>
                    <div v-else class="mb-8 rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                        Preview dokumen tidak tersedia untuk tipe file ini. Gunakan tombol download untuk membuka file.
                    </div>

                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div v-for="[label, value] in rows" :key="label">
                            <dt class="text-sm font-medium text-gray-500">{{ label }}</dt>
                            <dd class="mt-1 break-words text-sm text-gray-900">{{ value || '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6">
                        <Link :href="backHref" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-50 hover:text-gray-900" aria-label="Kembali" title="Kembali">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                                <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </Link>
                        <DangerButton v-if="canDelete" @click="destroy">Hapus</DangerButton>
                    </div>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
