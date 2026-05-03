<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
const props = defineProps({ rooms: Object, filters: Object, routePrefix: { type: String, default: 'admin' } });
const search = ref(props.filters?.search || '');
const r = (name, params) => route(`${props.routePrefix}.exam-rooms.${name}`, params);
const find = () => router.get(r('index'), { search: search.value }, { preserveState: true, replace: true });
const roomLocked = (room) => Number(room.assignments_count || 0) > 0;
const destroy = (room) => {
    if (roomLocked(room)) return;
    if (confirm(`Hapus ${room.name}?`)) router.delete(r('destroy', room.id));
};
</script>
<template>
<Head title="Ruang Ujian" />
<AdminLayout><div class="py-8"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
<div class="mb-6 flex items-center justify-between"><h1 class="text-2xl font-semibold text-gray-900">Ruang Ujian</h1><Link :href="r('create')" class="rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase text-white">Tambah Ruang</Link></div>
<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
<form class="border-b border-gray-200 p-4" @submit.prevent="find"><div class="flex gap-3"><input v-model="search" class="w-full rounded-md border-gray-300 text-sm" placeholder="Cari ruangan" /><button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Cari</button></div></form>
<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Kode</th><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Nama</th><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Lokasi</th><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Kapasitas</th><th class="px-6 py-3 text-left text-xs uppercase text-gray-500">Status</th><th class="px-6 py-3 text-right text-xs uppercase text-gray-500">Aksi</th></tr></thead>
<tbody class="divide-y divide-gray-200 bg-white"><tr v-for="room in rooms.data" :key="room.id"><td class="px-6 py-4 font-mono text-sm">{{ room.code }}</td><td class="px-6 py-4 text-sm">{{ room.name }}</td><td class="px-6 py-4 text-sm">{{ room.location || '-' }}</td><td class="px-6 py-4 text-sm">{{ room.capacity }}</td><td class="px-6 py-4 text-sm">{{ room.status }}</td><td class="px-6 py-4 text-right text-sm"><Link :href="r('show', room.id)" class="text-indigo-600">Detail</Link><Link :href="r('edit', room.id)" class="ms-4 text-blue-600">Edit</Link><button class="ms-4" :class="roomLocked(room) ? 'cursor-not-allowed text-gray-400' : 'text-red-600'" :title="roomLocked(room) ? 'Ruang masih dipakai penempatan peserta' : 'Hapus ruang'" @click="destroy(room)" type="button">Hapus</button></td></tr><tr v-if="rooms.data.length === 0"><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada ruang ujian.</td></tr></tbody></table></div>
<div class="flex flex-wrap gap-2 border-t px-6 py-4"><Link v-for="link in rooms.links" :key="link.label" :href="link.url || '#'" class="rounded-md px-3 py-2 text-sm" :class="[link.active ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700', !link.url ? 'pointer-events-none opacity-50' : '']" v-html="link.label" /></div>
</div></div></div></AdminLayout>
</template>
