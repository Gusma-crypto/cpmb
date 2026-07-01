<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentRegistrations: Array,
});

const statusLabel = {
    draft: 'Draft',
    submitted: 'Menunggu Verifikasi',
    under_review: 'Sedang Direview',
    revision_required: 'Perlu Revisi',
    verified: 'Terverifikasi',
    rejected: 'Ditolak',
    exam_ready: 'Siap Ujian',
    accepted: 'Diterima',
};

const statusClass = {
    draft: 'badge-gray',
    submitted: 'badge-yellow',
    under_review: 'badge-blue',
    revision_required: 'badge-yellow',
    verified: 'badge-blue',
    rejected: 'badge-red',
    exam_ready: 'badge-green',
    accepted: 'badge-green',
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout>
        <section class="page-head">
            <div>
                <h1>Dashboard</h1>
                <p>Sistem Penerimaan Mahasiswa Baru</p>
            </div>
            <div class="head-actions">
                <Link class="pill fill" :href="route('admin.registrations.index')">Lihat Pendaftaran</Link>
            </div>
        </section>

        <section class="page-content">
            <div class="stats-grid">
                <article class="stat-card">
                    <div class="stat-icon blue">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 10v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.total_students }}</p>
                        <p class="stat-label">Total Mahasiswa</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon purple">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="M4 5h16v13H4V5Zm6 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.total_registrations }}</p>
                        <p class="stat-label">Total Pendaftaran</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon orange">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Zm0-6v-4m0-4h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.pending }}</p>
                        <p class="stat-label">Menunggu Verifikasi</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon green">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.exam_ready }}</p>
                        <p class="stat-label">Siap Ujian</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon green">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="m7 11 3 3 7-7M5 21h14M12 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.accepted ?? 0 }}</p>
                        <p class="stat-label">Diterima</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon blue">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="M9 12l2 2 4-4M4 5h16v14H4V5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.cbt_attempts ?? 0 }}</p>
                        <p class="stat-label">Attempt CBT</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon green">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="M12 3v18M5 12h14M6 18l12-12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.cbt_average_score ?? 0 }}</p>
                        <p class="stat-label">Rata-rata CBT</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon green">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="m7 11 3 3 7-7M5 21h14M12 3v4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.cbt_passed ?? 0 }}</p>
                        <p class="stat-label">Lulus CBT</p>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon orange">
                        <svg viewBox="0 0 24 24" width="26" height="26" fill="none">
                            <path d="M12 8v5l3 3M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="stat-body">
                        <p class="stat-value">{{ stats.cbt_unpublished ?? 0 }}</p>
                        <p class="stat-label">Hasil Belum Publish</p>
                    </div>
                </article>
            </div>

            <article class="card">
                <div class="card-header">
                    <h2>Pendaftaran Terbaru</h2>
                    <Link :href="route('admin.registrations.index')" class="card-link">Lihat Semua →</Link>
                </div>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No. Pendaftaran</th>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="recentRegistrations.length === 0">
                                <td colspan="5" class="empty-row">Belum ada data pendaftaran.</td>
                            </tr>
                            <tr v-for="reg in recentRegistrations" :key="reg.id">
                                <td class="mono">{{ reg.registration_number }}</td>
                                <td>{{ reg.user?.name ?? '-' }}</td>
                                <td>
                                    <span class="badge" :class="statusClass[reg.status]">
                                        {{ statusLabel[reg.status] ?? reg.status }}
                                    </span>
                                </td>
                                <td>{{ formatDate(reg.created_at) }}</td>
                                <td class="action-col">
                                    <Link :href="route('registrations.show', reg.id)" class="btn-detail">Detail</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </AdminLayout>
</template>

<style scoped>
.page-head {
    min-height: 140px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
    padding: 40px 31px 54px;
    background: #1158b4;
    color: #fff;
    animation: slideDown .38s ease both;
}

.page-head h1 {
    margin: 0 0 10px;
    font-size: 24px;
    line-height: 1.15;
    font-weight: 700;
}

.page-head p {
    margin: 0;
    color: rgba(255, 255, 255, .72);
    font-size: 14px;
    font-weight: 300;
}

.head-actions {
    display: flex;
    gap: 10px;
    padding-top: 10px;
}

.pill.fill {
    min-width: 160px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    border: 1px solid #716aca;
    background: #716aca;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}

.pill.fill:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px rgba(113, 106, 202, .32);
    background: #6259c7;
}

.page-content {
    padding: 0 31px 60px;
    margin-top: -28px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.stat-card {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 7px 18px rgba(31, 45, 61, .08);
    border: 1px solid rgba(235, 236, 236, .8);
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    animation: riseIn .42s ease both;
    transition: transform .2s ease, box-shadow .2s ease;
}

.stat-card:nth-child(2) { animation-delay: .05s; }
.stat-card:nth-child(3) { animation-delay: .1s; }
.stat-card:nth-child(4) { animation-delay: .15s; }

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px rgba(31, 45, 61, .13);
}

.stat-icon {
    width: 52px;
    height: 52px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    border-radius: 8px;
    color: #fff;
    animation: popIn .42s ease both;
}

.stat-icon.blue   { background: #1572e8; }
.stat-icon.purple { background: #716aca; }
.stat-icon.orange { background: #ff9e27; }
.stat-icon.green  { background: #31ce36; }

.stat-value {
    margin: 0 0 4px;
    font-size: 28px;
    font-weight: 700;
    color: #44495a;
    line-height: 1;
}

.stat-label {
    margin: 0;
    font-size: 13px;
    color: #8d9498;
    font-weight: 500;
}

.card {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 7px 18px rgba(31, 45, 61, .08);
    border: 1px solid rgba(235, 236, 236, .8);
    animation: riseIn .42s ease .18s both;
    transition: box-shadow .2s ease, transform .2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(31, 45, 61, .12);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 20px 0;
}

.card-header h2 {
    margin: 0;
    color: #44495a;
    font-size: 18px;
    font-weight: 600;
}

.card-link {
    font-size: 13px;
    color: #1572e8;
    text-decoration: none;
    font-weight: 500;
}

.table-wrap {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
}

.table th,
.table td {
    padding: 12px 20px;
    text-align: left;
    font-size: 13px;
    border-bottom: 1px solid #f0f0f0;
}

.table th {
    color: #8d9498;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 11px;
    letter-spacing: .04em;
    background: #fafbfc;
}

.table tbody tr:last-child td {
    border-bottom: 0;
}

.table tbody tr:hover td {
    background: #f8faff;
}

.table tbody tr {
    transition: background .18s ease;
}

.mono {
    font-family: monospace;
    font-size: 13px;
    color: #575962;
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

.empty-row {
    text-align: center;
    color: #8d9498;
    padding: 32px 20px;
}

.action-col {
    text-align: right;
}

.btn-detail {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 5px;
    border: 1px solid #1572e8;
    color: #1572e8;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: background .15s, color .15s;
}

.btn-detail:hover {
    background: #1572e8;
    color: #fff;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes riseIn {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes popIn {
    0% {
        opacity: 0;
        transform: scale(.86);
    }
    70% {
        transform: scale(1.04);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 980px) {
    .page-head {
        padding: 30px 18px 52px;
        flex-direction: column;
    }

    .head-actions { padding-top: 0; }

    .page-content {
        padding: 0 18px 40px;
    }
}

@media (max-width: 600px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
