<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    results: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
});

const search = ref(props.filters.search || '');
const published = ref(props.filters.published || '');
const selected = ref([]);

const submitSearch = () => {
    router.get(route('admin.cbt.results.index'), {
        search: search.value,
        published: published.value,
    }, { preserveState: true, replace: true });
};

const allVisibleIds = computed(() => props.results.data.map((result) => result.id));
const toggleAll = () => {
    selected.value = selected.value.length === allVisibleIds.value.length ? [] : [...allVisibleIds.value];
};
const publish = (result) => {
    if (confirm(`Publish hasil ${result.user?.name || 'peserta'}?`)) {
        router.post(route('admin.cbt.results.publish', result.id), {}, { preserveScroll: true });
    }
};
const unpublish = (result) => {
    if (confirm(`Batalkan publish hasil ${result.user?.name || 'peserta'}?`)) {
        router.post(route('admin.cbt.results.unpublish', result.id), {}, { preserveScroll: true });
    }
};
const publishMany = () => {
    if (selected.value.length === 0) return;
    if (confirm(`Publish ${selected.value.length} hasil CBT?`)) {
        router.post(route('admin.cbt.results.publish-many'), { ids: selected.value }, {
            preserveScroll: true,
            onSuccess: () => selected.value = [],
        });
    }
};
const scoreClass = (result) => result.is_passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
</script>

<template>
    <Head title="Hasil CBT" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Hasil CBT</h1>
                        <p class="mt-1 text-sm text-gray-500">Publish hasil agar bisa dilihat oleh peserta.</p>
                    </div>
                    <button type="button" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:bg-gray-300" :disabled="selected.length === 0" @click="publishMany">
                        Publish Terpilih
                    </button>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form class="border-b border-gray-200 p-4" @submit.prevent="submitSearch">
                        <div class="grid gap-3 md:grid-cols-[1fr_180px_auto]">
                            <input v-model="search" type="search" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Cari nama, nomor daftar, paket CBT" />
                            <select v-model="published" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua status</option>
                                <option value="yes">Published</option>
                                <option value="no">Belum publish</option>
                            </select>
                            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Cari</button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3"><input type="checkbox" :checked="selected.length === allVisibleIds.length && allVisibleIds.length > 0" @change="toggleAll" /></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Peserta</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Paket</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Skor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Benar/Salah/Kosong</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Publish</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="result in results.data" :key="result.id">
                                    <td class="px-4 py-4"><input v-model="selected" type="checkbox" :value="result.id" /></td>
                                    <td class="px-4 py-4 text-sm">
                                        <div class="font-semibold text-gray-900">{{ result.user?.name || '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ result.registration?.registration_number || '-' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ result.cbt_exam?.title || '-' }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-semibold" :class="scoreClass(result)">
                                            {{ result.final_score }} / {{ result.pass_score }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700">{{ result.correct_answers }} / {{ result.wrong_answers }} / {{ result.unanswered }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <span class="rounded-full px-2 py-1 text-xs font-medium" :class="result.published_at ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'">
                                            {{ result.published_at ? 'Published' : 'Belum' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right text-sm">
                                        <button v-if="!result.published_at" type="button" class="font-medium text-blue-600 hover:text-blue-900" @click="publish(result)">Publish</button>
                                        <button v-else type="button" class="font-medium text-amber-700 hover:text-amber-900" @click="unpublish(result)">Unpublish</button>
                                    </td>
                                </tr>
                                <tr v-if="results.data.length === 0">
                                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Belum ada hasil CBT.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-gray-200 px-6 py-4">
                        <Link v-for="link in results.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
