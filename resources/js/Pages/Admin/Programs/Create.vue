<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    code: '',
    name: '',
    level: '',
    faculty: '',
    accreditation: '',
    description: '',
    is_active: true,
});

const submit = () => form.post(route('admin.programs.store'));
</script>

<template>
    <Head title="Tambah Program" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Tambah Program</h1>
                    <Link :href="route('admin.programs.index')" class="text-sm font-medium text-blue-600 hover:text-blue-900">Kembali</Link>
                </div>

                <form class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Kode</label>
                        <input v-model="form.code" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <p v-if="form.errors.code" class="mt-1 text-sm text-red-600">{{ form.errors.code }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nama</label>
                        <input v-model="form.name" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-6 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Jenjang</label>
                            <select v-model="form.level" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih jenjang</option>
                                <option value="D3">D3</option>
                                <option value="D4">D4</option>
                                <option value="S1">S1</option>
                                <option value="S2">S2</option>
                                <option value="S3">S3</option>
                            </select>
                            <p v-if="form.errors.level" class="mt-1 text-sm text-red-600">{{ form.errors.level }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Fakultas</label>
                            <input v-model="form.faculty" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: Fakultas Teknik" />
                            <p v-if="form.errors.faculty" class="mt-1 text-sm text-red-600">{{ form.errors.faculty }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Akreditasi</label>
                            <select v-model="form.accreditation" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih akreditasi</option>
                                <option value="Unggul">Unggul</option>
                                <option value="Baik Sekali">Baik Sekali</option>
                                <option value="Baik">Baik</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                            </select>
                            <p v-if="form.errors.accreditation" class="mt-1 text-sm text-red-600">{{ form.errors.accreditation }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea v-model="form.description" rows="4" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                        Aktif
                    </label>
                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
                        <Link :href="route('admin.programs.index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Batal</Link>
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white" :disabled="form.processing">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
