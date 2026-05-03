<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    question: { type: Object, default: null },
    categories: { type: Array, required: true },
    types: { type: Array, required: true },
    difficulties: { type: Array, required: true },
    submitUrl: { type: String, required: true },
    method: { type: String, default: 'post' },
});

const defaultOptions = () => [
    { option_text: '', is_correct: true },
    { option_text: '', is_correct: false },
];

const form = useForm({
    category_id: props.question?.category_id || '',
    type: props.question?.type || 'multiple_choice',
    question_text: props.question?.question_text || '',
    explanation: props.question?.explanation || '',
    difficulty: props.question?.difficulty || 'medium',
    is_active: props.question?.is_active ?? true,
    options: props.question?.options?.length
        ? props.question.options.map((option) => ({
            option_text: option.option_text,
            is_correct: Boolean(option.is_correct),
        }))
        : defaultOptions(),
});

const correctIndex = computed({
    get() {
        const index = form.options.findIndex((option) => option.is_correct);
        return index >= 0 ? index : 0;
    },
    set(value) {
        form.options = form.options.map((option, index) => ({
            ...option,
            is_correct: index === Number(value),
        }));
    },
});

watch(() => form.type, (type) => {
    if (type !== 'true_false') return;

    form.options = [
        { option_text: form.options[0]?.option_text || 'Benar', is_correct: correctIndex.value === 0 },
        { option_text: form.options[1]?.option_text || 'Salah', is_correct: correctIndex.value === 1 },
    ];

    if (!form.options.some((option) => option.is_correct)) {
        form.options[0].is_correct = true;
    }
});

const addOption = () => {
    form.options.push({ option_text: '', is_correct: false });
};

const removeOption = (index) => {
    if (form.options.length <= 2) return;

    const wasCorrect = form.options[index].is_correct;
    form.options.splice(index, 1);

    if (wasCorrect && form.options.length > 0) {
        form.options[0].is_correct = true;
    }
};

const submit = () => {
    if (props.method === 'put') {
        form.put(props.submitUrl);
        return;
    }

    form.post(props.submitUrl);
};
</script>

<template>
    <form class="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg" @submit.prevent="submit">
        <div class="grid gap-6 sm:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Kategori</label>
                <select v-model="form.category_id" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih kategori</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                </select>
                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tipe Soal</label>
                <select v-model="form.type" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option v-for="type in types" :key="type" :value="type">{{ type }}</option>
                </select>
                <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Tingkat Kesulitan</label>
                <select v-model="form.difficulty" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option v-for="difficulty in difficulties" :key="difficulty" :value="difficulty">{{ difficulty }}</option>
                </select>
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Pertanyaan</label>
            <textarea v-model="form.question_text" rows="5" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            <p v-if="form.errors.question_text" class="mt-1 text-sm text-red-600">{{ form.errors.question_text }}</p>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Pembahasan</label>
            <textarea v-model="form.explanation" rows="3" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
        </div>

        <div class="rounded-lg border border-gray-200">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Pilihan Jawaban</h2>
                    <p class="text-xs text-gray-500">Minimal 2 opsi dan tepat 1 jawaban benar.</p>
                </div>
                <button v-if="form.type !== 'true_false'" type="button" class="rounded-md bg-gray-900 px-3 py-2 text-xs font-semibold text-white" @click="addOption">
                    Tambah Opsi
                </button>
            </div>
            <div class="divide-y divide-gray-200">
                <div v-for="(option, index) in form.options" :key="index" class="grid gap-3 px-4 py-4 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input v-model="correctIndex" type="radio" :value="index" class="border-gray-300 text-blue-600 focus:ring-blue-500" />
                        Benar
                    </label>
                    <input v-model="option.option_text" type="text" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" :placeholder="`Opsi ${index + 1}`" />
                    <button type="button" class="rounded-md px-3 py-2 text-sm font-semibold" :class="form.options.length <= 2 ? 'cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-red-50 text-red-700'" @click="removeOption(index)">
                        Hapus
                    </button>
                    <p v-if="form.errors[`options.${index}.option_text`]" class="sm:col-start-2 text-sm text-red-600">{{ form.errors[`options.${index}.option_text`] }}</p>
                </div>
            </div>
        </div>
        <p v-if="form.errors.options" class="text-sm text-red-600">{{ form.errors.options }}</p>

        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
            <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" />
            Aktif
        </label>

        <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
            <Link :href="route('admin.questions.index')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">Batal</Link>
            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white" :disabled="form.processing">Simpan</button>
        </div>
    </form>
</template>
