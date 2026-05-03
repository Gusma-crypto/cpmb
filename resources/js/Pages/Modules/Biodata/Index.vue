<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import BiodataForm from './Form.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    biodata: {
        type: Object,
        required: true,
    },
    studentBiodata: {
        type: Object,
        default: null,
    },
    registrations: {
        type: Array,
        default: () => [],
    },
    canManageAll: {
        type: Boolean,
        default: false,
    },
    hasBiodata: {
        type: Boolean,
        default: false,
    },
    registrationOpen: {
        type: Boolean,
        default: false,
    },
});

const editing = ref(!props.studentBiodata);
const studentForm = useForm({
    registration_id: props.studentBiodata?.registration_id || '',
    nik: props.studentBiodata?.nik || '',
    birth_place: props.studentBiodata?.birth_place || '',
    birth_date: props.studentBiodata?.birth_date?.slice(0, 10) || '',
    gender: props.studentBiodata?.gender || '',
    religion: props.studentBiodata?.religion || '',
    address: props.studentBiodata?.address || '',
    province: props.studentBiodata?.province || '',
    city: props.studentBiodata?.city || '',
    school_name: props.studentBiodata?.school_name || '',
    school_graduation_year: props.studentBiodata?.school_graduation_year || '',
    parent_name: props.studentBiodata?.parent_name || '',
    parent_phone: props.studentBiodata?.parent_phone || '',
    parent_job: props.studentBiodata?.parent_job || '',
});

const backHref = computed(() => {
    const registrationId = props.studentBiodata?.registration?.id
        || props.registrations?.[0]?.id
        || props.biodata.data?.[0]?.registration?.id;

    return !props.canManageAll && registrationId
        ? route('registrations.show', registrationId)
        : route('registrations.index');
});
const editableStatuses = ['draft', 'revision_required'];
const canEditBiodata = (item) => props.canManageAll || editableStatuses.includes(item.registration?.status);
const canEditStudentBiodata = computed(() => !props.studentBiodata || editableStatuses.includes(props.studentBiodata.registration?.status));
const studentSubmitLabel = computed(() => {
    if (!props.studentBiodata) {
        return 'Save';
    }

    return editing.value ? 'Save' : 'Edit';
});

const submitStudentForm = () => {
    if (props.studentBiodata && !editing.value) {
        if (canEditStudentBiodata.value) {
            editing.value = true;
        }

        return;
    }

    if (props.studentBiodata) {
        studentForm.put(route('biodata.update', props.studentBiodata.id), {
            onSuccess: () => {
                editing.value = false;
            },
        });

        return;
    }

    studentForm.post(route('biodata.store'));
};
</script>

<template>
    <Head title="Biodata Mahasiswa" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto px-4 sm:px-6 lg:px-8" :class="canManageAll ? 'max-w-7xl' : 'max-w-4xl'">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Biodata Mahasiswa</h1>
                    <div class="flex flex-wrap justify-end gap-3">
                        <Link :href="backHref" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-50 hover:text-gray-900" aria-label="Kembali" title="Kembali">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                                <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </Link>
                        <Link v-if="!canManageAll" :href="route('documents.index')" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                            Next
                        </Link>
                        <Link v-if="canManageAll" :href="route('biodata.create')" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                            Tambah Biodata
                        </Link>
                    </div>
                </div>

                <div v-if="!canManageAll && !registrationOpen && !studentBiodata" class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Gelombang pendaftaran belum dibuka. Hubungi panitia PMB untuk informasi lebih lanjut.
                </div>

                <div v-else-if="!canManageAll" class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div v-if="studentBiodata && !canEditStudentBiodata" class="mb-6 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                        Biodata sudah terkunci karena pendaftaran telah disubmit.
                    </div>

                    <BiodataForm
                        :form="studentForm"
                        :registrations="registrations"
                        :can-manage-all="false"
                        :disabled="Boolean(studentBiodata) && !editing"
                        :show-cancel="false"
                        :submit-disabled="Boolean(studentBiodata) && !canEditStudentBiodata"
                        :submit-label="studentSubmitLabel"
                        @submit="submitStudentForm"
                    />
                </div>

                <div v-else class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">No. Registrasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">NIK</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Asal Sekolah</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="item in biodata.data" :key="item.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ item.registration?.registration_number || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ item.registration?.user?.name || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ item.nik }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ item.school_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('biodata.show', item.id)" class="font-medium text-blue-600 hover:text-blue-900">Detail</Link>
                                        <Link v-if="canEditBiodata(item)" :href="route('biodata.edit', item.id)" class="ms-4 font-medium text-blue-600 hover:text-blue-900">Edit</Link>
                                        <span v-else class="ms-4 cursor-not-allowed font-medium text-gray-400" title="Biodata terkunci setelah pendaftaran disubmit">Edit</span>
                                    </td>
                                </tr>
                                <tr v-if="biodata.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada biodata mahasiswa.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link v-for="link in biodata.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
