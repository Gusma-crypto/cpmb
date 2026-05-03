<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import BiodataForm from './Form.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    registrations: {
        type: Array,
        default: () => [],
    },
    canManageAll: {
        type: Boolean,
        default: false,
    },
    registrationOpen: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    registration_id: '',
    nik: '',
    birth_place: '',
    birth_date: '',
    gender: '',
    religion: '',
    address: '',
    province: '',
    city: '',
    school_name: '',
    school_graduation_year: '',
    parent_name: '',
    parent_phone: '',
    parent_job: '',
});

const submit = () => {
    form.post(route('biodata.store'));
};

const backHref = computed(() => {
    const registrationId = props.registrations?.[0]?.id;

    return !props.canManageAll && registrationId
        ? route('registrations.show', registrationId)
        : route('registrations.index');
});
</script>

<template>
    <Head title="Tambah Biodata" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Tambah Biodata</h1>
                    <Link :href="backHref" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-600 transition hover:bg-gray-50 hover:text-gray-900" aria-label="Kembali" title="Kembali">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" aria-hidden="true">
                            <path d="M15 6 9 12l6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </Link>
                </div>
                <div v-if="!registrationOpen" class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Gelombang pendaftaran belum dibuka. Hubungi panitia PMB untuk informasi lebih lanjut.
                </div>

                <div v-else class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <BiodataForm :form="form" :registrations="registrations" :can-manage-all="canManageAll" @submit="submit" />
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
