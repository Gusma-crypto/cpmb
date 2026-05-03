<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    academicYears: {
        type: Array,
        required: true,
    },
    programs: {
        type: Array,
        required: true,
    },
    registrationWaves: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const requestedWaveId = new URLSearchParams(page.url.split('?')[1] || '').get('wave');
const initialWaveId = props.registrationWaves.some((wave) => String(wave.id) === String(requestedWaveId))
    ? requestedWaveId
    : props.registrationWaves.length === 1 ? props.registrationWaves[0].id : '';

const form = useForm({
    registration_wave_id: initialWaveId,
    academic_year_id: props.registrationWaves.find((wave) => String(wave.id) === String(initialWaveId))?.academic_year_id || '',
    program_id: '',
});

const selectedWave = computed(() => props.registrationWaves.find((wave) => String(wave.id) === String(form.registration_wave_id)));
const availablePrograms = computed(() => selectedWave.value?.programs || props.programs);
const hasOpenRegistrationWave = computed(() => props.registrationWaves.length > 0);

watch(selectedWave, (wave) => {
    form.academic_year_id = wave?.academic_year_id || '';
    form.program_id = '';
}, { immediate: true });

const programOptionLabel = (program) => {
    const details = [program.level, program.faculty].filter(Boolean).join(' - ');

    return details ? `${program.name} (${details})` : program.name;
};

const submit = () => {
    form.post(route('registrations.store'));
};
</script>

<template>
    <Head title="Formulir Pendaftaran PMB" />

    <RoleLayout>
        <div class="py-8">
            <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
                <h1 class="mb-6 text-2xl font-semibold text-gray-900">Formulir Pendaftaran PMB</h1>

                <div v-if="!hasOpenRegistrationWave" class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Gelombang pendaftaran belum dibuka. Hubungi panitia PMB untuk informasi lebih lanjut.
                </div>

                <div v-else-if="registrationWaves.every((wave) => !wave.programs?.length)" class="rounded-md border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                    Program studi belum tersedia. Hubungi panitia PMB untuk informasi lebih lanjut.
                </div>

                <div v-else class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <form class="space-y-6" @submit.prevent="submit">
                        <div>
                            <InputLabel for="registration_wave_id" value="Gelombang Pendaftaran" />
                            <select
                                id="registration_wave_id"
                                v-model="form.registration_wave_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">-- Pilih Gelombang --</option>
                                <option v-for="wave in registrationWaves" :key="wave.id" :value="wave.id">
                                    {{ wave.label }} - {{ wave.academic_year?.label || '-' }}
                                </option>
                            </select>
                            <InputError :message="form.errors.registration_wave_id" class="mt-2" />

                            <input v-model="form.academic_year_id" type="hidden" />
                            <InputError :message="form.errors.academic_year_id" class="mt-2" />

                            <div v-if="selectedWave" class="mt-4 rounded-md border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
                                <p class="font-semibold">{{ selectedWave.label }}</p>
                                <p class="mt-1 text-blue-800">{{ selectedWave.academic_year?.label || '-' }}</p>
                                <p v-if="selectedWave.description" class="mt-2 leading-5 text-blue-800">{{ selectedWave.description }}</p>
                            </div>
                        </div>

                        <div v-if="selectedWave">
                            <InputLabel for="program_id" value="Program Studi" />
                            <select
                                id="program_id"
                                v-model="form.program_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">-- Pilih Program Studi --</option>
                                <option v-for="program in availablePrograms" :key="program.id" :value="program.id">
                                    {{ programOptionLabel(program) }}
                                </option>
                            </select>
                            <InputError :message="form.errors.program_id" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                            <Link :href="route('registrations.index')" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                Batal
                            </Link>
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Daftar
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>
