<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    registration: {
        type: Object,
        required: true,
    },
    canManageAll: {
        type: Boolean,
        default: false,
    },
    requiredDocs: {
        type: Array,
        default: () => [],
    },
    submitCaptcha: {
        type: String,
        default: null,
    },
    hasExamCard: {
        type: Boolean,
        default: false,
    },
});

const biodata = computed(() => props.registration.biodata || {});
const submitForm = useForm({
    review_biodata: false,
    review_ijazah: false,
    review_ktp: false,
    review_photo: false,
    declaration: false,
    captcha: '',
});
const revisionForm = useForm({
    revision_notes: '',
});
const isStudentDraft = computed(() => !props.canManageAll && ['draft', 'revision_required'].includes(props.registration.status));
const adminStatusInfo = computed(() => {
    const statuses = {
        submitted: {
            label: 'Pendaftaran terkirim',
            badge: 'Submitted',
            description: 'Pendaftaran Anda sudah dikirim dan menunggu review panitia.',
            className: 'border-blue-200 bg-blue-50 text-blue-800',
            buttonClass: 'bg-blue-600 text-white',
        },
        under_review: {
            label: 'Sedang direview',
            badge: 'Under Review',
            description: 'Panitia sedang memeriksa data dan dokumen pendaftaran Anda.',
            className: 'border-yellow-200 bg-yellow-50 text-yellow-800',
            buttonClass: 'bg-yellow-600 text-white',
        },
        revision_required: {
            label: 'Perlu revisi',
            badge: 'Revisi',
            description: 'Pendaftaran dikembalikan untuk diperbaiki. Baca catatan revisi dari panitia.',
            className: 'border-amber-200 bg-amber-50 text-amber-800',
            buttonClass: 'bg-amber-600 text-white',
        },
        verified: {
            label: 'Data sudah terverifikasi',
            badge: 'Terverifikasi',
            description: 'Pendaftaran Anda sudah diverifikasi. Anda bisa lanjut ke tahap seleksi CBT saat jadwal ujian aktif.',
            className: 'border-green-200 bg-green-50 text-green-800',
            buttonClass: 'bg-green-600 text-white',
        },
        rejected: {
            label: 'Data pendaftaran ditolak',
            badge: 'Ditolak',
            description: 'Pendaftaran Anda ditolak oleh admin. Silakan hubungi panitia PMB untuk informasi lebih lanjut.',
            className: 'border-red-200 bg-red-50 text-red-800',
            buttonClass: 'bg-red-600 text-white',
        },
        exam_ready: {
            label: 'Siap ujian',
            badge: 'Exam Ready',
            description: 'Pendaftaran sudah valid. Silakan pantau jadwal, ruang, kartu ujian, dan seleksi CBT.',
            className: 'border-blue-200 bg-blue-50 text-blue-800',
            buttonClass: 'bg-blue-600 text-white',
        },
    };

    return statuses[props.registration.status] || null;
});
const uploadedDocumentTypes = computed(() => props.registration.documents.map((document) => document.type));
const canVerifyRegistration = computed(() => props.canManageAll && ['submitted', 'under_review'].includes(props.registration.status));
const canStartReview = computed(() => props.canManageAll && props.registration.status === 'submitted');
const canRequestRevision = computed(() => props.canManageAll && ['submitted', 'under_review'].includes(props.registration.status));
const canApproveDocuments = computed(() => props.canManageAll && ['submitted', 'under_review'].includes(props.registration.status));
const hasDocument = (type) => uploadedDocumentTypes.value.includes(type);
const reviewItems = computed(() => [
    {
        key: 'review_biodata',
        label: 'Biodata sudah sesuai',
        description: props.registration.biodata ? 'Data biodata sudah diisi dan siap dikunci saat submit.' : 'Biodata belum diisi.',
        complete: Boolean(props.registration.biodata),
    },
    {
        key: 'review_ijazah',
        label: 'Foto ijazah',
        description: hasDocument('ijazah') ? 'Dokumen ijazah sudah diunggah.' : 'Dokumen ijazah belum diunggah.',
        complete: hasDocument('ijazah'),
    },
    {
        key: 'review_ktp',
        label: 'KTP',
        description: hasDocument('ktp') ? 'Dokumen KTP sudah diunggah.' : 'Dokumen KTP belum diunggah.',
        complete: hasDocument('ktp'),
    },
    {
        key: 'review_photo',
        label: 'Pasphoto 3x4 background merah',
        description: hasDocument('photo') ? 'Pasphoto sudah diunggah.' : 'Pasphoto belum diunggah.',
        complete: hasDocument('photo'),
    },
]);

const registrationRows = computed(() => [
    ['No. Registrasi', props.registration.registration_number],
    ['Nama', props.registration.user?.name],
    ['Email', props.registration.user?.email],
    ['No. HP/WA', props.registration.user?.phone],
    ['Status Pendaftaran', statusLabel(props.registration.status)],
    ['Gelombang', props.registration.wave],
    ['Dibuat', formatDateTime(props.registration.created_at)],
]);

const biodataRows = computed(() => [
    ['NIK', biodata.value.nik],
    ['Tempat, Tanggal Lahir', [biodata.value.birth_place, formatDate(biodata.value.birth_date)].filter(Boolean).join(', ')],
    ['Jenis Kelamin', genderLabel(biodata.value.gender)],
    ['Agama', biodata.value.religion],
    ['Provinsi', biodata.value.province],
    ['Kota', biodata.value.city],
    ['Sekolah', biodata.value.school_name],
    ['Tahun Lulus', biodata.value.school_graduation_year],
    ['Nama Orang Tua', biodata.value.parent_name],
    ['Telepon Orang Tua', biodata.value.parent_phone],
    ['Pekerjaan Orang Tua', biodata.value.parent_job],
    ['Alamat', biodata.value.address],
]);

function formatDate(value) {
    return value ? new Date(value).toLocaleDateString('id-ID', { dateStyle: 'medium' }) : '';
}

function formatDateTime(value) {
    return value ? new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-';
}

function statusLabel(value) {
    return ({
        draft: 'Draft',
        submitted: 'Submitted',
        under_review: 'Under Review',
        revision_required: 'Revision Required',
        verified: 'Verified',
        rejected: 'Rejected',
        exam_ready: 'Exam Ready',
    }[value] || value || '-');
}

function genderLabel(value) {
    return ({ male: 'Laki-laki', female: 'Perempuan' }[value] || value || '-');
}

function isImage(document) {
    return document.mime_type?.startsWith('image/');
}

function postAction(routeName) {
    router.post(route(routeName, props.registration.id), {}, { preserveScroll: true });
}

function requestRevision() {
    const notes = prompt('Catatan revisi untuk mahasiswa:');

    if (!notes) return;

    revisionForm.revision_notes = notes;
    revisionForm.post(route('registrations.revision', props.registration.id), {
        preserveScroll: true,
    });
}

function submitRegistration() {
    if (!confirm('Submit pendaftaran sekarang? Setelah submit, biodata inti dan dokumen inti tidak dapat diubah lagi.')) {
        return;
    }

    submitForm.post(route('registrations.submit', props.registration.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Detail Pendaftaran" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Detail Pendaftaran</h1>
                    <Link :href="route('registrations.index')" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                        Kembali
                    </Link>
                </div>

                <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-900">Detail Pendaftaran</h2>
                    <dl class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="[label, value] in registrationRows" :key="label">
                            <dt class="text-sm font-medium text-gray-500">{{ label }}</dt>
                            <dd class="mt-1 break-words text-sm text-gray-900">{{ value || '-' }}</dd>
                        </div>
                    </dl>
                    <div v-if="registration.status === 'revision_required' && registration.revision_notes" class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        <p class="font-semibold">Catatan revisi dari panitia</p>
                        <p class="mt-2 whitespace-pre-line leading-6">{{ registration.revision_notes }}</p>
                    </div>
                </section>

                <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">Detail Biodata</h2>
                        <Link
                            v-if="registration.biodata"
                            :href="`${route('biodata.show', registration.biodata.id)}?registration=${registration.id}`"
                            class="text-sm font-medium text-blue-600 hover:text-blue-900"
                        >
                            Lihat Detail Biodata
                        </Link>
                    </div>

                    <dl v-if="registration.biodata" class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="[label, value] in biodataRows" :key="label" :class="{ 'lg:col-span-3': label === 'Alamat' }">
                            <dt class="text-sm font-medium text-gray-500">{{ label }}</dt>
                            <dd class="mt-1 break-words text-sm text-gray-900">{{ value || '-' }}</dd>
                        </div>
                    </dl>

                    <div v-else class="mt-5 rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                        Biodata belum dilengkapi.
                    </div>
                </section>

                <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-900">Detail Dokumen</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Dokumen wajib: {{ requiredDocs.join(', ') || '-' }}.
                    </p>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Tipe</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama File</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ukuran</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="document in registration.documents" :key="document.id">
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ document.type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ document.original_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ document.size_kb ? `${document.size_kb} KB` : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ document.status || '-' }}</td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <a
                                            v-if="isImage(document)"
                                            :href="route('documents.view', document.id)"
                                            target="_blank"
                                            class="font-medium text-emerald-600 hover:text-emerald-900"
                                        >
                                            Lihat Gambar
                                        </a>
                                        <Link :href="`${route('documents.show', document.id)}?registration=${registration.id}`" class="font-medium text-indigo-600 hover:text-indigo-900">
                                            Detail
                                        </Link>
                                        <a :href="route('documents.download', document.id)" class="ms-4 font-medium text-blue-600 hover:text-blue-900">
                                            Download
                                        </a>
                                    </td>
                                </tr>
                                <tr v-if="registration.documents.length === 0">
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">
                                        Belum ada dokumen.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="registration.documents.some(isImage)" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <a
                            v-for="document in registration.documents.filter(isImage)"
                            :key="`preview-${document.id}`"
                            :href="route('documents.view', document.id)"
                            target="_blank"
                            class="block overflow-hidden rounded-md border border-gray-200 bg-gray-50"
                        >
                            <img :src="route('documents.view', document.id)" :alt="document.original_name" class="h-48 w-full object-contain" />
                            <div class="border-t bg-white px-3 py-2 text-sm text-gray-700">
                                {{ document.type }} - {{ document.original_name }}
                            </div>
                        </a>
                    </div>
                </section>

                <section v-if="isStudentDraft" class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h2 class="text-lg font-semibold text-gray-900">Review Sebelum Submit</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Pastikan semua data sudah benar. Setelah submit, biodata inti dan dokumen inti tidak dapat diubah atau diunggah ulang.
                    </p>

                    <div class="mt-5 divide-y divide-gray-200 rounded-md border border-gray-200">
                        <label v-for="item in reviewItems" :key="item.key" class="flex items-start justify-between gap-4 p-4">
                            <span>
                                <span class="block text-sm font-semibold text-gray-900">{{ item.label }}</span>
                                <span class="mt-1 block text-sm" :class="item.complete ? 'text-gray-600' : 'text-red-600'">
                                    {{ item.description }}
                                </span>
                                <span v-if="submitForm.errors[item.key]" class="mt-1 block text-sm text-red-600">
                                    {{ submitForm.errors[item.key] }}
                                </span>
                            </span>
                            <input
                                v-model="submitForm[item.key]"
                                type="checkbox"
                                class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                                :disabled="!item.complete"
                            />
                        </label>
                    </div>

                    <label class="mt-5 flex items-start gap-3 rounded-md border border-gray-200 p-4">
                        <input v-model="submitForm.declaration" type="checkbox" class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Pernyataan kebenaran data</span>
                            <span class="mt-1 block text-sm text-gray-600">
                                Dengan ini saya menyatakan bahwa data yang diisi benar dan dokumen yang diunggah sesuai dengan ketentuan PMB.
                            </span>
                            <span v-if="submitForm.errors.declaration" class="mt-1 block text-sm text-red-600">
                                {{ submitForm.errors.declaration }}
                            </span>
                        </span>
                    </label>

                    <div class="mt-5 grid gap-3 sm:grid-cols-[160px_minmax(0,1fr)] sm:items-center">
                        <div class="rounded-md border border-dashed border-gray-400 bg-gray-50 px-4 py-3 text-center font-mono text-xl font-bold tracking-[0.35em] text-gray-900">
                            {{ submitCaptcha || '-----' }}
                        </div>
                        <div>
                            <label for="captcha" class="block text-sm font-semibold text-gray-700">Masukkan kode captcha</label>
                            <input
                                id="captcha"
                                v-model="submitForm.captcha"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 text-sm uppercase shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Ketik kode di sebelah kiri"
                            />
                            <p v-if="submitForm.errors.captcha" class="mt-1 text-sm text-red-600">{{ submitForm.errors.captcha }}</p>
                        </div>
                    </div>
                </section>

                <section class="flex flex-wrap items-center gap-3 bg-white p-6 shadow-sm sm:rounded-lg">
                    <button
                        v-if="['draft', 'revision_required'].includes(registration.status) && !canManageAll"
                        type="button"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white"
                        :class="{ 'opacity-25': submitForm.processing }"
                        :disabled="submitForm.processing"
                        @click="submitRegistration"
                    >
                        Submit
                    </button>
                    <button
                        v-else-if="['draft', 'revision_required'].includes(registration.status)"
                        type="button"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white"
                        @click="postAction('registrations.submit')"
                    >
                        Submit
                    </button>
                    <template v-if="canManageAll">
                        <button
                            type="button"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!canStartReview"
                            title="Tandai pendaftaran sedang direview"
                            @click="postAction('registrations.review')"
                        >
                            Review
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!canVerifyRegistration"
                            :title="canVerifyRegistration ? 'Verifikasi berkas pendaftaran' : 'Verifikasi hanya untuk status submitted atau under review'"
                            @click="postAction('registrations.verify')"
                        >
                            Verifikasi
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="!canRequestRevision"
                            title="Kembalikan ke mahasiswa dengan catatan revisi"
                            @click="requestRevision"
                        >
                            Minta Revisi
                        </button>
                        <button type="button" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white" @click="postAction('registrations.reject')">
                            Tolak
                        </button>
                        <p v-if="!canApproveDocuments" class="w-full text-sm text-amber-700">
                            Aksi review/verifikasi hanya aktif untuk status submitted atau under_review.
                        </p>
                    </template>
                    <div v-if="!canManageAll && adminStatusInfo" class="w-full rounded-md border p-4" :class="adminStatusInfo.className">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold">{{ adminStatusInfo.label }}</p>
                                <p class="mt-1 text-sm">{{ adminStatusInfo.description }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold" :class="adminStatusInfo.buttonClass">
                                    {{ adminStatusInfo.badge }}
                                </span>
                                <Link
                                    v-if="hasExamCard"
                                    :href="route('student.exam-card.index')"
                                    class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800"
                                >
                                    Cetak Kartu Ujian
                                </Link>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </RoleLayout>
</template>
