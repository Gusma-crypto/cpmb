<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    registrations: {
        type: Object,
        required: true,
    },
    canManageAll: {
        type: Boolean,
        default: false,
    },
    hasRegistration: {
        type: Boolean,
        default: false,
    },
    existingRegistration: {
        type: Object,
        default: null,
    },
    requiredDocs: {
        type: Array,
        default: () => [],
    },
});

const canCreateRegistration = computed(() => props.canManageAll || !props.hasRegistration);
const uploadedDocumentTypes = computed(() => props.existingRegistration?.documents?.map((document) => document.type) || []);
const missingDocuments = computed(() => props.requiredDocs.filter((type) => !uploadedDocumentTypes.value.includes(type)));
const hasRequiredDocuments = computed(() => props.requiredDocs.length > 0 && missingDocuments.value.length === 0);
const hasBiodata = computed(() => Boolean(props.existingRegistration?.biodata));
const hasSubmitted = computed(() => ['submitted', 'under_review', 'revision_required', 'verified', 'exam_ready'].includes(props.existingRegistration?.status));
const isRevisionRequired = computed(() => props.existingRegistration?.status === 'revision_required');
const isVerified = computed(() => ['verified', 'exam_ready'].includes(props.existingRegistration?.status));
const isExamReady = computed(() => props.existingRegistration?.status === 'exam_ready');

const studentFlowSteps = computed(() => [
    {
        icon: 'registration',
        title: 'Pendaftaran',
        description: props.existingRegistration
            ? props.existingRegistration.registration_number
            : 'Buat data pendaftaran awal',
        complete: Boolean(props.existingRegistration),
        active: !props.existingRegistration,
        href: props.existingRegistration ? route('registrations.show', props.existingRegistration.id) : route('registrations.create'),
        action: props.existingRegistration ? 'Lihat' : 'Daftar',
    },
    {
        icon: 'biodata',
        title: 'Biodata',
        description: hasBiodata.value ? 'Biodata sudah diisi' : 'Lengkapi identitas dan data sekolah',
        complete: hasBiodata.value,
        active: Boolean(props.existingRegistration) && !hasBiodata.value,
        href: route('biodata.index'),
        action: hasBiodata.value ? 'Lihat' : 'Isi Biodata',
    },
    {
        icon: 'documents',
        title: 'Dokumen',
        description: hasRequiredDocuments.value
            ? 'Foto, KTP, dan ijazah sudah diupload'
            : `Belum lengkap: ${missingDocuments.value.join(', ') || 'dokumen wajib'}`,
        complete: hasRequiredDocuments.value,
        active: hasBiodata.value && !hasRequiredDocuments.value,
        href: route('documents.index'),
        action: hasRequiredDocuments.value ? 'Lihat' : 'Upload',
    },
    {
        icon: 'submit',
        title: 'Review Submit',
        description: hasSubmitted.value ? 'Pendaftaran sudah disubmit' : 'Kirim data untuk diverifikasi',
        complete: hasSubmitted.value,
        active: hasBiodata.value && hasRequiredDocuments.value && !hasSubmitted.value,
        href: props.existingRegistration ? route('registrations.show', props.existingRegistration.id) : route('registrations.index'),
        action: 'Submit',
    },
]);

const badgeClass = (value, type = 'status') => {
    const map = {
        draft: 'bg-gray-100 text-gray-700',
        submitted: 'bg-blue-100 text-blue-700',
        under_review: 'bg-indigo-100 text-indigo-700',
        revision_required: 'bg-amber-100 text-amber-700',
        verified: 'bg-emerald-100 text-emerald-700',
        exam_ready: 'bg-green-100 text-green-700',
        rejected: 'bg-red-100 text-red-700',
        paid: 'bg-green-100 text-green-700',
        pending: 'bg-yellow-100 text-yellow-700',
        unpaid: 'bg-gray-100 text-gray-700',
    };

    return map[value || (type === 'payment' ? 'unpaid' : 'draft')] || map.draft;
};

const statusLabel = (value) => ({
    draft: 'Draft',
    submitted: 'Submitted',
    under_review: 'Under Review',
    revision_required: 'Revision Required',
    verified: 'Verified',
    rejected: 'Rejected',
    exam_ready: 'Exam Ready',
}[value] || value);

const flowStepClass = (step) => {
    if (step.complete) return 'border-green-200 bg-green-50 text-green-800 hover:border-green-300';
    if (step.active) return 'border-blue-200 bg-blue-50 text-blue-800 ring-2 ring-blue-100 hover:border-blue-300';
    return 'border-gray-200 bg-white text-gray-600 hover:border-blue-200 hover:text-blue-700';
};

const notification = computed(() => {
    if (!props.existingRegistration) {
        return {
            title: 'Mulai pendaftaran',
            message: 'Buat data pendaftaran terlebih dahulu untuk mendapatkan nomor registrasi.',
            className: 'border-blue-200 bg-blue-50 text-blue-800',
        };
    }

    if (!hasBiodata.value) {
        return {
            title: 'Biodata belum lengkap',
            message: 'Lengkapi biodata agar Anda bisa melanjutkan upload dokumen dan submit pendaftaran.',
            className: 'border-yellow-200 bg-yellow-50 text-yellow-800',
        };
    }

    if (!hasRequiredDocuments.value) {
        return {
            title: 'Dokumen belum lengkap',
            message: `Upload dokumen wajib yang masih kurang: ${missingDocuments.value.join(', ')}.`,
            className: 'border-yellow-200 bg-yellow-50 text-yellow-800',
        };
    }

    if (!hasSubmitted.value) {
        return {
            title: 'Siap disubmit',
            message: 'Biodata dan dokumen wajib sudah lengkap. Buka detail pendaftaran untuk submit data.',
            className: 'border-blue-200 bg-blue-50 text-blue-800',
        };
    }

    if (isRevisionRequired.value) {
        return {
            title: 'Perlu revisi',
            message: props.existingRegistration?.revision_notes || 'Pendaftaran perlu diperbaiki sesuai catatan panitia.',
            className: 'border-amber-200 bg-amber-50 text-amber-800',
        };
    }

    if (isExamReady.value) {
        return {
            title: 'Siap ujian',
            message: 'Pendaftaran sudah valid. Pantau jadwal, ruang, kartu ujian, dan seleksi CBT dari menu yang tersedia.',
            className: 'border-green-200 bg-green-50 text-green-800',
        };
    }

    if (isVerified.value) {
        return {
            title: 'Pendaftaran terverifikasi',
            message: 'Berkas sudah disetujui. Anda bisa lanjut ke tahap seleksi CBT saat jadwal ujian aktif.',
            className: 'border-green-200 bg-green-50 text-green-800',
        };
    }

    return {
        title: 'Menunggu verifikasi',
        message: 'Data sudah dikirim. Panitia akan memeriksa pendaftaran dan dokumen Anda.',
        className: 'border-blue-200 bg-blue-50 text-blue-800',
    };
});

const documentLabels = {
    photo: 'Foto',
    ktp: 'KTP',
    ijazah: 'Ijazah',
};

const documentChecklist = computed(() => props.requiredDocs.map((type) => ({
    type,
    label: documentLabels[type] || type,
    uploaded: uploadedDocumentTypes.value.includes(type),
})));
</script>

<template>
    <Head title="Data Pendaftaran" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Data Pendaftaran (PMB)</h1>
                    <Link
                        v-if="canCreateRegistration"
                        :href="route('registrations.create')"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700"
                    >
                        Daftar Sekarang
                    </Link>
                    <div v-else class="flex flex-wrap justify-end gap-2">
                        <Link
                            :href="route('registrations.show', existingRegistration.id)"
                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700"
                        >
                            Detail Pendaftaran
                        </Link>
                        <Link
                            v-if="!hasSubmitted || isRevisionRequired"
                            :href="route('biodata.index')"
                            class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                        >
                            Perbaiki Biodata
                        </Link>
                        <Link
                            v-if="!hasSubmitted || isRevisionRequired"
                            :href="route('documents.index')"
                            class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-gray-700"
                        >
                            Perbaiki Dokumen
                        </Link>
                    </div>
                </div>

                <div v-if="!canCreateRegistration" class="mb-6 rounded-md border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    Akun Anda sudah memiliki pendaftaran. Gunakan menu detail, biodata, atau dokumen untuk melengkapi data.
                </div>

                <section v-if="!canManageAll" class="mb-6 bg-white p-5 shadow-sm sm:rounded-lg sm:p-6">
                    <div class="flow-layout">
                        <div class="flow-left">
                            <div class="mb-6">
                                <h2 class="text-xl font-semibold text-gray-900">Alur Pendaftaran</h2>
                                <p class="mt-1 text-sm text-gray-500">Klik langkah untuk membuka halaman terkait.</p>
                            </div>

                            <div class="flow-timeline">
                                <Link
                                    v-for="(step, index) in studentFlowSteps"
                                    :key="step.title"
                                    :href="step.href"
                                    class="flow-step"
                                    :class="flowStepClass(step)"
                                    :style="{ animationDelay: `${index * 90}ms` }"
                                >
                                    <span class="flow-line" aria-hidden="true"></span>
                                    <span class="flow-icon" :class="[step.complete ? 'bg-green-600 text-white' : step.active ? 'bg-blue-600 text-white flow-icon-active' : 'bg-gray-100 text-gray-500']">
                                            <svg v-if="step.icon === 'registration'" class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                                <path d="M7 3h7l5 5v13H7V3Zm7 0v5h5M10 13h6M10 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <svg v-else-if="step.icon === 'biodata'" class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                            </svg>
                                            <svg v-else-if="step.icon === 'documents'" class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                                <path d="M4 5h16v14H4V5Zm3 4h10M7 13h6M7 17h8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <svg v-else-if="step.icon === 'submit'" class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                                <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                                <path d="M20 7 9 18l-5-5" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                    </span>
                                    <span class="flow-title">{{ step.title }}</span>
                                </Link>
                            </div>
                        </div>

                        <div class="flow-right">
                            <div class="rounded-md border px-4 py-3 text-sm" :class="notification.className">
                                <p class="font-semibold">{{ notification.title }}</p>
                                <p class="mt-1 leading-5">{{ notification.message }}</p>
                            </div>

                            <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
                                <h3 class="text-sm font-semibold text-gray-900">Dokumen Wajib</h3>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        v-for="document in documentChecklist"
                                        :key="document.type"
                                        class="rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="document.uploaded ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                                    >
                                        {{ document.label }}: {{ document.uploaded ? 'Sudah' : 'Belum' }}
                                    </span>
                                </div>
                                <Link :href="route('documents.index')" class="mt-4 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">
                                    Upload atau cek dokumen
                                </Link>
                            </div>

                            <div class="rounded-md border border-gray-200 bg-gray-50 p-4">
                                <h3 class="text-sm font-semibold text-gray-900">Bantuan Singkat</h3>
                                <ul class="mt-3 space-y-2 text-sm leading-5 text-gray-600">
                                    <li>Isi biodata sesuai dokumen resmi.</li>
                                    <li>Pastikan nomor telepon orang tua diawali 08 atau 07.</li>
                                    <li>Upload dokumen dengan gambar/file yang jelas terbaca.</li>
                                    <li>Submit hanya bisa dilakukan setelah biodata dan dokumen wajib lengkap.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">No. Registrasi</th>
                                    <th v-if="canManageAll" class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Didaftarkan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="registration in registrations.data" :key="registration.id">
                                    <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-700">
                                        {{ registration.registration_number }}
                                    </td>
                                    <td v-if="canManageAll" class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ registration.user?.name || '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="badgeClass(registration.status)">
                                            {{ statusLabel(registration.status) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ new Date(registration.created_at).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('registrations.show', registration.id)" class="font-medium text-indigo-600 hover:text-indigo-900">
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="registrations.data.length === 0">
                                    <td :colspan="canManageAll ? 5 : 4" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Belum ada data pendaftaran.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link
                            v-for="link in registrations.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="rounded-md px-3 py-2 text-sm"
                            :class="[
                                link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700',
                                !link.url ? 'pointer-events-none opacity-50' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>

<style scoped>
.flow-layout {
    display: grid;
    grid-template-columns: minmax(340px, 1.2fr) minmax(280px, .8fr);
    gap: 28px;
    align-items: start;
}

.flow-left {
    min-height: 100%;
    border-radius: 8px;
    padding: 24px;
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
    border: 1px solid #e5edf8;
}

.flow-right {
    display: grid;
    gap: 16px;
}

.flow-timeline {
    position: relative;
    display: grid;
    gap: 18px;
    max-width: 520px;
}

.flow-step {
    min-height: 76px;
    display: grid;
    grid-template-columns: 58px minmax(0, 1fr);
    align-items: center;
    gap: 18px;
    position: relative;
    border-width: 1px;
    border-radius: 8px;
    padding: 12px 18px 12px 12px;
    text-decoration: none;
    box-shadow: 0 7px 18px rgba(31, 45, 61, .06);
    animation: flowRise .42s ease both;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, color .18s ease;
}

.flow-step:hover {
    transform: translateX(5px);
    box-shadow: 0 12px 28px rgba(31, 45, 61, .12);
}

.flow-line {
    position: absolute;
    left: 40px;
    top: 66px;
    bottom: -20px;
    width: 3px;
    border-radius: 999px;
    background: #dbeafe;
}

.flow-step:last-child .flow-line {
    display: none;
}

.flow-icon {
    width: 58px;
    height: 58px;
    display: grid;
    place-items: center;
    position: relative;
    z-index: 1;
    border-radius: 999px;
}

.flow-icon-active::after {
    content: "";
    position: absolute;
    inset: -5px;
    border-radius: inherit;
    border: 2px solid rgba(37, 99, 235, .25);
    animation: flowPulse 1.4s ease-out infinite;
}

.flow-title {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 17px;
    font-weight: 700;
}

@keyframes flowRise {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes flowPulse {
    from {
        opacity: .9;
        transform: scale(.92);
    }
    to {
        opacity: 0;
        transform: scale(1.25);
    }
}

@media (max-width: 900px) {
    .flow-layout {
        grid-template-columns: 1fr;
    }

    .flow-left {
        padding: 18px;
    }

    .flow-timeline {
        max-width: none;
    }
}

@media (max-width: 520px) {
    .flow-step {
        min-height: 68px;
        grid-template-columns: 50px minmax(0, 1fr);
        gap: 14px;
    }

    .flow-icon {
        width: 50px;
        height: 50px;
    }

    .flow-line {
        left: 36px;
        top: 60px;
    }

    .flow-title {
        font-size: 15px;
    }
}
</style>
