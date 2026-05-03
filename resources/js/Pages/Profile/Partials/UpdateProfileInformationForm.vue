<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const countryCodes = ['+62'];
const normalizePhoneNumber = (value) => (value || '').toString().replace(/\D/g, '').replace(/^0+/, '');
const initialPhone = (user.phone || '').toString();
const initialPhoneNumber = initialPhone.startsWith('+62')
    ? initialPhone.slice(3)
    : normalizePhoneNumber(initialPhone);

const form = useForm({
    name: user.name,
    email: user.email,
    phone_country_code: '+62',
    phone_number: initialPhoneNumber,
});

const submit = () => {
    form
        .transform((data) => ({
            email: data.email,
            phone: data.phone_number
                ? `${data.phone_country_code}${normalizePhoneNumber(data.phone_number)}`
                : null,
        }))
        .patch(route('profile.update'));
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-semibold text-gray-900">
                Informasi Profile
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Perbarui alamat email dan nomor handphone akun Anda.
            </p>
        </header>

        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Nama" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full bg-gray-100 text-gray-500"
                    v-model="form.name"
                    disabled
                    autocomplete="name"
                />

                <p class="mt-2 text-xs text-gray-500">Username tidak dapat diedit dari halaman profil.</p>
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="phone_number" value="Nomor Handphone" />

                <div class="mt-1 flex rounded-md shadow-sm">
                    <select
                        id="phone_country_code"
                        v-model="form.phone_country_code"
                        class="rounded-l-md border-gray-300 bg-gray-50 text-sm font-semibold text-gray-700 focus:border-blue-500 focus:ring-blue-500"
                        aria-label="Kode negara"
                    >
                        <option v-for="code in countryCodes" :key="code" :value="code">{{ code }}</option>
                    </select>

                    <TextInput
                        id="phone_number"
                        type="tel"
                        class="-ml-px block w-full rounded-l-none"
                        v-model="form.phone_number"
                        placeholder="81234567890"
                        autocomplete="tel-national"
                        inputmode="numeric"
                    />
                </div>

                <p class="mt-2 text-xs text-gray-500">Masukkan nomor tanpa angka 0 di depan. Contoh: 81234567890.</p>
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Email Anda belum diverifikasi.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Kirim ulang email verifikasi.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    Link verifikasi baru sudah dikirim ke email Anda.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Simpan</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Tersimpan.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
