<script setup>
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onBeforeUnmount, onMounted, reactive, ref } from 'vue';

const props = defineProps({
    attempt: { type: Object, required: true },
    exam: { type: Object, required: true },
    questions: { type: Array, required: true },
    answers: { type: Array, default: () => [] },
    serverTime: { type: String, required: true },
});

const currentIndex = ref(0);
const saveState = ref('Tersimpan');
const tabSwitchWarning = ref('');
const fullscreenWarning = ref('');
const isFullscreen = ref(typeof document !== 'undefined' ? Boolean(document.fullscreenElement) : false);
const nowMs = ref(new Date(props.serverTime).getTime());
const answers = reactive({});
const flags = reactive({});
const saveTimers = new Map();

props.answers.forEach((answer) => {
    answers[answer.question_id] = answer.option_id || '';
    flags[answer.question_id] = Boolean(answer.is_flagged);
});

const currentQuestion = computed(() => props.questions[currentIndex.value] || null);
const expiresMs = computed(() => new Date(props.attempt.expires_at).getTime());
const remainingSeconds = computed(() => Math.max(0, Math.floor((expiresMs.value - nowMs.value) / 1000)));
const timerText = computed(() => {
    const hours = Math.floor(remainingSeconds.value / 3600).toString().padStart(2, '0');
    const minutes = Math.floor((remainingSeconds.value % 3600) / 60).toString().padStart(2, '0');
    const seconds = (remainingSeconds.value % 60).toString().padStart(2, '0');

    return `${hours}:${minutes}:${seconds}`;
});
const answeredCount = computed(() => Object.values(answers).filter(Boolean).length);
const flaggedCount = computed(() => Object.values(flags).filter(Boolean).length);
const isFinished = computed(() => props.attempt.status !== 'ongoing');
const isLocked = computed(() => !isFinished.value && !isFullscreen.value);

let intervalId = null;

const goTo = (index) => {
    if (isLocked.value) return;
    currentIndex.value = Math.min(Math.max(index, 0), props.questions.length - 1);
};

const next = () => goTo(currentIndex.value + 1);
const prev = () => goTo(currentIndex.value - 1);

const autosave = (questionId) => {
    if (isFinished.value || isLocked.value) return;
    saveState.value = 'Menyimpan...';

    if (saveTimers.has(questionId)) {
        clearTimeout(saveTimers.get(questionId));
    }

    saveTimers.set(questionId, setTimeout(async () => {
        try {
            await axios.post(route('student.cbt.autosave'), {
                attempt_uuid: props.attempt.uuid,
                question_id: questionId,
                option_id: answers[questionId] || null,
            });
            saveState.value = 'Tersimpan';
        } catch (error) {
            saveState.value = error.response?.data?.message || 'Gagal menyimpan';
        }
    }, 500));
};

const toggleFlag = async (questionId) => {
    if (isFinished.value || isLocked.value) return;
    flags[questionId] = !flags[questionId];

    try {
        await axios.post(route('student.cbt.flag'), {
            attempt_uuid: props.attempt.uuid,
            question_id: questionId,
            is_flagged: flags[questionId],
        });
    } catch {
        flags[questionId] = !flags[questionId];
    }
};

const heartbeat = async (tabSwitched = false) => {
    try {
        const response = await axios.post(route('student.cbt.heartbeat', props.attempt.uuid), {
            tab_switched: tabSwitched,
        });

        if (response.data.status !== 'ongoing') {
            window.location.href = route('student.exam-card.index');
            return;
        }

        if (tabSwitched) {
            tabSwitchWarning.value = `Pindah tab terdeteksi ${response.data.tab_switch_count} kali.`;
        }
    } catch {
        tabSwitchWarning.value = 'Koneksi heartbeat gagal. Pastikan jaringan lokal stabil.';
    }
};

const submitExam = () => {
    if (isLocked.value) return;
    if (!confirm('Submit ujian sekarang? Jawaban tidak dapat diubah setelah submit.')) return;

    router.post(route('student.cbt.submit', props.attempt.uuid));
};

const enterFullscreen = async () => {
    if (!document.documentElement.requestFullscreen) {
        fullscreenWarning.value = 'Browser tidak mendukung fullscreen. Gunakan browser terbaru untuk melanjutkan CBT.';
        return;
    }

    try {
        await document.documentElement.requestFullscreen();
        isFullscreen.value = true;
        fullscreenWarning.value = '';
    } catch {
        fullscreenWarning.value = 'Browser menolak fullscreen. Izinkan fullscreen untuk melanjutkan CBT.';
    }
};

const handleVisibility = () => {
    if (document.hidden) {
        heartbeat(true);
    }
};

const handleBlur = () => heartbeat(true);

const handleFullscreenChange = () => {
    isFullscreen.value = Boolean(document.fullscreenElement);

    if (!isFullscreen.value && !isFinished.value) {
        fullscreenWarning.value = 'Mode fullscreen keluar. Masuk kembali ke fullscreen untuk melanjutkan ujian.';
        heartbeat(true);
        return;
    }

    fullscreenWarning.value = '';
};

onMounted(() => {
    intervalId = setInterval(() => {
        nowMs.value += 1000;

        if (remainingSeconds.value <= 0) {
            heartbeat(false);
        }
    }, 1000);

    document.addEventListener('visibilitychange', handleVisibility);
    document.addEventListener('fullscreenchange', handleFullscreenChange);
    window.addEventListener('blur', handleBlur);

    enterFullscreen();
});

onBeforeUnmount(() => {
    if (intervalId) clearInterval(intervalId);
    document.removeEventListener('visibilitychange', handleVisibility);
    document.removeEventListener('fullscreenchange', handleFullscreenChange);
    window.removeEventListener('blur', handleBlur);
    saveTimers.forEach((timer) => clearTimeout(timer));
});
</script>

<template>
    <Head :title="`CBT - ${exam.title}`" />

    <div class="min-h-screen bg-gray-100 text-gray-900">
        <header class="sticky top-0 z-30 border-b border-blue-800 bg-blue-700 text-white shadow-sm">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6 lg:px-8">
                <div>
                    <p class="text-xs font-semibold uppercase text-blue-100">Computer Based Test</p>
                    <h1 class="text-lg font-bold">{{ exam.title }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-md bg-white/15 px-3 py-2 font-mono text-lg font-bold">{{ timerText }}</span>
                    <button type="button" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white" @click="submitExam">Submit</button>
                </div>
            </div>
        </header>

        <div v-if="isLocked" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/90 px-4 text-white">
            <div class="w-full max-w-md rounded-lg bg-white p-6 text-center text-gray-900 shadow-xl">
                <p class="text-xs font-semibold uppercase text-blue-700">CBT terkunci</p>
                <h2 class="mt-2 text-xl font-bold">Kembali ke fullscreen</h2>
                <p class="mt-3 text-sm leading-6 text-gray-600">
                    Ujian hanya dapat dikerjakan dalam mode fullscreen. Aktivitas keluar fullscreen dicatat sebagai audit CBT.
                </p>
                <p v-if="fullscreenWarning" class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">{{ fullscreenWarning }}</p>
                <button type="button" class="mt-5 rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white" @click="enterFullscreen">
                    Masuk Fullscreen
                </button>
            </div>
        </div>

        <main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[280px_1fr] lg:px-8">
            <aside class="rounded-lg bg-white p-4 shadow-sm">
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="rounded-md bg-blue-50 p-2 text-blue-700">
                        <div class="font-bold">{{ answeredCount }}</div>
                        <div>Terjawab</div>
                    </div>
                    <div class="rounded-md bg-amber-50 p-2 text-amber-700">
                        <div class="font-bold">{{ flaggedCount }}</div>
                        <div>Ragu</div>
                    </div>
                    <div class="rounded-md bg-gray-50 p-2 text-gray-700">
                        <div class="font-bold">{{ questions.length }}</div>
                        <div>Soal</div>
                    </div>
                </div>

                <p v-if="tabSwitchWarning" class="mt-4 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ tabSwitchWarning }}</p>
                <p class="mt-4 text-xs font-semibold uppercase text-gray-500">{{ saveState }}</p>

                <div class="mt-4 grid grid-cols-5 gap-2">
                    <button
                        v-for="(question, index) in questions"
                        :key="question.id"
                        type="button"
                        class="h-10 rounded-md text-sm font-semibold"
                        :class="[
                            index === currentIndex ? 'bg-blue-600 text-white' : answers[question.id] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700',
                            flags[question.id] ? 'ring-2 ring-amber-400' : '',
                        ]"
                        @click="goTo(index)"
                    >
                        {{ index + 1 }}
                    </button>
                </div>
            </aside>

            <section v-if="currentQuestion" class="rounded-lg bg-white p-6 shadow-sm">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase text-blue-700">Soal {{ currentIndex + 1 }}</p>
                        <div class="mt-3 whitespace-pre-line text-base leading-7 text-gray-900">{{ currentQuestion.question_text }}</div>
                    </div>
                    <button type="button" class="shrink-0 rounded-md px-3 py-2 text-sm font-semibold disabled:opacity-50" :disabled="isLocked" :class="flags[currentQuestion.id] ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700'" @click="toggleFlag(currentQuestion.id)">
                        {{ flags[currentQuestion.id] ? 'Ragu' : 'Tandai' }}
                    </button>
                </div>

                <div class="space-y-3">
                    <label
                        v-for="option in currentQuestion.options"
                        :key="option.id"
                        class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 text-sm transition"
                        :class="answers[currentQuestion.id] === option.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-blue-200'"
                    >
                        <input v-model="answers[currentQuestion.id]" type="radio" :name="`question-${currentQuestion.id}`" :value="option.id" class="mt-1 border-gray-300 text-blue-600 focus:ring-blue-500" :disabled="isLocked" @change="autosave(currentQuestion.id)" />
                        <span class="leading-6 text-gray-900">{{ option.option_text }}</span>
                    </label>
                </div>

                <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-5">
                    <button type="button" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 disabled:opacity-50" :disabled="currentIndex === 0 || isLocked" @click="prev">
                        Sebelumnya
                    </button>
                    <button type="button" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50" :disabled="currentIndex === questions.length - 1 || isLocked" @click="next">
                        Berikutnya
                    </button>
                </div>
            </section>
        </main>
    </div>
</template>
