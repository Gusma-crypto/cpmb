<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    studentBiodata: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const roles = computed(() => page.props.auth?.roles || []);
const canManageAll = computed(() => roles.value.some((role) => ['admin', 'staff', 'superadmin'].includes(role)));
const isLocked = computed(() => !canManageAll.value && props.studentBiodata.registration?.status !== 'draft');
const returnRegistrationId = computed(() => new URLSearchParams(page.url.split('?')[1] || '').get('registration'));
const backHref = computed(() => returnRegistrationId.value
    ? route('registrations.show', returnRegistrationId.value)
    : route('registrations.index'));
const rows = computed(() => [
    ['No. Registrasi', props.studentBiodata.registration?.registration_number || '-'],
    ['Nama', props.studentBiodata.registration?.user?.name || '-'],
    ['NIK', props.studentBiodata.nik],
    ['Tempat, Tanggal Lahir', `${props.studentBiodata.birth_place}, ${new Date(props.studentBiodata.birth_date).toLocaleDateString('id-ID')}`],
    ['Jenis Kelamin', props.studentBiodata.gender === 'male' ? 'Laki-laki' : 'Perempuan'],
    ['Agama', props.studentBiodata.religion],
    ['Provinsi', props.studentBiodata.province],
    ['Kota', props.studentBiodata.city],
    ['Sekolah', props.studentBiodata.school_name],
    ['Tahun Lulus', props.studentBiodata.school_graduation_year],
    ['Nama Orang Tua', props.studentBiodata.parent_name],
    ['Telepon Orang Tua', props.studentBiodata.parent_phone],
    ['Pekerjaan Orang Tua', props.studentBiodata.parent_job],
]);

</script>

<template>
    <Head title="Detail Biodata" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Detail Biodata</h1>
                    <div class="flex flex-wrap justify-end gap-3">
                        <Link :href="backHref" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-50 hover:text-gray-900" aria-label="Kembali" title="Kembali">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                                <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </Link>
                        <Link v-if="!isLocked" :href="route('biodata.edit', studentBiodata.id)" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                            Edit
                        </Link>
                        <button v-else type="button" class="inline-flex cursor-not-allowed items-center rounded-md bg-gray-200 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-500" disabled title="Biodata terkunci setelah pendaftaran disubmit">
                            Edit
                        </button>
                    </div>
                </div>

                <div v-if="isLocked" class="mb-6 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Biodata sudah terkunci karena pendaftaran telah disubmit.
                </div>

                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div v-for="[label, value] in rows" :key="label">
                            <dt class="text-sm font-medium text-gray-500">{{ label }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ value || '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ studentBiodata.address }}</dd>
                        </div>
                    </dl>

                    <div class="mt-8 flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
                        <Link :href="backHref" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-50 hover:text-gray-900" aria-label="Kembali" title="Kembali">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                                <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </Link>
                        <div class="flex flex-wrap gap-3">
                            <Link v-if="!isLocked" :href="route('biodata.edit', studentBiodata.id)" class="inline-flex items-center rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-200">
                                Edit Biodata
                            </Link>
                            <button v-else type="button" class="inline-flex cursor-not-allowed items-center rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400" disabled title="Biodata terkunci setelah pendaftaran disubmit">
                                Edit Biodata
                            </button>
                            <Link :href="route('documents.index')" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                                Next ke Dokumen
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
