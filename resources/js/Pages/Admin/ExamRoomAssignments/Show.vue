<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; import { Head, Link } from '@inertiajs/vue3';
const props = defineProps({ assignment: Object, routePrefix: { type: String, default: 'admin' } });
const r = (name, params) => route(`${props.routePrefix}.exam-room-assignments.${name}`, params);
</script>
<template><Head title="Detail Penempatan"/><AdminLayout><div class="py-8"><div class="mx-auto max-w-5xl px-4"><div class="mb-6 flex justify-between"><h1 class="text-2xl font-semibold">Detail Penempatan</h1><Link :href="r('index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm">Kembali</Link></div>
<div class="bg-white p-6 shadow-sm sm:rounded-lg"><h2 class="text-lg font-semibold">{{ assignment.schedule?.title }}</h2><p class="mt-1 text-sm text-gray-500">{{ assignment.room?.name }} · {{ assignment.supervisor?.name || 'Tanpa pengawas' }}</p><div class="mt-6 overflow-x-auto"><table class="min-w-full divide-y"><tbody class="divide-y"><tr v-for="p in assignment.participants" :key="p.id"><td class="px-4 py-3 text-sm font-mono">{{ p.participant_number }}</td><td class="px-4 py-3 text-sm">{{ p.registration?.registration_number }}</td><td class="px-4 py-3 text-sm">{{ p.registration?.user?.name }}</td><td class="px-4 py-3 text-sm">{{ p.status }}</td></tr><tr v-if="assignment.participants.length===0"><td class="px-4 py-8 text-center text-sm text-gray-500">Belum ada peserta.</td></tr></tbody></table></div></div>
</div></div></AdminLayout></template>
