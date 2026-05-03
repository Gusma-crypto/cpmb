<script setup>
import SweetAlert from '@/Components/SweetAlert.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const mobileMenuOpen = ref(false);

const user = computed(() => page.props.auth?.user || {});
const roles = computed(() => page.props.auth?.roles || []);
const displayName = computed(() => user.value?.name || 'Mahasiswa');
const profilePhotoUrl = computed(() => user.value?.profile_photo_url || null);
const profileInitials = computed(() => displayName.value
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase());
const displayRole = computed(() => {
    const role = roles.value[0] || user.value?.role || 'student';

    return role.charAt(0).toUpperCase() + role.slice(1);
});

const navItems = [
    {
        label: 'Pendaftaran',
        routeName: 'student.registrations.index',
        current: 'student.registrations.*',
    },
    {
        label: 'Biodata',
        routeName: 'student.biodata.index',
        current: 'student.biodata.*',
    },
    {
        label: 'Dokumen',
        routeName: 'student.documents.index',
        current: 'student.documents.*',
    },
    {
        label: 'Pembayaran',
        routeName: 'student.payments.index',
        current: 'student.payments.*',
    },
    {
        label: 'Kartu Ujian',
        routeName: 'student.exam-card.index',
        current: 'student.exam-card.*',
    },
    {
        label: 'Hasil CBT',
        routeName: 'student.cbt.results.index',
        current: 'student.cbt.results.*',
    },
];

const hasRoute = (name) => {
    try {
        if (!name || typeof route !== 'function') {
            return false;
        }

        const routeHelper = route();

        if (typeof routeHelper?.has === 'function') {
            return routeHelper.has(name);
        }

        route(name);
        return true;
    } catch {
        return false;
    }
};

const hrefFor = (name) => (hasRoute(name) ? route(name) : '#');

const isActive = (pattern) => {
    try {
        return Boolean(pattern) && typeof route === 'function' && route().current(pattern);
    } catch {
        return false;
    }
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

const logout = () => {
    closeMobileMenu();

    if (!hasRoute('logout')) {
        return;
    }

    router.post(route('logout'));
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 text-gray-900">
        <header class="sticky top-0 z-40 border-b border-blue-700 bg-blue-700 text-white shadow-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-5 px-4 sm:px-6 lg:px-8">
                <Link :href="hrefFor('student.dashboard')" class="flex shrink-0 items-center gap-3" @click="closeMobileMenu">
                    <span class="flex h-9 w-9 items-center justify-center rounded-md bg-white/15 ring-1 ring-white/20">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path d="m12 3 9 5-9 5-9-5 9-5Zm7 9-7 4-7-4m14 4-7 4-7-4" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <div class="leading-tight">
                        <p class="text-sm font-semibold">Universitas</p>
                        <p class="text-xs text-blue-100">PMB Online</p>
                    </div>
                </Link>

                <nav class="hidden min-w-0 flex-1 items-center justify-start gap-1 lg:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.routeName"
                        :href="hrefFor(item.routeName)"
                        class="inline-flex items-center rounded-md px-3 py-2 text-sm font-medium transition"
                        :class="[
                            isActive(item.current) ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-50 hover:bg-transparent hover:text-white',
                            !hasRoute(item.routeName) ? 'cursor-not-allowed opacity-60' : '',
                        ]"
                        :title="hasRoute(item.routeName) ? item.label : 'Route belum tersedia'"
                    >
                        {{ item.label }}
                    </Link>
                </nav>

                <div class="ml-auto hidden items-center gap-3 lg:flex">
                    <Link :href="hrefFor('profile.edit')" class="flex items-center gap-2 rounded-md px-2 py-1.5 transition hover:bg-transparent">
                        <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-white text-sm font-bold text-blue-700">
                            <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="displayName" class="h-full w-full object-cover" />
                            <span v-else>{{ profileInitials }}</span>
                            <svg v-if="!profilePhotoUrl && !profileInitials" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </span>
                        <span class="max-w-28 truncate text-sm">
                            {{ displayName }}
                            <span class="block text-xs text-blue-100">{{ displayRole }}</span>
                        </span>
                    </Link>
                    <button type="button" class="inline-flex items-center gap-2 rounded-md border border-white/30 px-3 py-2 text-sm font-semibold text-white transition hover:bg-transparent hover:text-white" @click="logout">
                        Logout
                    </button>
                </div>

                <button class="ml-auto inline-flex h-10 w-10 items-center justify-center rounded-md text-white transition hover:bg-transparent lg:hidden" type="button" :aria-expanded="mobileMenuOpen" aria-label="Toggle navigation" @click="toggleMobileMenu">
                    <svg v-if="!mobileMenuOpen" class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                    </svg>
                    <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                        <path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div v-show="mobileMenuOpen" class="border-t border-white/10 bg-blue-700 lg:hidden">
                <div class="mx-auto max-w-7xl space-y-1 px-4 py-3 sm:px-6">
                    <Link
                        v-for="item in navItems"
                        :key="item.routeName"
                        :href="hrefFor(item.routeName)"
                        class="flex items-center justify-between rounded-md px-3 py-2 text-sm font-medium transition"
                        :class="[
                            isActive(item.current) ? 'bg-white text-blue-700' : 'text-blue-50 hover:bg-transparent hover:text-white',
                            !hasRoute(item.routeName) ? 'cursor-not-allowed opacity-60' : '',
                        ]"
                        :title="hasRoute(item.routeName) ? item.label : 'Route belum tersedia'"
                        @click="closeMobileMenu"
                    >
                        <span>{{ item.label }}</span>
                        <span v-if="isActive(item.current)" class="text-xs font-semibold">Aktif</span>
                    </Link>

                    <div class="mt-3 border-t border-white/10 px-3 pt-3 text-sm text-blue-50">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-white text-sm font-bold text-blue-700">
                                <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="displayName" class="h-full w-full object-cover" />
                                <span v-else>{{ profileInitials }}</span>
                            </span>
                            <div>
                                <p class="font-semibold text-white">{{ displayName }}</p>
                                <p class="text-xs">{{ displayRole }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <Link :href="hrefFor('profile.edit')" class="rounded-md border border-white/30 px-3 py-2 text-sm font-semibold text-white" @click="closeMobileMenu">
                                Profile
                            </Link>
                            <button type="button" class="rounded-md border border-white/30 px-3 py-2 text-sm font-semibold text-white" @click="logout">
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <SweetAlert />
    </div>
</template>
