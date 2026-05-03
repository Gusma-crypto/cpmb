<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    form: {
        type: Object,
        required: true,
    },
    registrations: {
        type: Array,
        default: () => [],
    },
    canManageAll: {
        type: Boolean,
        default: false,
    },
    submitLabel: {
        type: String,
        default: 'Simpan',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    showCancel: {
        type: Boolean,
        default: true,
    },
    submitDisabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['submit']);

const religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
const provinces = [
    'Aceh',
    'Sumatera Utara',
    'Sumatera Barat',
    'Riau',
    'Kepulauan Riau',
    'Jambi',
    'Sumatera Selatan',
    'Bangka Belitung',
    'Bengkulu',
    'Lampung',
    'DKI Jakarta',
    'Banten',
    'Jawa Barat',
    'Jawa Tengah',
    'DI Yogyakarta',
    'Jawa Timur',
    'Bali',
    'Nusa Tenggara Barat',
    'Nusa Tenggara Timur',
    'Kalimantan Barat',
    'Kalimantan Tengah',
    'Kalimantan Selatan',
    'Kalimantan Timur',
    'Kalimantan Utara',
    'Sulawesi Utara',
    'Gorontalo',
    'Sulawesi Tengah',
    'Sulawesi Barat',
    'Sulawesi Selatan',
    'Sulawesi Tenggara',
    'Maluku',
    'Maluku Utara',
    'Papua',
    'Papua Barat',
    'Papua Barat Daya',
    'Papua Pegunungan',
    'Papua Selatan',
    'Papua Tengah',
];
const graduationYears = computed(() => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 11 }, (_, index) => currentYear + 1 - index);
});
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <div v-if="canManageAll">
            <InputLabel for="registration_id" value="Pendaftaran" />
            <select id="registration_id" v-model="form.registration_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled">
                <option value="">Pilih pendaftaran</option>
                <option v-for="registration in registrations" :key="registration.id" :value="registration.id">
                    {{ registration.registration_number }} - {{ registration.user?.name || '-' }}
                </option>
            </select>
            <InputError :message="form.errors.registration_id" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <InputLabel for="nik" value="NIK" />
                <TextInput id="nik" v-model="form.nik" type="text" class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.nik" class="mt-2" />
            </div>

            <div>
                <InputLabel for="gender" value="Jenis Kelamin" />
                <select id="gender" v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required>
                    <option value="">Pilih jenis kelamin</option>
                    <option value="male">Laki-laki</option>
                    <option value="female">Perempuan</option>
                </select>
                <InputError :message="form.errors.gender" class="mt-2" />
            </div>

            <div>
                <InputLabel for="birth_place" value="Tempat Lahir" />
                <TextInput id="birth_place" v-model="form.birth_place" type="text" class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.birth_place" class="mt-2" />
            </div>

            <div>
                <InputLabel for="birth_date" value="Tanggal Lahir" />
                <TextInput id="birth_date" v-model="form.birth_date" type="date" class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.birth_date" class="mt-2" />
            </div>

            <div>
                <InputLabel for="religion" value="Agama" />
                <select id="religion" v-model="form.religion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required>
                    <option value="">Pilih agama</option>
                    <option v-for="religion in religions" :key="religion" :value="religion">{{ religion }}</option>
                </select>
                <InputError :message="form.errors.religion" class="mt-2" />
            </div>

            <div>
                <InputLabel for="province" value="Provinsi" />
                <select id="province" v-model="form.province" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required>
                    <option value="">Pilih provinsi</option>
                    <option v-for="province in provinces" :key="province" :value="province">{{ province }}</option>
                </select>
                <InputError :message="form.errors.province" class="mt-2" />
            </div>

            <div>
                <InputLabel for="city" value="Kota/Kabupaten" />
                <TextInput id="city" v-model="form.city" type="text" class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.city" class="mt-2" />
            </div>

            <div>
                <InputLabel for="school_name" value="Asal Sekolah" />
                <TextInput id="school_name" v-model="form.school_name" type="text" class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.school_name" class="mt-2" />
            </div>

            <div>
                <InputLabel for="school_graduation_year" value="Tahun Lulus" />
                <select id="school_graduation_year" v-model="form.school_graduation_year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required>
                    <option value="">Pilih tahun lulus</option>
                    <option v-for="year in graduationYears" :key="year" :value="year">{{ year }}</option>
                </select>
                <InputError :message="form.errors.school_graduation_year" class="mt-2" />
            </div>

            <div>
                <InputLabel for="parent_name" value="Nama Orang Tua" />
                <TextInput id="parent_name" v-model="form.parent_name" type="text" class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.parent_name" class="mt-2" />
            </div>

            <div>
                <InputLabel for="parent_phone" value="Telepon Orang Tua" />
                <TextInput id="parent_phone" v-model="form.parent_phone" type="text" inputmode="tel" placeholder="- atau 08xxxxxxxxxx / +62xxxxxxxxxx / 07xxxxxxxx" class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.parent_phone" class="mt-2" />
            </div>

            <div>
                <InputLabel for="parent_job" value="Pekerjaan Orang Tua" />
                <TextInput id="parent_job" v-model="form.parent_job" type="text" class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.parent_job" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <InputLabel for="address" value="Alamat" />
                <textarea id="address" v-model="form.address" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm disabled:bg-gray-100 disabled:text-gray-500" :disabled="disabled" required />
                <InputError :message="form.errors.address" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
            <Link v-if="showCancel" :href="route('biodata.index')" class="text-sm font-medium text-gray-600 hover:text-gray-900">Batal</Link>
            <PrimaryButton :class="{ 'opacity-25': form.processing || submitDisabled }" :disabled="form.processing || submitDisabled">
                {{ submitLabel }}
            </PrimaryButton>
        </div>
    </form>
</template>
