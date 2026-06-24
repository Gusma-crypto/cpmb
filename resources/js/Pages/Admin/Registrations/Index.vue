<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';

const props = defineProps({
    registrations: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
        }),
    },
    statusOptions: {
        type: Array,
        default: () => [],
    },
    canExport: {
        type: Boolean,
        default: false,
    },
    routePrefix: {
        type: String,
        required: true,
    },
});

const form = reactive({
    search: props.filters.search || '',
    status: props.filters.status || '',
});

const r = (name, params = {}) => route(`${props.routePrefix}.registrations.${name}`, params);

const activeFilters = computed(() => Object.fromEntries(
    Object.entries(form).filter(([, value]) => value !== null && value !== undefined && value !== ''),
));

const submitFilters = () => {
    router.get(r('index'), activeFilters.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = () => {
    router.get(r('index'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const exportHref = (type) => r(`export.${type}`, activeFilters.value);

const statusLabel = (value) => ({
    draft: 'Draft',
    submitted: 'Submitted',
    under_review: 'Under Review',
    revision_required: 'Revision Required',
    verified: 'Verified',
    rejected: 'Rejected',
    exam_ready: 'Exam Ready',
}[value] || value);

const badgeClass = (value) => {
    const map = {
        draft: 'bg-gray-100 text-gray-700',
        submitted: 'bg-blue-100 text-blue-700',
        under_review: 'bg-indigo-100 text-indigo-700',
        revision_required: 'bg-amber-100 text-amber-700',
        verified: 'bg-emerald-100 text-emerald-700',
        rejected: 'bg-red-100 text-red-700',
        exam_ready: 'bg-green-100 text-green-700',
    };

    return map[value || 'draft'] || map.draft;
};
</script>

<template>
    <Head title="Pendaftaran PMB" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Pendaftaran PMB</h1>
                        <p class="mt-1 text-sm text-gray-500">Kelola data pendaftaran mahasiswa baru.</p>
                    </div>
                </div>

                <div class="mb-4 overflow-x-auto bg-white p-3 shadow-sm sm:rounded-lg">
                    <form class="flex min-w-max items-center gap-2" @submit.prevent="submitFilters">
                        <div class="w-72">
                            <label for="search" class="sr-only">Pencarian</label>
                            <input
                                id="search"
                                v-model="form.search"
                                type="search"
                                class="block h-10 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Cari no, nama, email, HP, prodi"
                            />
                        </div>

                        <div class="w-36">
                            <label for="status" class="sr-only">Status</label>
                            <select id="status" v-model="form.status" class="block h-10 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Status</option>
                                <option v-for="status in statusOptions" :key="status" :value="status">
                                    {{ statusLabel(status) }}
                                </option>
                            </select>
                        </div>

                        <div class="flex shrink-0 gap-2">
                            <button type="submit" class="h-10 rounded-md bg-blue-600 px-4 text-sm font-semibold text-white transition hover:bg-blue-700">
                                Filter
                            </button>
                            <button type="button" class="h-10 rounded-md bg-gray-100 px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-200" @click="resetFilters">
                                Reset
                            </button>
                        </div>

                        <div v-if="canExport" class="flex shrink-0 gap-2">
                            <a
                                :href="exportHref('pdf')"
                                class="inline-flex h-10 items-center rounded-md bg-red-600 px-3 text-sm font-semibold text-white transition hover:bg-red-700"
                            >
                                PDF
                            </a>
                            <a
                                :href="exportHref('excel')"
                                class="inline-flex h-10 items-center rounded-md bg-emerald-600 px-3 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            >
                                Excel
                            </a>
                        </div>
                    </form>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">No. Registrasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Program Studi</th>
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
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        <div class="font-medium text-gray-900">{{ registration.user?.name || '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ registration.user?.email || '-' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ registration.program?.name || '-' }}
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
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Tidak ada data pendaftaran yang sesuai filter.
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
    </AdminLayout>
</template>
