<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'; import { Head, Link, useForm } from '@inertiajs/vue3';
const props = defineProps({ room: Object, routePrefix: { type: String, default: 'admin' } });
const r = (name, params) => route(`${props.routePrefix}.exam-rooms.${name}`, params);
const form = useForm({ name: props.room.name, code: props.room.code, location: props.room.location || '', capacity: props.room.capacity, status: props.room.status });
const submit = () => form.put(r('update', props.room.id));
</script>
<template><Head title="Edit Ruang Ujian" /><AdminLayout><div class="py-8"><div class="mx-auto max-w-3xl px-4"><div class="mb-6 flex justify-between"><h1 class="text-2xl font-semibold">Edit Ruang Ujian</h1><Link :href="r('index')" class="text-sm text-blue-600">Kembali</Link></div><form class="space-y-6 bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
<div><label class="mb-1 block text-sm font-medium">Nama</label><input v-model="form.name" class="w-full rounded-md border-gray-300 text-sm"/><p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p></div>
<div><label class="mb-1 block text-sm font-medium">Kode</label><input v-model="form.code" class="w-full rounded-md border-gray-300 text-sm"/><p v-if="form.errors.code" class="text-sm text-red-600">{{ form.errors.code }}</p></div>
<div><label class="mb-1 block text-sm font-medium">Lokasi</label><input v-model="form.location" class="w-full rounded-md border-gray-300 text-sm"/></div>
<div><label class="mb-1 block text-sm font-medium">Kapasitas</label><input v-model="form.capacity" type="number" min="1" class="w-full rounded-md border-gray-300 text-sm"/><p v-if="form.errors.capacity" class="text-sm text-red-600">{{ form.errors.capacity }}</p></div>
<div><label class="mb-1 block text-sm font-medium">Status</label><select v-model="form.status" class="w-full rounded-md border-gray-300 text-sm"><option value="active">active</option><option value="inactive">inactive</option></select></div>
<div class="flex justify-end gap-3 border-t pt-6"><Link :href="r('index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm">Batal</Link><button class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white">Simpan</button></div>
</form></div></div></AdminLayout></template>
