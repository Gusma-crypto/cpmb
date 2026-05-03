<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    registration: Object,
    registrationWaves: {
        type: Array,
        default: () => [],
    },
    loginStats: {
        type: Object,
        default: () => ({
            login_count: 0,
            last_login_at: null,
            previous_login_at: null,
            last_login_ip: null,
        }),
    },
    cbtSummary: {
        type: Object,
        default: () => ({
            attempts: 0,
            published_results: 0,
            best_score: 0,
            passed: 0,
        }),
    },
});

const statusLabel = {
    draft: 'Draft',
    submitted: 'Menunggu Verifikasi',
    under_review: 'Sedang Direview',
    revision_required: 'Perlu Revisi',
    verified: 'Terverifikasi',
    rejected: 'Ditolak',
    exam_ready: 'Siap Ujian',
};

const statusClass = {
    draft: 'badge-gray',
    submitted: 'badge-yellow',
    under_review: 'badge-blue',
    revision_required: 'badge-yellow',
    verified: 'badge-blue',
    rejected: 'badge-red',
    exam_ready: 'badge-green',
};

const paymentLabel = {
    unpaid: 'Belum Bayar',
    pending: 'Menunggu Konfirmasi',
    paid: 'Lunas',
};

const paymentClass = {
    unpaid: 'badge-red',
    pending: 'badge-yellow',
    paid: 'badge-green',
};

const nextStepText = computed(() => {
    if (! props.registration) return 'Mulai pendaftaran Anda sekarang.';
    switch (props.registration.status) {
        case 'draft':     return 'Lengkapi biodata dan dokumen, lalu submit pendaftaran.';
        case 'submitted': return 'Pendaftaran Anda sudah dikirim dan menunggu review panitia.';
        case 'under_review': return 'Pendaftaran Anda sedang direview oleh panitia.';
        case 'revision_required': return props.registration.revision_notes || 'Pendaftaran perlu direvisi sesuai catatan panitia.';
        case 'verified':  return 'Silakan selesaikan pembayaran untuk melanjutkan proses.';
        case 'exam_ready':  return 'Pendaftaran dan pembayaran sudah valid. Silakan pantau jadwal ujian.';
        case 'rejected':  return 'Pendaftaran Anda ditolak. Hubungi panitia untuk informasi lebih lanjut.';
        default:          return '';
    }
});

const hasOpenRegistrationWave = computed(() => props.registrationWaves.length > 0);
const formatDate = (value) => value
    ? new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
    : '-';

const formatDateTime = (value) => value
    ? new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
    : '-';

const totalLogin = computed(() => Number(props.loginStats.login_count || 0));
const loginHistory = computed(() => [
    {
        title: 'Login Terakhir',
        datetime: props.loginStats.last_login_at,
        ip: props.loginStats.last_login_ip || '-',
    },
    {
        title: 'Login Sebelumnya',
        datetime: props.loginStats.previous_login_at,
        ip: '-',
    },
].filter((item) => item.datetime));

const chartBars = computed(() => {
    const lastLoginCount = props.loginStats.last_login_at ? 1 : 0;
    const previousLoginCount = props.loginStats.previous_login_at ? 1 : 0;
    const values = [
        { label: 'Total', value: totalLogin.value },
        { label: 'Terakhir', value: lastLoginCount },
        { label: 'Sebelumnya', value: previousLoginCount },
    ];
    const maxValue = Math.max(...values.map((item) => item.value), 1);

    return values.map((item) => ({
        ...item,
        height: Math.max((item.value / maxValue) * 100, item.value > 0 ? 14 : 0),
    }));
});
</script>

<template>
    <Head title="Dashboard" />

    <RoleLayout>
        <section class="page-head">
            <div>
                <h1>Dashboard</h1>
                <p>Sistem Penerimaan Mahasiswa Baru</p>
            </div>
        </section>

        <section class="page-content">
            <div v-if="! registration" class="card empty-state">
                <div class="empty-illustration" aria-hidden="true">
                    <svg viewBox="0 0 220 150" fill="none">
                        <rect x="30" y="28" width="160" height="100" rx="10" fill="#eef5ff" />
                        <rect x="48" y="46" width="124" height="64" rx="6" fill="#ffffff" stroke="#b7d4fb" stroke-width="3" />
                        <path d="M66 66h76M66 82h48M66 98h30" stroke="#1572e8" stroke-width="6" stroke-linecap="round" />
                        <circle cx="160" cy="42" r="22" fill="#1572e8" />
                        <path d="m150 42 7 7 14-16" stroke="#ffffff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M74 128h72" stroke="#b7d4fb" stroke-width="6" stroke-linecap="round" />
                    </svg>
                </div>
                <h2>Belum Ada Pendaftaran</h2>
                <p v-if="hasOpenRegistrationWave">Anda belum memiliki data pendaftaran. Pilih gelombang pendaftaran yang sedang dibuka.</p>
                <p v-else>Gelombang pendaftaran belum dibuka. Silakan pantau informasi PMB secara berkala.</p>

                <div v-if="registrationWaves.length === 1" class="empty-actions">
                    <Link :href="route('registrations.create', { wave: registrationWaves[0].id })" class="btn-primary">
                        Daftar Sekarang
                    </Link>
                    <span class="wave-note">{{ registrationWaves[0].label }} · {{ registrationWaves[0].academic_year?.label || '-' }}</span>
                </div>

                <div v-else-if="registrationWaves.length > 1" class="wave-options">
                    <Link
                        v-for="wave in registrationWaves"
                        :key="wave.id"
                        :href="route('registrations.create', { wave: wave.id })"
                        class="wave-option"
                    >
                        <strong>{{ wave.label }}</strong>
                        <span>{{ wave.academic_year?.label || '-' }}</span>
                        <small>{{ formatDate(wave.open_at) }} - {{ formatDate(wave.close_at) }}</small>
                    </Link>
                </div>
            </div>

            <div v-else class="content-grid">
                <div class="left-column">
                    <article class="card status-card">
                        <div class="card-body">
                            <h2 class="card-title">Status Pendaftaran</h2>
                            <p class="reg-number">{{ registration.registration_number }}</p>

                            <div class="status-row">
                                <div class="status-item">
                                    <span class="status-desc">Status</span>
                                    <span class="badge" :class="statusClass[registration.status]">
                                        {{ statusLabel[registration.status] ?? registration.status }}
                                    </span>
                                </div>
                                <div class="status-item">
                                    <span class="status-desc">Pembayaran</span>
                                    <span class="badge" :class="paymentClass[registration.payment_status]">
                                        {{ paymentLabel[registration.payment_status] ?? registration.payment_status }}
                                    </span>
                                </div>
                            </div>

                            <p class="next-step">{{ nextStepText }}</p>
                            <div v-if="registration.status === 'revision_required' && registration.revision_notes" class="revision-box">
                                <strong>Catatan revisi</strong>
                                <p>{{ registration.revision_notes }}</p>
                            </div>

                            <Link :href="route('registrations.show', registration.id)" class="btn-primary mt">
                                Lihat Detail Pendaftaran
                            </Link>
                        </div>
                    </article>

                    <article class="card activity-panel">
                        <h3 class="ql-title">Grafik & Statistik Login</h3>

                        <div class="login-chart">
                            <div class="chart-summary">
                                <span>Total Login</span>
                                <strong>{{ totalLogin }}</strong>
                            </div>

                            <div class="chart-bars" aria-label="Grafik aktivitas login">
                                <div v-for="bar in chartBars" :key="bar.label" class="chart-bar-item">
                                    <span class="bar-value">{{ bar.value }}</span>
                                    <div class="bar-track">
                                        <div class="bar-fill" :style="{ height: `${bar.height}%` }"></div>
                                    </div>
                                    <span class="bar-label">{{ bar.label }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="history-block">
                            <h3 class="ql-title">Log History</h3>

                            <div v-if="loginHistory.length" class="history-list">
                                <div v-for="item in loginHistory" :key="item.title" class="history-item">
                                    <span class="history-dot"></span>
                                    <div>
                                        <p class="history-title">{{ item.title }}</p>
                                        <p class="history-meta">{{ formatDateTime(item.datetime) }}</p>
                                        <p class="history-meta">IP: {{ item.ip }}</p>
                                    </div>
                                </div>
                            </div>

                            <p v-else class="history-empty">Belum ada riwayat login yang tersimpan.</p>
                        </div>
                    </article>

                    <article class="card activity-panel">
                        <h3 class="ql-title">Rekap CBT</h3>
                        <div class="status-row">
                            <div class="status-item">
                                <span class="status-desc">Attempt</span>
                                <strong>{{ cbtSummary?.attempts ?? 0 }}</strong>
                            </div>
                            <div class="status-item">
                                <span class="status-desc">Hasil Publish</span>
                                <strong>{{ cbtSummary?.published_results ?? 0 }}</strong>
                            </div>
                        </div>
                        <div class="status-row mt">
                            <div class="status-item">
                                <span class="status-desc">Nilai Terbaik</span>
                                <strong>{{ cbtSummary?.best_score ?? 0 }}</strong>
                            </div>
                            <div class="status-item">
                                <span class="status-desc">Lulus</span>
                                <strong>{{ cbtSummary?.passed ?? 0 }}</strong>
                            </div>
                        </div>
                        <Link :href="route('student.cbt.results.index')" class="btn-primary mt">
                            Lihat Hasil CBT
                        </Link>
                    </article>
                </div>

                <div class="quick-links">
                    <h3 class="ql-title">Aksi Cepat</h3>

                    <Link :href="route('student.registrations.index')" class="ql-item">
                        <div class="ql-icon blue">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                                <path d="M4 5h16v13H4V5Zm6 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <p class="ql-label">Pendaftaran</p>
                            <p class="ql-sub">Lihat alur pendaftaran</p>
                        </div>
                    </Link>

                    <Link :href="route('biodata.index')" class="ql-item">
                        <div class="ql-icon purple">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div>
                            <p class="ql-label">Biodata</p>
                            <p class="ql-sub">Kelola data pribadi</p>
                        </div>
                    </Link>

                    <Link :href="route('documents.index')" class="ql-item">
                        <div class="ql-icon orange">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                                <path d="M7 3h7l5 5v13H7V3Zm7 0v5h5M10 13h6M10 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <p class="ql-label">Dokumen</p>
                            <p class="ql-sub">Upload & kelola dokumen</p>
                        </div>
                    </Link>

                    <Link :href="route('registrations.show', registration.id)" class="ql-item">
                        <div class="ql-icon dark">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                                <path d="M5 12h14M13 6l6 6-6 6M5 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <p class="ql-label">Review & Submit</p>
                            <p class="ql-sub">Checklist akhir pendaftaran</p>
                        </div>
                    </Link>

                    <Link :href="route('profile.edit')" class="ql-item">
                        <div class="ql-icon green">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2 2 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <div>
                            <p class="ql-label">Profile</p>
                            <p class="ql-sub">Edit akun Anda</p>
                        </div>
                    </Link>
                </div>
            </div>
        </section>
    </RoleLayout>
</template>

<style scoped>
.page-head {
    min-height: 120px;
    display: flex;
    align-items: flex-start;
    padding: 36px 31px 50px;
    background: #1158b4;
    color: #fff;
}

.page-head h1 {
    margin: 0 0 8px;
    font-size: 24px;
    font-weight: 700;
}

.page-head p {
    margin: 0;
    color: rgba(255, 255, 255, .72);
    font-size: 14px;
    font-weight: 300;
}

.page-content {
    padding: 0 31px 60px;
    margin-top: -24px;
}

.card {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 7px 18px rgba(31, 45, 61, .08);
    border: 1px solid rgba(235, 236, 236, .8);
}

.empty-state {
    max-width: 560px;
    text-align: center;
    padding: 44px 32px;
    margin: 0 auto;
}

.empty-illustration {
    width: min(220px, 100%);
    margin: 0 auto 20px;
}

.empty-illustration svg {
    display: block;
    width: 100%;
    height: auto;
}

.empty-state h2 {
    margin: 0 0 10px;
    font-size: 20px;
    font-weight: 600;
    color: #44495a;
}

.empty-state p {
    margin: 0 0 24px;
    font-size: 14px;
    color: #8d9498;
    line-height: 1.5;
}

.empty-actions {
    display: grid;
    justify-items: center;
    gap: 10px;
}

.wave-note {
    font-size: 12px;
    color: #8d9498;
}

.wave-options {
    display: grid;
    gap: 10px;
    margin-top: 24px;
    text-align: left;
}

.wave-option {
    display: grid;
    gap: 3px;
    padding: 13px 14px;
    border-radius: 6px;
    border: 1px solid #d8e8ff;
    background: #f8fbff;
    color: #44495a;
    text-decoration: none;
    transition: border-color .15s, background .15s, transform .15s;
}

.wave-option:hover {
    border-color: #1572e8;
    background: #eef5ff;
    transform: translateY(-1px);
}

.wave-option strong {
    font-size: 14px;
    color: #1158b4;
}

.wave-option span,
.wave-option small {
    font-size: 12px;
    color: #8d9498;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 24px;
    align-items: start;
}

.left-column {
    display: grid;
    gap: 24px;
}

.card-body {
    padding: 24px;
}

.card-title {
    margin: 0 0 8px;
    font-size: 18px;
    font-weight: 600;
    color: #44495a;
}

.reg-number {
    margin: 0 0 20px;
    font-size: 13px;
    font-family: monospace;
    color: #8d9498;
}

.status-row {
    display: flex;
    gap: 32px;
    margin-bottom: 20px;
}

.status-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.status-desc {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #8d9498;
}

.next-step {
    margin: 0 0 20px;
    font-size: 14px;
    color: #575962;
    line-height: 1.5;
    padding: 12px 14px;
    background: #f5f7fb;
    border-radius: 5px;
    border-left: 3px solid #1572e8;
}

.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .02em;
}

.badge-gray   { background: #f0f0f0; color: #6b7280; }
.badge-yellow { background: #fff3cd; color: #856404; }
.badge-blue   { background: #dbeafe; color: #1d4ed8; }
.badge-green  { background: #d1fae5; color: #065f46; }
.badge-red    { background: #fee2e2; color: #991b1b; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 40px;
    padding: 0 20px;
    border-radius: 5px;
    background: #1572e8;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: background .15s;
}

.btn-primary:hover {
    background: #1158b4;
}

.btn-primary.mt {
    margin-top: 4px;
}

.quick-links {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 7px 18px rgba(31, 45, 61, .08);
    border: 1px solid rgba(235, 236, 236, .8);
    padding: 20px;
}

.ql-title {
    margin: 0 0 16px;
    font-size: 14px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #8d9498;
}

.ql-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 10px;
    border-radius: 6px;
    text-decoration: none;
    transition: background .15s;
    margin-bottom: 4px;
}

.ql-item:last-child {
    margin-bottom: 0;
}

.ql-item:hover {
    background: #f5f7fb;
}

.ql-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border-radius: 8px;
    display: grid;
    place-items: center;
    color: #fff;
}

.ql-icon.blue   { background: #1572e8; }
.ql-icon.purple { background: #716aca; }
.ql-icon.orange { background: #ff9e27; }
.ql-icon.green  { background: #31ce36; }
.ql-icon.dark   { background: #1f2937; }

.ql-label {
    margin: 0 0 2px;
    font-size: 14px;
    font-weight: 600;
    color: #44495a;
}

.ql-sub {
    margin: 0;
    font-size: 12px;
    color: #8d9498;
}

.activity-panel {
    padding: 20px;
}

.login-chart {
    display: grid;
    grid-template-columns: 96px 1fr;
    gap: 18px;
    align-items: stretch;
    padding: 12px;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
}

.chart-summary {
    display: grid;
    place-content: center;
    text-align: center;
    border-radius: 6px;
    background: #ffffff;
    border: 1px solid #e5edf6;
    min-height: 132px;
}

.chart-summary span,
.bar-label,
.bar-value {
    color: #8d9498;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.chart-summary strong {
    margin-top: 4px;
    color: #2f3441;
    font-size: 30px;
    font-weight: 700;
    line-height: 1;
}

.chart-bars {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    align-items: end;
    min-height: 132px;
}

.chart-bar-item {
    min-width: 0;
    display: grid;
    grid-template-rows: 18px 1fr 18px;
    gap: 6px;
    justify-items: center;
}

.bar-track {
    width: 100%;
    max-width: 44px;
    height: 88px;
    display: flex;
    align-items: end;
    border-radius: 8px 8px 4px 4px;
    background: #eaf2fd;
    overflow: hidden;
}

.bar-fill {
    width: 100%;
    min-height: 0;
    border-radius: 8px 8px 4px 4px;
    background: linear-gradient(180deg, #1572e8 0%, #1158b4 100%);
}

.bar-label {
    max-width: 72px;
    overflow: hidden;
    text-align: center;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.history-block {
    margin-top: 18px;
}

.revision-box {
    margin-top: 14px;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #f5d08a;
    background: #fffbeb;
    color: #92400e;
    font-size: 13px;
    line-height: 1.5;
}

.revision-box strong {
    display: block;
    margin-bottom: 4px;
    color: #78350f;
}

.revision-box p {
    margin: 0;
    white-space: pre-line;
}

.history-list {
    display: grid;
    gap: 12px;
}

.history-item {
    display: grid;
    grid-template-columns: 10px 1fr;
    gap: 12px;
    padding: 12px;
    border-radius: 6px;
    background: #ffffff;
    border: 1px solid #edf2f7;
}

.history-dot {
    width: 10px;
    height: 10px;
    margin-top: 5px;
    border-radius: 50%;
    background: #1572e8;
    box-shadow: 0 0 0 4px #eaf2fd;
}

.history-title {
    margin: 0 0 4px;
    color: #44495a;
    font-size: 13px;
    font-weight: 700;
}

.history-meta {
    margin: 0 0 3px;
    color: #8d9498;
    font-size: 12px;
    line-height: 1.35;
}

.history-empty {
    margin: 0;
    padding: 12px;
    border-radius: 6px;
    background: #f8fafc;
    color: #8d9498;
    font-size: 13px;
}

@media (max-width: 520px) {
    .login-chart {
        grid-template-columns: 1fr;
    }

    .chart-summary {
        min-height: 86px;
    }
}

@media (max-width: 980px) {
    .page-head {
        padding: 28px 18px 48px;
    }

    .page-content {
        padding: 0 18px 40px;
    }

    .content-grid {
        grid-template-columns: 1fr;
    }
}
</style>
