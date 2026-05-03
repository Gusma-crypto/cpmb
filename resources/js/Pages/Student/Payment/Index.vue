<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    registration: {
        type: Object,
        default: null,
    },
    paymentVerification: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    bank_name: props.paymentVerification?.bank_name || '',
    account_name: props.paymentVerification?.account_name || '',
    transfer_amount: props.paymentVerification?.transfer_amount || '',
    transfer_date: props.paymentVerification?.transfer_date?.slice(0, 10) || '',
    file: null,
});

const previewUrl = ref('');
const previewType = ref('');
const previewName = ref('');
const maxFileSize = 2 * 1024 * 1024;

const statusClass = computed(() => ({
    pending: 'bg-yellow-100 text-yellow-700',
    verified: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    paid: 'bg-green-100 text-green-700',
    unpaid: 'bg-gray-100 text-gray-700',
}[props.paymentVerification?.status] || 'bg-gray-100 text-gray-700'));

const canUpload = computed(() => {
    if (!props.registration) return false;
    if (props.registration.status !== 'verified') return false;
    if (props.registration.payment_status === 'paid') return false;

    return props.paymentVerification?.status !== 'pending';
});

const message = computed(() => {
    if (!props.registration) return 'Anda belum memiliki data pendaftaran.';
    if (props.registration.status !== 'verified') {
        return 'Upload bukti pembayaran dapat dilakukan setelah pendaftaran diverifikasi panitia.';
    }
    if (props.registration.payment_status === 'paid') return 'Pembayaran sudah diverifikasi.';
    if (props.paymentVerification?.status === 'pending') return 'Bukti pembayaran sedang menunggu verifikasi admin.';
    if (props.paymentVerification?.status === 'rejected') return 'Bukti pembayaran ditolak. Silakan unggah ulang bukti yang benar.';

    return 'Unggah bukti transfer pendaftaran untuk diverifikasi admin.';
});

const clearPreview = () => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);

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
        form.setError('file', 'Ukuran file maksimal 2 MB.');
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
    form.post(route('student.payments.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset('file');
            clearPreview();
        },
    });
};

onBeforeUnmount(clearPreview);
</script>

<template>
    <Head title="Pembayaran" />

    <StudentLayout>
        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Pembayaran Pendaftaran</h1>
                        <p class="mt-1 text-sm text-gray-500">Upload bukti transfer dan tunggu verifikasi admin.</p>
                    </div>
                    <Link :href="route('student.registrations.index')" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                        Lihat Pendaftaran
                    </Link>
                </div>

                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <p class="text-sm font-medium text-gray-500">No. Registrasi</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ registration?.registration_number || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status Pendaftaran</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ registration?.status || '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status Pembayaran</p>
                            <span class="mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold" :class="statusClass">
                                {{ paymentVerification?.status || registration?.payment_status || 'unpaid' }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-5 rounded-md border px-4 py-3 text-sm" :class="canUpload ? 'border-blue-200 bg-blue-50 text-blue-800' : 'border-yellow-200 bg-yellow-50 text-yellow-800'">
                        {{ message }}
                    </div>
                </section>

                <section v-if="paymentVerification" class="rounded-lg bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Riwayat Bukti Pembayaran</h2>
                    <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <dt class="font-medium text-gray-500">Bank</dt>
                            <dd class="mt-1 text-gray-900">{{ paymentVerification.bank_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Nama Rekening</dt>
                            <dd class="mt-1 text-gray-900">{{ paymentVerification.account_name }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Nominal</dt>
                            <dd class="mt-1 text-gray-900">Rp {{ Number(paymentVerification.transfer_amount || 0).toLocaleString('id-ID') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Tanggal Transfer</dt>
                            <dd class="mt-1 text-gray-900">{{ paymentVerification.transfer_date?.slice(0, 10) }}</dd>
                        </div>
                    </dl>
                    <p v-if="paymentVerification.notes" class="mt-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                        Catatan admin: {{ paymentVerification.notes }}
                    </p>
                    <a
                        v-if="paymentVerification.proof_document"
                        :href="route('documents.view', paymentVerification.proof_document.id)"
                        target="_blank"
                        class="mt-5 inline-flex rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Lihat Bukti
                    </a>
                </section>

                <section v-if="canUpload" class="rounded-lg bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-gray-900">Upload Bukti Pembayaran</h2>
                    <form class="mt-5 space-y-5" @submit.prevent="submit">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700">Nama Bank</label>
                                <input id="bank_name" v-model="form.bank_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <InputError :message="form.errors.bank_name" class="mt-2" />
                            </div>
                            <div>
                                <label for="account_name" class="block text-sm font-medium text-gray-700">Nama Pemilik Rekening</label>
                                <input id="account_name" v-model="form.account_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <InputError :message="form.errors.account_name" class="mt-2" />
                            </div>
                            <div>
                                <label for="transfer_amount" class="block text-sm font-medium text-gray-700">Nominal Transfer</label>
                                <input id="transfer_amount" v-model="form.transfer_amount" type="number" min="1" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <InputError :message="form.errors.transfer_amount" class="mt-2" />
                            </div>
                            <div>
                                <label for="transfer_date" class="block text-sm font-medium text-gray-700">Tanggal Transfer</label>
                                <input id="transfer_date" v-model="form.transfer_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                                <InputError :message="form.errors.transfer_date" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label for="payment_file" class="block text-sm font-medium text-gray-700">Bukti Transfer PDF/JPG</label>
                            <label for="payment_file" class="mt-1 flex min-h-64 cursor-pointer flex-col items-center justify-center overflow-hidden rounded-md border-2 border-dashed border-blue-200 bg-blue-50/60 p-4 text-center transition hover:border-blue-300 hover:bg-blue-50">
                                <template v-if="previewUrl">
                                    <img v-if="previewType.startsWith('image/')" :src="previewUrl" :alt="previewName" class="max-h-56 w-full object-contain" />
                                    <iframe v-else-if="previewType === 'application/pdf'" :src="previewUrl" class="h-56 w-full rounded-md bg-white" title="Preview bukti pembayaran"></iframe>
                                </template>
                                <template v-else>
                                    <span class="text-sm font-semibold text-gray-900">Klik untuk memilih file</span>
                                    <span class="mt-1 text-sm text-gray-500">Format PDF/JPG, maksimal 2 MB.</span>
                                </template>
                            </label>
                            <input id="payment_file" type="file" accept=".pdf,.jpg,.jpeg,application/pdf,image/jpeg" class="sr-only" required @change="handleFileChange" />
                            <p class="mt-2 truncate text-xs text-gray-500">{{ previewName || 'Belum ada file dipilih' }}</p>
                            <InputError :message="form.errors.file" class="mt-2" />
                        </div>

                        <div class="flex justify-end border-t border-gray-200 pt-4">
                            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50" :disabled="form.processing">
                                Upload Bukti
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </StudentLayout>
</template>
