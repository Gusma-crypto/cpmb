<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const phoneCode = ref('+62');
const phoneNumber = ref('');

const submit = () => {
    const sanitizedPhone = phoneNumber.value
        .replace(/\D+/g, '')
        .replace(/^62/, '')
        .replace(/^0+/, '');

    form.phone = `${phoneCode.value}${sanitizedPhone}`;

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <nav class="register-nav" aria-label="Informasi PMB">
            <Link href="/" class="register-nav-link">Beranda</Link>

            <details class="about-menu">
                <summary>Tentang Kami</summary>
                <div class="about-dropdown">
                    <a href="#informasi">Informasi</a>
                    <a href="#fakultas">Fakultas</a>
                    <a href="#jenis-kelas">Jenis Kelas</a>
                    <a href="#biaya-pendidikan">Biaya Pendidikan</a>
                </div>
            </details>
        </nav>

        <div class="auth-head">
            <p>Pendaftaran akun</p>
            <h2>Buat Akun PMB</h2>
            <span class="home-info">Portal resmi pendaftaran mahasiswa baru. Lengkapi akun untuk melanjutkan proses PMB online.</span>
        </div>

        <form class="auth-form" @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nama Lengkap" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full auth-input"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full auth-input"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="phone" value="No HP/WA" />

                <div class="phone-field">
                    <select v-model="phoneCode" class="phone-code" aria-label="Kode negara">
                        <option value="+62">🇮🇩 +62</option>
                    </select>

                    <TextInput
                        id="phone"
                        type="tel"
                        class="block w-full auth-input phone-input"
                        v-model="phoneNumber"
                        required
                        autocomplete="tel"
                        inputmode="numeric"
                        placeholder="Masukkan nomor HP"
                    />
                </div>

                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full auth-input"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full auth-input"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="auth-actions">
                <Link
                    :href="route('login')"
                    class="auth-link"
                >
                    Sudah punya akun?
                </Link>

                <PrimaryButton
                    class="auth-button"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Daftar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.auth-head {
    margin-bottom: 24px;
}

.register-nav {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 18px;
    margin-bottom: 24px;
    color: #4b5563;
    font-size: 14px;
    font-weight: 700;
}

.register-nav-link {
    color: inherit;
    text-decoration: none;
}

.register-nav-link:hover,
.about-menu summary:hover {
    color: #1572e8;
}

.about-menu {
    position: relative;
}

.about-menu summary {
    list-style: none;
    cursor: pointer;
    color: #1572e8;
}

.about-menu summary::-webkit-details-marker {
    display: none;
}

.about-menu summary::after {
    content: "⌄";
    margin-left: 7px;
    font-size: 13px;
}

.about-menu[open] summary::after {
    content: "⌃";
}

.about-dropdown {
    position: absolute;
    top: 30px;
    right: 0;
    z-index: 20;
    min-width: 170px;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    padding: 8px;
    background: #fff;
    box-shadow: 0 16px 32px rgba(31, 45, 61, .16);
}

.about-dropdown a {
    display: block;
    border-radius: 5px;
    padding: 9px 10px;
    color: #4b5563;
    font-size: 13px;
    text-decoration: none;
}

.about-dropdown a:hover {
    background: #f1f6ff;
    color: #1572e8;
}

.auth-head p {
    margin: 0 0 8px;
    color: #1572e8;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
}

.auth-head h2 {
    margin: 0;
    color: #2f3441;
    font-size: 28px;
    line-height: 1.15;
    font-weight: 800;
}

.home-info {
    display: block;
    margin-top: 10px;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.55;
}

.auth-form {
    display: grid;
    gap: 16px;
}

.auth-input {
    min-height: 46px;
}

.phone-field {
    min-height: 46px;
    display: flex;
    align-items: stretch;
    margin-top: 4px;
}

.phone-code {
    width: 104px;
    border: 1px solid #d1d5db;
    border-right: 0;
    border-radius: 6px 0 0 6px;
    background: #fff;
    color: #374151;
    font-size: 14px;
    font-weight: 700;
}

.phone-input {
    border-radius: 0 6px 6px 0;
}

.auth-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding-top: 4px;
}

.auth-link {
    color: #1572e8;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
}

.auth-button {
    min-height: 46px;
    justify-content: center;
    border-radius: 6px;
    background: #1572e8;
    padding-inline: 26px;
}

@media (max-width: 520px) {
    .register-nav {
        justify-content: flex-start;
    }

    .auth-actions {
        align-items: stretch;
        flex-direction: column-reverse;
    }
}
</style>
