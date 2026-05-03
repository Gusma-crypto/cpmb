<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import BiodataForm from './Form.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    studentBiodata: {
        type: Object,
        required: true,
    },
    registrations: {
        type: Array,
        default: () => [],
    },
    canManageAll: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    registration_id: props.studentBiodata.registration_id || '',
    nik: props.studentBiodata.nik || '',
    birth_place: props.studentBiodata.birth_place || '',
    birth_date: props.studentBiodata.birth_date?.slice(0, 10) || '',
    gender: props.studentBiodata.gender || '',
    religion: props.studentBiodata.religion || '',
    address: props.studentBiodata.address || '',
    province: props.studentBiodata.province || '',
    city: props.studentBiodata.city || '',
    school_name: props.studentBiodata.school_name || '',
    school_graduation_year: props.studentBiodata.school_graduation_year || '',
    parent_name: props.studentBiodata.parent_name || '',
    parent_phone: props.studentBiodata.parent_phone || '',
    parent_job: props.studentBiodata.parent_job || '',
});

const submit = () => {
    form.put(route('biodata.update', props.studentBiodata.id));
};

const backHref = computed(() => props.studentBiodata.registration?.id
    ? route('registrations.show', props.studentBiodata.registration.id)
    : route('biodata.show', props.studentBiodata.id));
</script>

<template>
    <Head title="Edit Biodata" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Biodata</h1>
                    <Link :href="backHref" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-50 hover:text-gray-900" aria-label="Kembali" title="Kembali">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                            <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </Link>
                </div>
                <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <BiodataForm :form="form" :registrations="registrations" :can-manage-all="canManageAll" submit-label="Update" @submit="submit" />
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
