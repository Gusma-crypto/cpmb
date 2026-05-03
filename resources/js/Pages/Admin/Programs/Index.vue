<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    programs: { type: Object, required: true },
    filters: { type: Object, default: () => ({ search: '' }) },
});

const search = ref(props.filters.search || '');

const submitSearch = () => {
    router.get(route('admin.programs.index'), { search: search.value }, { preserveState: true, replace: true });
};

const destroy = (program) => {
    if (confirm(`Hapus program ${program.name}?`)) {
        router.delete(route('admin.programs.destroy', program.id));
    }
};
</script>

<template>
    <Head title="Programs" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Programs</h1>
                    <Link :href="route('admin.programs.create')" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700">
                        Tambah Program
                    </Link>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="border-b border-gray-200 p-4" @submit.prevent="submitSearch">
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input v-model="search" type="search" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari kode, nama, jenjang, fakultas, akreditasi" />
                            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Kode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Jenjang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Fakultas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Akreditasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="program in programs.data" :key="program.id">
                                    <td class="whitespace-nowrap px-6 py-4 font-mono text-sm text-gray-700">{{ program.code }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ program.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ program.level || '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ program.faculty || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ program.accreditation || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="program.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                            {{ program.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <Link :href="route('admin.programs.edit', program.id)" class="font-medium text-blue-600 hover:text-blue-900">Edit</Link>
                                        <button type="button" class="ms-4 font-medium text-red-600 hover:text-red-900" @click="destroy(program)">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="programs.data.length === 0">
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada program.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link v-for="link in programs.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
