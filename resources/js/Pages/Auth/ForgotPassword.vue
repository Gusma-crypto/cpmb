<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="auth-head">
            <p>Pemulihan akses</p>
            <h2>Lupa Password</h2>
        </div>

        <div class="help-text">
            Masukkan email akun Anda. Sistem akan mengirim tautan reset password untuk membuat password baru.
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

            <div class="auth-actions">
                <Link :href="route('login')" class="auth-link">Kembali ke login</Link>
                <PrimaryButton
                    class="auth-button"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Kirim Link Reset
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

<style scoped>
.auth-head {
    margin-bottom: 18px;
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

.help-text {
    margin-bottom: 22px;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.65;
}

.auth-form {
    display: grid;
    gap: 18px;
}

.auth-input {
    min-height: 46px;
}

.auth-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
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
    .auth-actions {
        align-items: stretch;
        flex-direction: column-reverse;
    }
}
</style>
