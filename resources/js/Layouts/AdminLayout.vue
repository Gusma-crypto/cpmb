<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import SweetAlert from '@/Components/SweetAlert.vue';
import { computed, ref } from 'vue';

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const menuCollapsed = ref(false);
const masterPmbCollapsed = ref(false);
const masterCbtCollapsed = ref(false);
const page = usePage();
const topSearch = ref('');
const topSearchMessage = ref('');

const user = computed(() => page.props.auth?.user);
const roles = computed(() => page.props.auth?.roles || []);
const isAdmin = computed(() => roles.value.includes('admin') || roles.value.includes('superadmin'));
const isStudent = computed(() => roles.value.includes('student'));
const isStaff = computed(() => roles.value.includes('staff'));
const isDosen = computed(() => roles.value.includes('dosen'));
const displayName = computed(() => user.value?.name || 'Hizrian');
const profilePhotoUrl = computed(() => user.value?.profile_photo_url || null);
const profileInitials = computed(() => displayName.value
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase());
const displayRole = computed(() => {
    const role = roles.value[0] || user.value?.role || 'Administrator';

    return role.charAt(0).toUpperCase() + role.slice(1);
});
const menuItems = computed(() => {
    if (isAdmin.value) {
        return [
            { label: 'Dashboard', routeName: 'admin.dashboard', current: 'admin.dashboard', icon: 'dashboard' },
            { label: 'Pendaftaran', routeName: 'admin.registrations.index', current: 'admin.registrations.*', icon: 'list' },
            { label: 'Jadwal Ujian', routeName: 'admin.exam-schedules.index', current: 'admin.exam-schedules.*', icon: 'calendar' },
            { label: 'Ruang Ujian', routeName: 'admin.exam-rooms.index', current: 'admin.exam-rooms.*', icon: 'layers' },
            { label: 'Penempatan Peserta', routeName: 'admin.exam-room-assignments.index', current: 'admin.exam-room-assignments.*', icon: 'list' },
            { label: 'Kartu Ujian', routeName: 'admin.exam-cards.index', current: 'admin.exam-cards.*', icon: 'document' },
        ];
    }

    if (isStaff.value) {
        return [
            { label: 'Dashboard', routeName: 'staff.dashboard', current: 'staff.dashboard', icon: 'dashboard' },
            { label: 'Pendaftaran', routeName: 'staff.registrations.index', current: 'staff.registrations.*', icon: 'list' },
            { label: 'Verifikasi Dokumen', routeName: 'staff.documents.index', current: 'staff.documents.*', icon: 'document' },
            { label: 'Biodata Pendaftar', routeName: 'staff.biodata.index', current: 'staff.biodata.*', icon: 'user' },
            { label: 'Jadwal Ujian', routeName: 'staff.exam-schedules.index', current: 'staff.exam-schedules.*', icon: 'calendar' },
            { label: 'Ruang Ujian', routeName: 'staff.exam-rooms.index', current: 'staff.exam-rooms.*', icon: 'layers' },
            { label: 'Penempatan Peserta', routeName: 'staff.exam-room-assignments.index', current: 'staff.exam-room-assignments.*', icon: 'list' },
            { label: 'Kartu Ujian', routeName: 'staff.exam-cards.index', current: 'staff.exam-cards.*', icon: 'document' },
        ];
    }

    if (isDosen.value) {
        return [
            { label: 'Dashboard', routeName: 'dosen.dashboard', current: 'dosen.dashboard', icon: 'dashboard' },
            { label: 'Data Peserta', routeName: 'dosen.participants.index', current: 'dosen.participants.*', icon: 'list' },
            { label: 'Penilaian', routeName: 'dosen.assessments.index', current: 'dosen.assessments.*', icon: 'document' },
            { label: 'Jadwal Supervisi', routeName: 'dosen.exam-schedules.index', current: 'dosen.exam-schedules.*', icon: 'calendar' },
            { label: 'Ruang Supervisi', routeName: 'dosen.exam-rooms.index', current: 'dosen.exam-rooms.*', icon: 'layers' },
        ];
    }

    return [
        { label: 'Dashboard', routeName: 'student.dashboard', current: 'student.dashboard', icon: 'dashboard' },
        { label: 'Pendaftaran', routeName: 'student.registrations.index', current: 'student.registrations.*', icon: 'list' },
        { label: 'Biodata', routeName: 'student.biodata.index', current: 'student.biodata.*', icon: 'user' },
        { label: 'Dokumen', routeName: 'student.documents.index', current: 'student.documents.*', icon: 'document' },
        { label: 'Jadwal Ujian', routeName: 'student.exam-schedule.show', current: 'student.exam-schedule.*', icon: 'calendar' },
        { label: 'Ruang Ujian', routeName: 'student.exam-room.show', current: 'student.exam-room.*', icon: 'layers' },
        { label: 'Kartu Ujian', routeName: 'student.exam-card.index', current: 'student.exam-card.*', icon: 'document' },
        { label: 'Profile', routeName: 'profile.edit', current: 'profile.*', icon: 'user' },
    ];
});

const masterPmbItems = [
    {
        label: 'Tahun Akademik',
        routeName: 'admin.academic-years.index',
        current: 'admin.academic-years.*',
        icon: 'calendar',
    },

    {
        label: 'Program Studi',
        routeName: 'admin.programs.index',
        current: 'admin.programs.*',
        icon: 'list',
    },
    
    {
        label: 'Gel. Pendaftaran',
        routeName: 'admin.registration-waves.index',
        current: 'admin.registration-waves.*',
        icon: 'layers',
    },
    {
        label: 'Users',
        routeName: 'admin.users.index',
        current: 'admin.users.*',
        icon: 'user',
    },
];

const masterCbtItems = [
    {
        label: 'Paket CBT',
        routeName: 'admin.cbt.exams.index',
        current: 'admin.cbt.exams.*',
        icon: 'document',
    },
    {
        label: 'Kategori Soal',
        routeName: 'admin.question-categories.index',
        current: 'admin.question-categories.*',
        icon: 'layers',
    },
    {
        label: 'Bank Soal',
        routeName: 'admin.questions.index',
        current: 'admin.questions.*',
        icon: 'document',
    },
    {
        label: 'Hasil CBT',
        routeName: 'admin.cbt.results.index',
        current: 'admin.cbt.results.*',
        icon: 'list',
    },
];

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const toggleSidebar = () => {
    if (window.matchMedia('(max-width: 980px)').matches) {
        sidebarOpen.value = !sidebarOpen.value;
        return;
    }

    sidebarCollapsed.value = !sidebarCollapsed.value;
};

const toggleMenuGroup = () => {
    menuCollapsed.value = !menuCollapsed.value;
};

const toggleMasterPmbGroup = () => {
    masterPmbCollapsed.value = !masterPmbCollapsed.value;
};

const toggleMasterCbtGroup = () => {
    masterCbtCollapsed.value = !masterCbtCollapsed.value;
};

const hasRoute = (name) => {
    try {
        return typeof route === 'function' && typeof route().has === 'function' && route().has(name);
    } catch {
        return false;
    }
};

const searchAliases = {
    'admin.dashboard': ['home', 'beranda'],
    'staff.dashboard': ['home', 'beranda'],
    'dosen.dashboard': ['home', 'beranda'],
    'student.dashboard': ['home', 'beranda'],
    'admin.registrations.index': ['registration', 'registrasi', 'pendaftaran', 'daftar', 'pmb'],
    'staff.registrations.index': ['registration', 'registrasi', 'pendaftaran', 'daftar', 'pmb'],
    'student.registrations.index': ['registration', 'registrasi', 'pendaftaran', 'daftar', 'pmb'],
    'admin.academic-years.index': ['academic', 'akademik', 'tahun academic', 'tahun akademik', 'tahun ajaran'],
    'admin.programs.index': ['program', 'prodi', 'program studi', 'jurusan'],
    'admin.registration-waves.index': ['gelombang', 'gel pendaftaran', 'wave'],
    'admin.users.index': ['user', 'users', 'pengguna', 'akun'],
    'admin.exam-schedules.index': ['jadwal', 'jadwal ujian', 'schedule'],
    'staff.exam-schedules.index': ['jadwal', 'jadwal ujian', 'schedule'],
    'admin.exam-rooms.index': ['ruang', 'ruang ujian', 'room'],
    'staff.exam-rooms.index': ['ruang', 'ruang ujian', 'room'],
    'admin.exam-room-assignments.index': ['penempatan', 'penempatan peserta', 'assign'],
    'staff.exam-room-assignments.index': ['penempatan', 'penempatan peserta', 'assign'],
    'admin.exam-cards.index': ['kartu', 'kartu ujian', 'exam card'],
    'admin.cbt.exams.index': ['paket cbt', 'paket ujian', 'exam cbt', 'cbt exam'],
    'admin.question-categories.index': ['kategori soal', 'category question', 'question category', 'cbt'],
    'admin.questions.index': ['bank soal', 'soal', 'question', 'questions', 'cbt'],
    'admin.cbt.results.index': ['hasil cbt', 'nilai cbt', 'result cbt'],
    'staff.exam-cards.index': ['kartu', 'kartu ujian', 'exam card'],
    'student.exam-card.index': ['kartu', 'kartu ujian', 'exam card'],
    'student.exam-schedule.show': ['jadwal', 'jadwal ujian', 'schedule'],
    'student.exam-room.show': ['ruang', 'ruang ujian', 'room'],
    'staff.documents.index': ['dokumen', 'verifikasi dokumen', 'document'],
    'student.documents.index': ['dokumen', 'document'],
    'staff.biodata.index': ['biodata', 'biodata pendaftar'],
    'student.biodata.index': ['biodata'],
    'dosen.participants.index': ['peserta', 'data peserta', 'participant'],
    'dosen.assessments.index': ['penilaian', 'nilai', 'assessment'],
    'dosen.exam-schedules.index': ['jadwal', 'jadwal supervisi', 'schedule'],
    'dosen.exam-rooms.index': ['ruang', 'ruang supervisi', 'room'],
    'profile.edit': ['profile', 'profil', 'pengaturan'],
};

const searchableMenuItems = computed(() => [
    ...menuItems.value,
    ...(isAdmin.value ? masterPmbItems : []),
    ...(isAdmin.value ? masterCbtItems : []),
    { label: 'Profile', routeName: 'profile.edit', current: 'profile.*', icon: 'user' },
].filter((item) => hasRoute(item.routeName)));

const normalizeSearchKeyword = (value) => value.toString().toLowerCase().replace(/\s+/g, ' ').trim();

const shortcutRouteName = (search) => {
    const keyword = normalizeSearchKeyword(search);

    return searchableMenuItems.value.find((item) => {
        const aliases = searchAliases[item.routeName] || [];
        const terms = [item.label, item.routeName, ...aliases].map(normalizeSearchKeyword);

        return terms.some((term) => term.includes(keyword) || keyword.includes(term));
    })?.routeName || null;
};

const submitTopSearch = () => {
    const search = topSearch.value.trim();

    topSearchMessage.value = '';

    if (!search) {
        topSearchMessage.value = 'Ketik nama menu yang ingin dibuka.';
        return;
    }

    const targetRoute = shortcutRouteName(search);

    if (targetRoute) {
        topSearch.value = '';
        router.get(route(targetRoute));
        return;
    }

    topSearchMessage.value = `Menu "${search}" tidak ditemukan.`;
};
</script>

<template>
    <div class="atlantis" :class="{ 'sidebar-open': sidebarOpen, 'sidebar-collapsed': sidebarCollapsed }">
        <aside class="sidebar">
            <div class="brand">
                <Link class="brand-link" :href="route(isAdmin ? 'admin.dashboard' : isStaff ? 'staff.dashboard' : isDosen ? 'dosen.dashboard' : 'student.dashboard')">
                    <span class="brand-logo" aria-hidden="true"></span>
                    <span class="brand-text">Universitas</span>
                </Link>
            </div>

            <div class="sidebar-body">
                <div class="profile">
                    <span class="avatar" aria-hidden="true">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="displayName" />
                        <span v-else>{{ profileInitials }}</span>
                    </span>
                    <div>
                        <p class="profile-name">{{ displayName }}</p>
                        <p class="profile-role">{{ displayRole }}</p>
                    </div>
                    <!-- <span class="profile-caret">⌄</span> -->
                </div>

                <button class="sidebar-title sidebar-title-button" type="button" :aria-expanded="!menuCollapsed" @click="toggleMenuGroup">
                    <span>Menu</span>
                    <span class="group-caret" :class="{ collapsed: menuCollapsed }">⌄</span>
                </button>
                <ul v-show="!menuCollapsed" class="nav">
                    <li v-for="item in menuItems" :key="item.routeName">
                        <Link class="nav-link" :class="{ active: route().current(item.current) }" :href="route(item.routeName)" @click="closeSidebar">
                            <svg v-if="item.icon === 'dashboard'" viewBox="0 0 24 24" fill="none">
                                <path d="M3 11.3 12 4l9 7.3V21h-6v-6H9v6H3v-9.7Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                            </svg>
                            <svg v-else-if="item.icon === 'list'" viewBox="0 0 24 24" fill="none">
                                <path d="M4 5h16v13H4V5Zm6 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg v-else-if="item.icon === 'user'" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            <svg v-else-if="item.icon === 'calendar'" viewBox="0 0 24 24" fill="none">
                                <path d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none">
                                <path d="M7 3h7l5 5v13H7V3Zm7 0v5h5M10 13h6M10 17h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>

                <button v-if="isAdmin" class="sidebar-title sidebar-title-button" type="button" :aria-expanded="!masterPmbCollapsed" @click="toggleMasterPmbGroup">
                    <span>Master PMB</span>
                    <span class="group-caret" :class="{ collapsed: masterPmbCollapsed }">⌄</span>
                </button>
                <ul v-if="isAdmin" v-show="!masterPmbCollapsed" class="nav">
                    <li v-for="item in masterPmbItems" :key="item.routeName">
                        <Link class="nav-link" :class="{ active: route().current(item.current) }" :href="route(item.routeName)" @click="closeSidebar">
                            <svg v-if="item.icon === 'calendar'" viewBox="0 0 24 24" fill="none">
                                <path d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg v-else-if="item.icon === 'user'" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            </svg>
                            <svg v-else-if="item.icon === 'layers'" viewBox="0 0 24 24" fill="none">
                                <path d="m12 3 9 5-9 5-9-5 9-5Zm7 9-7 4-7-4m14 4-7 4-7-4" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none">
                                <path d="M4 5h16v13H4V5Zm6 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>

                <button v-if="isAdmin" class="sidebar-title sidebar-title-button" type="button" :aria-expanded="!masterCbtCollapsed" @click="toggleMasterCbtGroup">
                    <span>Master CBT</span>
                    <span class="group-caret" :class="{ collapsed: masterCbtCollapsed }">⌄</span>
                </button>
                <ul v-if="isAdmin" v-show="!masterCbtCollapsed" class="nav">
                    <li v-for="item in masterCbtItems" :key="item.routeName">
                        <Link class="nav-link" :class="{ active: route().current(item.current) }" :href="route(item.routeName)" @click="closeSidebar">
                            <svg v-if="item.icon === 'calendar'" viewBox="0 0 24 24" fill="none">
                                <path d="M7 3v4m10-4v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <svg v-else-if="item.icon === 'layers'" viewBox="0 0 24 24" fill="none">
                                <path d="m12 3 9 5-9 5-9-5 9-5Zm7 9-7 4-7-4m14 4-7 4-7-4" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none">
                                <path d="M4 5h16v13H4V5Zm6 16h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ item.label }}</span>
                        </Link>
                    </li>
                </ul>
            </div>
        </aside>

        <div class="overlay" @click="closeSidebar"></div>

        <main class="main">
            <header class="topbar">
                <button class="top-icon sidebar-control" type="button" :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'" @click="toggleSidebar">
                    <svg viewBox="0 0 24 24" width="23" height="23" fill="none">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" />
                    </svg>
                </button>

                <form class="search" @submit.prevent="submitTopSearch">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                        <path d="m21 21-4.2-4.2M10.7 18.3a7.6 7.6 0 1 1 0-15.2 7.6 7.6 0 0 1 0 15.2Z" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
                    </svg>
                    <input v-model="topSearch" type="search" placeholder="Search ..." @input="topSearchMessage = ''" />
                    <div v-if="topSearchMessage" class="search-message">
                        {{ topSearchMessage }}
                    </div>
                </form>

                <div class="top-spacer"></div>

                <button class="top-icon" type="button" aria-label="Mail">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                        <path d="M4 6h16v12H4V6Zm0 0 8 7 8-7" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    </svg>
                </button>
                <button class="top-icon" type="button" aria-label="Notifications">
                    <span class="notif-dot">4</span>
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none">
                        <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-4.3 13a2 2 0 0 1-3.4 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <button class="top-icon" type="button" aria-label="Layers">
                    <svg viewBox="0 0 24 24" width="23" height="23" fill="none">
                        <path d="m12 3 9 5-9 5-9-5 9-5Zm7 9-7 4-7-4m14 4-7 4-7-4" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    </svg>
                </button>
                <Link class="profile-action" :href="route('profile.edit')" title="Profile">
                    <span class="top-avatar" aria-hidden="true">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="displayName" />
                        <span v-else>{{ profileInitials }}</span>
                    </span>
                    <span class="profile-action-text">{{ displayName }}</span>
                </Link>
                <Link class="logout-button" :href="route('logout')" method="post" as="button" title="Logout">
                    <svg viewBox="0 0 24 24" width="21" height="21" fill="none">
                        <path d="M10 17l5-5-5-5M15 12H3m8-8h7a3 3 0 0 1 3 3v10a3 3 0 0 1-3 3h-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <span>Logout</span>
                </Link>
            </header>

            <slot />
        </main>

        <SweetAlert />
    </div>
</template>

<style scoped>
:global(body) {
    margin: 0;
    background: #f5f7fb;
    color: #2a2f3a;
    font-family: "Public Sans", Arial, sans-serif;
    letter-spacing: 0;
}

* {
    box-sizing: border-box;
}

.atlantis {
    min-height: 100vh;
    padding-left: 258px;
    background: #f5f7fb;
    transition: padding-left .24s ease;
}

.atlantis.sidebar-collapsed {
    padding-left: 78px;
}

.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 30;
    width: 258px;
    background: #fff;
    box-shadow: 0 0 20px rgba(69, 87, 105, .13);
    transition: width .24s ease, transform .24s ease;
}

.brand {
    height: 64px;
    display: flex;
    align-items: center;
    padding: 0 31px;
    background: #1572e8;
    color: #fff;
    font-size: 17px;
    font-weight: 700;
}

.brand-link {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    color: inherit;
    text-decoration: none;
}

.brand-text,
.profile > div,
.profile-caret,
.sidebar-title,
.nav-link span {
    transition: opacity .18s ease, transform .18s ease;
}

.brand-logo {
    width: 31px;
    height: 36px;
    display: grid;
    place-items: center;
    position: relative;
}

.brand-logo::before,
.brand-logo::after {
    content: "";
    position: absolute;
    border: 6px solid #fff;
    border-radius: 6px;
    transform: rotate(30deg);
}

.brand-logo::before {
    width: 22px;
    height: 22px;
}

.brand-logo::after {
    width: 8px;
    height: 8px;
    background: #1572e8;
}

.sidebar-body {
    height: calc(100vh - 64px);
    overflow-y: auto;
    padding: 22px 10px 24px;
}

.profile {
    min-height: 56px;
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr) 16px;
    align-items: center;
    gap: 14px;
    padding: 0 10px 18px;
    border-bottom: 1px solid #ebecec;
    margin-bottom: 19px;
}

.avatar,
.top-avatar {
    display: inline-grid;
    place-items: center;
    border-radius: 50%;
    overflow: hidden;
    background:
        radial-gradient(circle at 50% 38%, #f4c7a1 0 15%, transparent 16%),
        radial-gradient(circle at 50% 78%, #2f3747 0 27%, transparent 28%),
        linear-gradient(#dfe8f2, #f6f7f9);
    color: #1d4ed8;
    font-size: 12px;
    font-weight: 800;
}

.avatar img,
.top-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar {
    width: 44px;
    height: 44px;
}

.profile-name {
    margin: 0 0 5px;
    font-size: 13px;
    color: #575962;
}

.profile-role {
    margin: 0;
    font-size: 12px;
    color: #30333a;
    font-weight: 600;
}

.profile-caret {
    color: #8d9498;
}

.sidebar-title {
    margin: 20px 28px 13px;
    color: #83848a;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}

.sidebar-title-button {
    width: calc(100% - 56px);
    min-height: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 0;
    padding: 0;
    background: transparent;
    cursor: pointer;
    letter-spacing: 0;
    text-align: left;
}

.sidebar-title-button:hover {
    color: #1572e8;
}

.group-caret {
    font-size: 15px;
    line-height: 1;
    transition: transform .18s ease;
}

.group-caret.collapsed {
    transform: rotate(-90deg);
}

.nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-link {
    min-height: 45px;
    display: grid;
    grid-template-columns: 27px minmax(0, 1fr) 16px;
    align-items: center;
    gap: 12px;
    margin: 0 13px 7px;
    padding: 0 13px;
    border-radius: 5px;
    color: #8d9498;
    font-size: 14px;
    text-decoration: none;
    transition: background .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.nav-link:hover {
    transform: translateX(3px);
    color: #1572e8;
    background: #f1f6ff;
}

.nav-link.active {
    color: #fff;
    background: #1572e8;
    box-shadow: 0 8px 15px rgba(21, 114, 232, .28);
}

.nav-link svg {
    width: 21px;
    height: 21px;
}

.arrow {
    justify-self: end;
    font-size: 18px;
}

.badge {
    justify-self: end;
    min-width: 25px;
    height: 25px;
    display: inline-grid;
    place-items: center;
    border-radius: 999px;
    background: #31ce36;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
}

.main {
    min-height: 100vh;
    transition: transform .24s ease;
}

.topbar {
    height: 64px;
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 0 31px;
    background: #1572e8;
    color: #fff;
    box-shadow: 0 1px 8px rgba(0, 0, 0, .12);
}

.search {
    width: 384px;
    height: 42px;
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 0 16px;
    border-radius: 5px;
    position: relative;
    background: rgba(0, 0, 0, .12);
}

.search input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    color: #fff;
    background: transparent;
    font-size: 14px;
}

.search input::placeholder {
    color: rgba(255, 255, 255, .95);
}

.search-message {
    position: absolute;
    top: 48px;
    left: 0;
    z-index: 70;
    width: 100%;
    border: 1px solid #f1c4c4;
    border-radius: 5px;
    padding: 10px 12px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(31, 45, 61, .16);
    color: #d93025;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
}

.top-spacer {
    flex: 1;
}

.top-icon {
    width: 36px;
    height: 36px;
    border: 0;
    border-radius: 50%;
    display: grid;
    place-items: center;
    position: relative;
    background: transparent;
    color: #fff;
    cursor: pointer;
    transition: background .18s ease, transform .18s ease;
}

.top-icon:hover {
    background: rgba(255, 255, 255, .14);
    transform: translateY(-1px);
}

.notif-dot {
    position: absolute;
    right: 2px;
    top: 0;
    min-width: 17px;
    height: 17px;
    display: grid;
    place-items: center;
    border-radius: 999px;
    background: #31ce36;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
}

.top-avatar {
    width: 39px;
    height: 39px;
    border: 2px solid rgba(255, 255, 255, .65);
}

.profile-action {
    min-width: 0;
    display: inline-flex;
    align-items: center;
    gap: 9px;
    color: #fff;
    text-decoration: none;
}

.profile-action-text {
    max-width: 130px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 13px;
    font-weight: 600;
}

.logout-button {
    min-height: 36px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: 1px solid rgba(255, 255, 255, .35);
    border-radius: 5px;
    padding: 0 11px;
    background: rgba(0, 0, 0, .1);
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.logout-button:hover {
    background: rgba(0, 0, 0, .18);
}

.overlay {
    display: none;
}

.sidebar-collapsed .sidebar {
    width: 78px;
}

.sidebar-collapsed .brand {
    padding: 0;
}

.sidebar-collapsed .brand-link {
    justify-content: center;
}

.sidebar-collapsed .brand-text,
.sidebar-collapsed .profile > div,
.sidebar-collapsed .profile-caret,
.sidebar-collapsed .sidebar-title,
.sidebar-collapsed .nav-link span,
.sidebar-collapsed .group-caret {
    opacity: 0;
    pointer-events: none;
    transform: translateX(-8px);
}

.sidebar-collapsed .profile {
    grid-template-columns: 44px;
    justify-content: center;
    padding: 0 0 18px;
}

.sidebar-collapsed .nav-link {
    grid-template-columns: 27px;
    justify-content: center;
    margin: 0 13px 9px;
    padding: 0;
}

.sidebar-collapsed .nav-link:hover {
    transform: translateX(0) scale(1.04);
}

@media (max-width: 980px) {
    .atlantis {
        padding-left: 0;
    }

    .atlantis.sidebar-collapsed {
        padding-left: 0;
    }

    .sidebar {
        width: 258px;
        transform: translateX(-100%);
    }

    .atlantis.sidebar-open .sidebar {
        transform: translateX(0);
    }

    .sidebar-collapsed .sidebar {
        width: 258px;
    }

    .sidebar-collapsed .brand {
        padding: 0 31px;
    }

    .sidebar-collapsed .brand-link {
        justify-content: flex-start;
    }

    .sidebar-collapsed .brand-text,
    .sidebar-collapsed .profile > div,
    .sidebar-collapsed .profile-caret,
    .sidebar-collapsed .sidebar-title,
    .sidebar-collapsed .nav-link span,
    .sidebar-collapsed .group-caret {
        opacity: 1;
        pointer-events: auto;
        transform: none;
    }

    .sidebar-collapsed .profile {
        grid-template-columns: 44px minmax(0, 1fr) 16px;
        justify-content: stretch;
        padding: 0 10px 18px;
    }

    .sidebar-collapsed .nav-link {
        grid-template-columns: 27px minmax(0, 1fr) 16px;
        justify-content: stretch;
        margin: 0 13px 7px;
        padding: 0 13px;
    }

    .search {
        width: min(384px, 50vw);
    }

    .overlay {
        position: fixed;
        inset: 0;
        z-index: 25;
        background: rgba(33, 37, 41, .45);
    }

    .atlantis.sidebar-open .overlay {
        display: block;
    }
}

@media (max-width: 720px) {
    .topbar {
        padding: 0 14px;
        gap: 8px;
    }

    .search {
        display: none;
    }

    .top-icon {
        width: 32px;
    }

    .profile-action-text,
    .logout-button span {
        display: none;
    }

    .logout-button {
        width: 36px;
        justify-content: center;
        padding: 0;
    }
}
</style>
