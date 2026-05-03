<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    category: { type: Object, required: true },
});

const form = useForm({
    name: props.category.name,
    description: props.category.description || '',
    is_active: props.category.is_active,
});

const submit = () => form.put(route('admin.question-categories.update', props.category.id));
</script>

<template>
    <Head title="Edit Kategori Soal" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Kategori Soal</h1>
                    <Link :href="route('admin.question-categories.index')" class="text-sm font-medium text-blue-600 hover:text-blue-900">Kembali</Link>
                </div>

                <form class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nama Kategori</label>
                        <input v-model="form.name" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea v-model="form.description" rows="4" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                        Aktif
                    </label>
                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
                        <Link :href="route('admin.question-categories.index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Batal</Link>
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white" :disabled="form.processing">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
