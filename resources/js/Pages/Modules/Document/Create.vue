<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    registrations: {
        type: Array,
        default: () => [],
    },
    types: {
        type: Object,
        required: true,
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
    type: '',
    file: null,
});

const maxFileSize = 2 * 1024 * 1024;
const maxFileSizeMessage = 'Ukuran file maksimal 2 MB.';
const previewUrl = ref('');
const previewType = ref('');
const previewName = ref('');

const clearPreview = () => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = '';
    previewType.value = '';
    previewName.value = '';
};

const handleFileChange = (event) => {
    const file = event.target.files?.[0] || null;

    form.clearErrors('file');
    clearPreview();

    if (file && file.size > maxFileSize) {
        form.file = null;
        event.target.value = '';
        form.setError('file', maxFileSizeMessage);

        return;
    }

    form.file = file;

    if (file) {
        previewUrl.value = URL.createObjectURL(file);
        previewType.value = file.type;
        previewName.value = file.name;
    }
};

const submit = () => {
    if (form.file && form.file.size > maxFileSize) {
        form.setError('file', maxFileSizeMessage);

        return;
    }

    form.post(route('documents.store'), {
        forceFormData: true,
    });
};

onBeforeUnmount(clearPreview);

const backHref = computed(() => {
    const registrationId = props.registrations?.[0]?.id;

    return !props.canManageAll && registrationId
        ? route('registrations.show', registrationId)
        : route('documents.index');
});
</script>

<template>
    <Head title="Upload Dokumen" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Upload Dokumen</h1>
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
                    <form class="space-y-6" @submit.prevent="submit">
                        <div v-if="canManageAll">
                            <InputLabel for="registration_id" value="Pendaftaran" />
                            <select id="registration_id" v-model="form.registration_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Pilih pendaftaran</option>
                                <option v-for="registration in registrations" :key="registration.id" :value="registration.id">
                                    {{ registration.registration_number }} - {{ registration.user?.name || '-' }}
                                </option>
                            </select>
                            <InputError :message="form.errors.registration_id" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="type" value="Jenis Dokumen" />
                            <select id="type" v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                                <option value="">Pilih jenis dokumen</option>
                                <option v-for="(label, value) in types" :key="value" :value="value">{{ label }}</option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="file" value="File PDF/JPG" />
                            <label for="file" class="mt-1 flex min-h-72 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-md border-2 border-dashed border-blue-200 bg-blue-50/60 p-4 text-center transition hover:border-blue-300 hover:bg-blue-50">
                                <template v-if="previewUrl">
                                    <img v-if="previewType.startsWith('image/')" :src="previewUrl" :alt="previewName" class="max-h-64 w-full object-contain" />
                                    <iframe v-else-if="previewType === 'application/pdf'" :src="previewUrl" class="h-64 w-full rounded-md bg-white" title="Preview PDF"></iframe>
                                    <div v-else class="grid h-64 place-items-center px-3 text-center text-sm text-gray-500">
                                        Preview tidak tersedia.
                                    </div>
                                </template>
                                <template v-else>
                                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M12 16V4m0 0-4 4m4-4 4 4M5 16v3h14v-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                    <span class="mt-4 text-sm font-semibold text-gray-900">Klik untuk memilih file</span>
                                    <span class="mt-1 text-sm text-gray-500">Area ini adalah tombol pilih file.</span>
                                </template>
                            </label>
                            <input id="file" type="file" accept=".pdf,.jpg,.jpeg,application/pdf,image/jpeg" class="sr-only" required @change="handleFileChange" />
                            <div class="mt-3 rounded-md bg-gray-50 px-3 py-2 text-xs text-gray-600">
                                <p class="truncate font-semibold text-gray-700">{{ previewName || 'Belum ada file dipilih' }}</p>
                                <p class="mt-1">Format PDF/JPG, maksimal 2 MB. Klik area di atas untuk mengganti file.</p>
                            </div>
                            <InputError :message="form.errors.file" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                            <Link :href="route('documents.index')" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Save File</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
