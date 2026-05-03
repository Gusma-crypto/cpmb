<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const roles = computed(() => page.props.auth?.roles || []);
const userRole = computed(() => page.props.auth?.user?.role);
const isStudent = computed(() => roles.value.includes('student') || userRole.value === 'student');
const Layout = computed(() => (isStudent.value ? StudentLayout : AdminLayout));
</script>

<template>
    <component :is="Layout">
        <slot />
    </component>
</template>
