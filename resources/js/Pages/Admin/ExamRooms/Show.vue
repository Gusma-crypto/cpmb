<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; import { Head, Link } from '@inertiajs/vue3';
const props = defineProps({ room: Object, routePrefix: { type: String, default: 'admin' } });
const r = (name, params) => route(`${props.routePrefix}.exam-rooms.${name}`, params);
</script>
<template><Head title="Detail Ruang Ujian"/><AdminLayout><div class="py-8"><div class="mx-auto max-w-5xl px-4"><div class="mb-6 flex justify-between"><h1 class="text-2xl font-semibold">Detail Ruang Ujian</h1><Link :href="r('index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm">Kembali</Link></div>
<div class="grid gap-6 lg:grid-cols-3"><div class="bg-white p-6 shadow-sm sm:rounded-lg"><h2 class="text-lg font-semibold">{{ room.name }}</h2><dl class="mt-4 space-y-2 text-sm"><div><dt class="font-medium text-gray-500">Kode</dt><dd>{{ room.code }}</dd></div><div><dt class="font-medium text-gray-500">Lokasi</dt><dd>{{ room.location || '-' }}</dd></div><div><dt class="font-medium text-gray-500">Kapasitas</dt><dd>{{ room.capacity }}</dd></div><div><dt class="font-medium text-gray-500">Status</dt><dd>{{ room.status }}</dd></div></dl></div>
<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-2"><div class="border-b px-6 py-4 font-semibold">Assignment</div><table class="min-w-full divide-y"><tbody class="divide-y"><tr v-for="a in room.assignments" :key="a.id"><td class="px-6 py-4 text-sm">{{ a.schedule?.title || '-' }}</td><td class="px-6 py-4 text-sm">{{ a.supervisor?.name || '-' }}</td><td class="px-6 py-4 text-sm">{{ a.status }}</td></tr><tr v-if="room.assignments.length===0"><td class="px-6 py-8 text-center text-sm text-gray-500">Belum ada assignment.</td></tr></tbody></table></div></div>
</div></div></AdminLayout></template>
