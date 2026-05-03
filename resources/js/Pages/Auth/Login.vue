<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Login" />

        <div class="auth-head">
            <p>Selamat datang kembali</p>
            <h2>Login Akun PMB</h2>
        </div>

        <form class="auth-form" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full auth-input"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full auth-input"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="auth-options">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                </label>
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="auth-link"
                >
                    Lupa password?
                </Link>
            </div>

            <div class="auth-actions">
                <PrimaryButton
                    class="auth-button"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Login
                </PrimaryButton>

                <p class="auth-foot">
                    Belum punya akun?
                    <Link :href="route('register')">Daftar sekarang</Link>
                </p>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.auth-head {
    margin-bottom: 26px;
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

.auth-form {
    display: grid;
    gap: 18px;
}

.auth-input {
    min-height: 46px;
}

.auth-options,
.auth-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
}

.auth-link,
.auth-foot a {
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

.auth-foot {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

@media (max-width: 520px) {
    .auth-options,
    .auth-actions {
        align-items: stretch;
        flex-direction: column;
    }
}
</style>
