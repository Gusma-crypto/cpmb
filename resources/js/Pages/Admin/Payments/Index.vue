<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    payments: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: '' }),
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
});

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
});

const statusClass = (status) => ({
    pending: 'bg-yellow-100 text-yellow-700',
    verified: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
}[status] || 'bg-gray-100 text-gray-700');

const submitFilters = () => {
    router.get(route('admin.payments.index'), {
        search: form.search,
        status: form.status,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    router.get(route('admin.payments.index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const verifyPayment = (payment) => {
    if (!confirm(`Verifikasi pembayaran ${payment.registration?.user?.name || 'peserta'}?`)) return;

    router.post(route('admin.payments.verify', payment.id), {}, { preserveScroll: true });
};

const rejectPayment = (payment) => {
    const notes = prompt('Alasan penolakan bukti pembayaran:');

    if (!notes) return;

    router.post(route('admin.payments.reject', payment.id), { notes }, { preserveScroll: true });
};
</script>

<template>
    <Head title="Verifikasi Pembayaran" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Verifikasi Pembayaran</h1>
                    <p class="mt-1 text-sm text-gray-500">Review bukti pembayaran pendaftaran mahasiswa baru.</p>
                </div>

                <div class="mb-4 bg-white p-4 shadow-sm sm:rounded-lg">
                    <form class="grid gap-3 md:grid-cols-[1fr_180px_auto_auto]" @submit.prevent="submitFilters">
                        <input v-model="form.search" type="search" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari nama, no registrasi, bank" />
                        <select v-model="form.status" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua status</option>
                            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
                        </select>
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Filter</button>
                        <button type="button" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700" @click="resetFilters">Reset</button>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Peserta</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Transfer</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Nominal</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Bukti</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="payment in payments.data" :key="payment.id">
                                    <td class="px-5 py-4 text-sm">
                                        <div class="font-semibold text-gray-900">{{ payment.registration?.user?.name || '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ payment.registration?.registration_number || '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ payment.registration?.program?.name || '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">
                                        <div>{{ payment.bank_name }}</div>
                                        <div class="text-xs text-gray-500">{{ payment.account_name }}</div>
                                        <div class="text-xs text-gray-500">{{ payment.transfer_date }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-gray-900">
                                        Rp {{ Number(payment.transfer_amount || 0).toLocaleString('id-ID') }}
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="statusClass(payment.status)">
                                            {{ payment.status }}
                                        </span>
                                        <p v-if="payment.notes" class="mt-2 max-w-xs text-xs text-red-600">{{ payment.notes }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm">
                                        <a
                                            v-if="payment.proof_document"
                                            :href="route('documents.view', payment.proof_document.id)"
                                            target="_blank"
                                            class="font-medium text-blue-600 hover:text-blue-900"
                                        >
                                            Lihat Bukti
                                        </a>
                                    </td>
                                    <td class="px-5 py-4 text-right text-sm">
                                        <div class="flex justify-end gap-3">
                                            <Link :href="route('registrations.show', payment.registration_id)" class="font-medium text-indigo-600 hover:text-indigo-900">
                                                Detail
                                            </Link>
                                            <button v-if="payment.status === 'pending'" type="button" class="font-medium text-green-600 hover:text-green-900" @click="verifyPayment(payment)">
                                                Verifikasi
                                            </button>
                                            <button v-if="payment.status === 'pending'" type="button" class="font-medium text-red-600 hover:text-red-900" @click="rejectPayment(payment)">
                                                Tolak
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="payments.data.length === 0">
                                    <td colspan="6" class="px-5 py-8 text-center text-sm text-gray-500">Belum ada bukti pembayaran.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link
                            v-for="link in payments.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            class="rounded-md px-3 py-2 text-sm"
                            :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
