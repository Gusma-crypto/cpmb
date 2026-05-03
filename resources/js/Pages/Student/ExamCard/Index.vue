<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    cards: {
        type: Array,
        default: () => [],
    },
});

const universityName = 'Universitas';
const universitySubtitle = 'Penerimaan Mahasiswa Baru';

const formatDate = (value) => {
    if (!value) return '-';

    return new Date(value).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const formatTime = (value) => {
    if (!value) return '-';
    if (/^\d{2}:\d{2}/.test(value)) return value.slice(0, 5);

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const fullName = (card) => card.registration?.user?.name || card.user?.name || '-';
const registrationNumber = (card) => card.registration?.registration_number || '-';
const programName = (card) => card.registration?.program?.name || '-';
const academicYear = (card) => card.registration?.academic_year?.label || card.registration?.academicYear?.label || '-';
const scheduleTitle = (card) => card.schedule?.title || '-';
const examType = (card) => card.schedule?.exam_type || '-';
const examDate = (card) => formatDate(card.schedule?.exam_date);
const examTime = (card) => `${formatTime(card.schedule?.start_time)} - ${formatTime(card.schedule?.end_time)}`;
const roomName = (card) => card.room_assignment?.room?.name || card.roomAssignment?.room?.name || '-';
const roomCode = (card) => card.room_assignment?.room?.code || card.roomAssignment?.room?.code || '-';
const roomLocation = (card) => card.room_assignment?.room?.location || card.roomAssignment?.room?.location || '-';
const supervisorName = (card) => card.room_assignment?.supervisor?.name || card.roomAssignment?.supervisor?.name || 'Tanpa pengawas';
const canStartCbt = (card) => Boolean(card.schedule?.cbt_exam_id) && card.schedule?.status === 'active';
const cbtMessage = (card) => {
    if (!card.schedule?.cbt_exam_id) return 'Jadwal ini belum dihubungkan ke Paket CBT oleh admin.';
    if (card.schedule?.status !== 'active') return 'Jadwal ujian belum aktif.';

    return '';
};

const printCard = (card) => {
    router.post(route('student.exam-card.print', card.id), {}, {
        preserveScroll: true,
        onSuccess: () => window.print(),
    });
};
</script>

<template>
    <Head title="Kartu Ujian" />

    <StudentLayout>
        <div class="py-8 print:bg-white print:py-0">
            <div class="mx-auto max-w-5xl px-4 print:max-w-none print:px-0">
                <div class="mb-6 print:hidden">
                    <h1 class="text-2xl font-semibold text-gray-900">Kartu Ujian</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        Kartu ini sudah memuat data pendaftaran, jadwal ujian, ruang ujian, dan bukti peserta untuk dicetak.
                    </p>
                </div>

                <div class="space-y-6 print:space-y-0">
                    <article
                        v-for="card in cards"
                        :key="card.id"
                        class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm print:break-after-page print:rounded-none print:border-gray-900 print:shadow-none"
                    >
                        <div class="border-b border-gray-200 px-8 py-6 print:border-gray-900">
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-blue-700 text-white print:border print:border-gray-900 print:bg-white print:text-gray-900">
                                        <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                            <path d="m12 3 9 5-9 5-9-5 9-5Zm7 9-7 4-7-4m14 4-7 4-7-4" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700 print:text-gray-900">{{ universitySubtitle }}</p>
                                        <h2 class="text-2xl font-bold text-gray-900">{{ universityName }}</h2>
                                        <p class="mt-1 text-sm text-gray-500 print:text-gray-700">Bukti pendaftaran untuk mengikuti ujian PMB</p>
                                    </div>
                                </div>

                                <div class="text-left sm:text-right">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nomor Kartu</p>
                                    <p class="mt-1 font-mono text-lg font-bold text-gray-900">{{ card.card_number }}</p>
                                    <button class="mt-4 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white print:hidden" type="button" @click="printCard(card)">
                                        Cetak Kartu
                                    </button>
                                    <Link
                                        v-if="card.schedule?.cbt_exam_id"
                                        :href="route('student.cbt.start', card.schedule.id)"
                                        class="ml-2 mt-4 inline-flex rounded-md px-4 py-2 text-sm font-semibold print:hidden"
                                        :class="canStartCbt(card) ? 'bg-gray-900 text-white hover:bg-gray-800' : 'pointer-events-none bg-gray-100 text-gray-400'"
                                    >
                                        Mulai CBT
                                    </Link>
                                    <p v-if="cbtMessage(card)" class="mt-3 max-w-xs text-xs text-red-600 print:hidden">
                                        {{ cbtMessage(card) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-6 px-8 py-6 lg:grid-cols-[1.1fr_0.9fr] print:grid-cols-[1.1fr_0.9fr]">
                            <section>
                                <h3 class="border-b border-gray-200 pb-2 text-sm font-bold uppercase tracking-wide text-gray-700 print:border-gray-900">Data Peserta</h3>
                                <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">Nama Peserta</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ fullName(card) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">No. Pendaftaran</dt>
                                        <dd class="mt-1 font-mono text-sm text-gray-900">{{ registrationNumber(card) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">No. Peserta</dt>
                                        <dd class="mt-1 font-mono text-sm font-semibold text-gray-900">{{ card.participant_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">Program Studi</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ programName(card) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">Tahun Akademik</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ academicYear(card) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">Status Kartu</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ card.status }}</dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="rounded-lg border border-blue-100 bg-blue-50 p-5 print:border-gray-900 print:bg-white">
                                <h3 class="text-sm font-bold uppercase tracking-wide text-blue-800 print:text-gray-900">Kode Verifikasi</h3>
                                <p class="mt-3 break-all font-mono text-2xl font-bold text-blue-900 print:text-gray-900">{{ card.verification_code }}</p>
                                <p class="mt-3 text-xs leading-5 text-blue-800 print:text-gray-700">
                                    Tunjukkan kartu ini kepada panitia saat masuk ruang ujian. Kartu ini menjadi bukti pendaftaran dan bukti peserta ujian.
                                </p>
                            </section>
                        </div>

                        <div class="grid gap-6 border-t border-gray-200 px-8 py-6 lg:grid-cols-2 print:grid-cols-2 print:border-gray-900">
                            <section>
                                <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700">Jadwal Ujian</h3>
                                <dl class="mt-4 space-y-3">
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">Judul Ujian</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ scheduleTitle(card) }}</dd>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-xs font-semibold uppercase text-gray-500">Tanggal</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ examDate(card) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase text-gray-500">Waktu</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ examTime(card) }}</dd>
                                        </div>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">Tipe Ujian</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ examType(card) }}</dd>
                                    </div>
                                </dl>
                            </section>

                            <section>
                                <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700">Ruang Ujian</h3>
                                <dl class="mt-4 space-y-3">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-xs font-semibold uppercase text-gray-500">Ruang</dt>
                                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ roomName(card) }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase text-gray-500">Kode Ruang</dt>
                                            <dd class="mt-1 text-sm text-gray-900">{{ roomCode(card) }}</dd>
                                        </div>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">Lokasi</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ roomLocation(card) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-semibold uppercase text-gray-500">Pengawas</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ supervisorName(card) }}</dd>
                                    </div>
                                </dl>
                            </section>
                        </div>

                        <div class="border-t border-gray-200 px-8 py-5 print:border-gray-900">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-gray-700">Ketentuan</h3>
                            <ol class="mt-3 list-decimal space-y-1 pl-5 text-sm text-gray-700">
                                <li>Peserta wajib membawa kartu ujian ini saat ujian berlangsung.</li>
                                <li>Peserta wajib membawa identitas diri yang valid.</li>
                                <li>Peserta hadir sesuai jadwal dan ruang yang tercantum pada kartu.</li>
                            </ol>
                        </div>
                    </article>

                    <div v-if="cards.length === 0" class="bg-white p-10 text-center shadow-sm sm:rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900">Kartu Ujian Belum Tersedia</h2>
                        <p class="mt-2 text-sm text-gray-500">
                            Pastikan pendaftaran sudah diverifikasi dan Anda sudah memiliki penempatan ruang ujian.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
