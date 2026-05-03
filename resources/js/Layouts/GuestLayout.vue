<script setup>
import SweetAlert from '@/Components/SweetAlert.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="auth-shell">
        <section class="brand-panel">
            <Link href="/" class="campus-brand" aria-label="Beranda">
                <span class="campus-logo" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none">
                        <path d="M24 5 5 15l19 10 19-10L24 5Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
                        <path d="M12 20v10c0 5 5.4 9 12 9s12-4 12-9V20" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M43 15v15" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                </span>
                <span>
                    <strong>PMB Kampus</strong>
                    <small>Sistem Pendaftaran Mahasiswa Baru</small>
                </span>
            </Link>

            <div class="brand-copy">
                <p class="eyebrow">Atlantis Admin</p>
                <h1>Portal Pendaftaran Mahasiswa Baru</h1>
                <p>
                    Kelola proses pendaftaran, biodata, dokumen, dan verifikasi calon mahasiswa dalam satu dashboard.
                </p>
            </div>

            <div class="brand-stats" aria-label="Ringkasan layanan">
                <div>
                    <strong>Online</strong>
                    <span>Pendaftaran</span>
                </div>
                <div>
                    <strong>24/7</strong>
                    <span>Akses Sistem</span>
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="auth-card">
                <slot />
            </div>
        </section>

        <SweetAlert />
    </div>
</template>

<style scoped>
:global(body) {
    margin: 0;
    background: #f4f7fb;
    color: #2a2f3a;
    font-family: "Public Sans", Arial, sans-serif;
    letter-spacing: 0;
}

* {
    box-sizing: border-box;
}

.auth-shell {
    min-height: 100vh;
    display: grid;
    grid-template-columns: minmax(360px, .95fr) minmax(420px, 1.05fr);
    background: #f4f7fb;
}

.brand-panel {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    padding: 48px;
    background:
        linear-gradient(135deg, rgba(21, 114, 232, .96), rgba(17, 88, 180, .98)),
        #1572e8;
    color: #fff;
}

.brand-panel::before,
.brand-panel::after {
    content: "";
    position: absolute;
    border-radius: 999px;
    background: rgba(255, 255, 255, .12);
}

.brand-panel::before {
    width: 320px;
    height: 320px;
    right: -120px;
    top: -80px;
}

.brand-panel::after {
    width: 220px;
    height: 220px;
    left: -90px;
    bottom: 80px;
}

.campus-brand,
.brand-copy,
.brand-stats {
    position: relative;
    z-index: 1;
}

.campus-brand {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    color: inherit;
    text-decoration: none;
    animation: fadeRise .5s ease both;
}

.campus-logo {
    width: 54px;
    height: 54px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: rgba(255, 255, 255, .15);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .24);
}

.campus-logo svg {
    width: 34px;
    height: 34px;
}

.campus-brand strong,
.campus-brand small {
    display: block;
}

.campus-brand strong {
    font-size: 22px;
    line-height: 1.15;
}

.campus-brand small {
    margin-top: 4px;
    color: rgba(255, 255, 255, .76);
    font-size: 13px;
}

.brand-copy {
    max-width: 520px;
    animation: fadeRise .55s ease .08s both;
}

.eyebrow {
    margin: 0 0 14px;
    color: rgba(255, 255, 255, .72);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.brand-copy h1 {
    margin: 0;
    font-size: clamp(34px, 4vw, 54px);
    line-height: 1.04;
    font-weight: 800;
}

.brand-copy p:last-child {
    max-width: 460px;
    margin: 22px 0 0;
    color: rgba(255, 255, 255, .78);
    font-size: 16px;
    line-height: 1.7;
}

.brand-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 150px));
    gap: 14px;
    animation: fadeRise .55s ease .16s both;
}

.brand-stats div {
    min-height: 82px;
    display: grid;
    align-content: center;
    gap: 6px;
    border-radius: 8px;
    padding: 16px;
    background: rgba(255, 255, 255, .12);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .16);
}

.brand-stats strong {
    font-size: 20px;
}

.brand-stats span {
    color: rgba(255, 255, 255, .72);
    font-size: 13px;
}

.form-panel {
    min-width: 0;
    display: grid;
    place-items: center;
    padding: 42px;
}

.auth-card {
    width: min(100%, 470px);
    border-radius: 8px;
    background: #fff;
    padding: 34px;
    box-shadow: 0 16px 40px rgba(31, 45, 61, .12);
    border: 1px solid rgba(235, 236, 236, .9);
    animation: cardIn .45s ease both;
}

@keyframes fadeRise {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateX(18px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@media (max-width: 900px) {
    .auth-shell {
        grid-template-columns: 1fr;
    }

    .brand-panel {
        min-height: 360px;
        padding: 32px 24px;
        gap: 38px;
    }

    .form-panel {
        padding: 28px 18px 42px;
    }
}

@media (max-width: 560px) {
    .brand-copy h1 {
        font-size: 32px;
    }

    .brand-stats {
        grid-template-columns: 1fr;
    }

    .auth-card {
        padding: 24px;
    }
}
</style>
