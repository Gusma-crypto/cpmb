<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const visible = ref(false);
const alert = ref(null);

const flashAlert = computed(() => {
    const flash = page.props.flash || {};
    const statusMessages = {
        'verification-link-sent': 'Link verifikasi baru sudah dikirim ke alamat email Anda.',
    };

    if (flash.success || flash.status) {
        return {
            type: 'success',
            title: 'Berhasil',
            message: flash.success || flash.status,
        };
    }

    if (page.props.status) {
        return {
            type: 'success',
            title: 'Berhasil',
            message: statusMessages[page.props.status] || page.props.status,
        };
    }

    if (flash.info) {
        return {
            type: 'info',
            title: 'Informasi',
            message: flash.info,
        };
    }

    if (flash.warning) {
        return {
            type: 'warning',
            title: 'Perhatian',
            message: flash.warning,
        };
    }

    if (flash.error) {
        return {
            type: 'error',
            title: 'Gagal',
            message: flash.error,
        };
    }

    const errors = page.props.errors || {};
    const firstError = Object.values(errors)[0];

    if (firstError) {
        return {
            type: 'error',
            title: 'Validasi Gagal',
            message: Array.isArray(firstError) ? firstError[0] : firstError,
        };
    }

    return null;
});

const close = () => {
    visible.value = false;
};

watch(
    flashAlert,
    (value) => {
        if (!value) {
            return;
        }

        alert.value = value;
        visible.value = true;
    },
    { immediate: true },
);
</script>

<template>
    <Teleport to="body">
        <Transition name="sweet-fade">
            <div v-if="visible && alert" class="sweet-backdrop" @click.self="close">
                <div class="sweet-card" role="alertdialog" aria-modal="true">
                    <div class="sweet-icon" :class="alert.type">
                        <svg v-if="alert.type === 'success'" viewBox="0 0 24 24" fill="none">
                            <path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg v-else-if="alert.type === 'warning'" viewBox="0 0 24 24" fill="none">
                            <path d="M12 8v5m0 4h.01M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg v-else-if="alert.type === 'info'" viewBox="0 0 24 24" fill="none">
                            <path d="M12 17v-6m0-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" />
                        </svg>
                        <svg v-else viewBox="0 0 24 24" fill="none">
                            <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                        </svg>
                    </div>

                    <h2>{{ alert.title }}</h2>
                    <p>{{ alert.message }}</p>

                    <button type="button" class="sweet-button" @click="close">
                        OK
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.sweet-backdrop {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: grid;
    place-items: center;
    padding: 20px;
    background: rgba(33, 37, 41, .48);
}

.sweet-card {
    width: min(100%, 390px);
    border-radius: 8px;
    background: #fff;
    padding: 30px 28px 26px;
    text-align: center;
    box-shadow: 0 24px 70px rgba(31, 45, 61, .28);
}

.sweet-icon {
    width: 74px;
    height: 74px;
    display: grid;
    place-items: center;
    margin: 0 auto 18px;
    border-radius: 50%;
}

.sweet-icon svg {
    width: 42px;
    height: 42px;
}

.sweet-icon.success { color: #31ce36; background: #e9f9ee; }
.sweet-icon.info { color: #1572e8; background: #eaf3ff; }
.sweet-icon.warning { color: #ff9e27; background: #fff4e4; }
.sweet-icon.error { color: #f25961; background: #feecef; }

.sweet-card h2 {
    margin: 0;
    color: #2f3441;
    font-size: 24px;
    font-weight: 800;
}

.sweet-card p {
    margin: 12px 0 0;
    color: #6b7280;
    font-size: 15px;
    line-height: 1.55;
}

.sweet-button {
    min-width: 94px;
    height: 42px;
    margin-top: 24px;
    border: 0;
    border-radius: 6px;
    background: #1572e8;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}

.sweet-button:hover {
    transform: translateY(-1px);
    background: #1158b4;
    box-shadow: 0 10px 22px rgba(21, 114, 232, .25);
}

.sweet-fade-enter-active,
.sweet-fade-leave-active {
    transition: opacity .2s ease;
}

.sweet-fade-enter-from,
.sweet-fade-leave-to {
    opacity: 0;
}

.sweet-fade-enter-active .sweet-card {
    animation: sweetPop .24s ease both;
}

@keyframes sweetPop {
    from {
        opacity: 0;
        transform: translateY(16px) scale(.94);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
