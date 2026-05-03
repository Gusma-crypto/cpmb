<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    managedUser: { type: Object, required: true },
    roles: { type: Array, required: true },
});

const currentRole = props.managedUser.roles?.[0]?.name || props.managedUser.role || 'student';

const form = useForm({
    name: props.managedUser.name,
    email: props.managedUser.email,
    phone: props.managedUser.phone || '',
    password: '',
    role: currentRole,
    is_active: props.managedUser.is_active,
});

const submit = () => form.put(route('admin.users.update', props.managedUser.id));
</script>

<template>
    <Head title="Edit User" />

    <AdminLayout>
        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-gray-900">Edit User</h1>
                    <Link :href="route('admin.users.index')" class="text-sm font-medium text-blue-600 hover:text-blue-900">Kembali</Link>
                </div>

                <form class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Nama</label>
                        <input v-model="form.name" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Email</label>
                        <input v-model="form.email" type="email" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">No HP/WA</label>
                        <input v-model="form.phone" type="tel" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Contoh: 081234567890" />
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Password Baru</label>
                        <input v-model="form.password" type="password" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Role</label>
                        <select v-model="form.role" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>
                        <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
                        Aktif
                    </label>
                    <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
                        <Link :href="route('admin.users.index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Batal</Link>
                        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white" :disabled="form.processing">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
