<script setup>
import RoleLayout from '@/Layouts/RoleLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import ProfilePhotoForm from './Partials/ProfilePhotoForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <RoleLayout>
        <section class="profile-head">
            <div>
                <h1>Profile</h1>
                <p>Kelola informasi akun, password, dan keamanan pengguna.</p>
            </div>
        </section>

        <div class="profile-content">
            <div class="profile-grid">
                <aside class="profile-summary">
                    <ProfilePhotoForm />
                </aside>

                <div class="profile-forms">
                    <article class="profile-card">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </article>

                    <article class="profile-card">
                        <UpdatePasswordForm />
                    </article>

                    <article class="profile-card danger">
                        <DeleteUserForm />
                    </article>
                </div>
            </div>
        </div>
    </RoleLayout>
</template>

<style scoped>
.profile-head {
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

.profile-head h1 {
    margin: 0 0 10px;
    font-size: 24px;
    line-height: 1.15;
    font-weight: 700;
}

.profile-head p {
    margin: 0;
    color: rgba(255, 255, 255, .72);
    font-size: 14px;
}

.profile-content {
    padding: 0 31px 60px;
    margin-top: -28px;
}

.profile-grid {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    gap: 24px;
    align-items: start;
}

.profile-summary,
.profile-card {
    border-radius: 6px;
    background: #fff;
    border: 1px solid rgba(235, 236, 236, .9);
    box-shadow: 0 7px 18px rgba(31, 45, 61, .08);
    animation: riseIn .42s ease both;
}

.profile-summary {
    position: sticky;
    top: 86px;
    padding: 26px;
    text-align: center;
}

.profile-summary .avatar {
    width: 84px;
    height: 84px;
    margin: 0 auto 18px;
    border-radius: 50%;
    background:
        radial-gradient(circle at 50% 38%, #f4c7a1 0 15%, transparent 16%),
        radial-gradient(circle at 50% 78%, #2f3747 0 27%, transparent 28%),
        linear-gradient(#dfe8f2, #f6f7f9);
    box-shadow: 0 10px 24px rgba(31, 45, 61, .12);
}

.profile-summary h2 {
    margin: 0 0 8px;
    color: #2f3441;
    font-size: 18px;
    font-weight: 700;
}

.profile-summary p {
    margin: 0;
    color: #8d9498;
    font-size: 14px;
    line-height: 1.55;
}

.profile-forms {
    display: grid;
    gap: 20px;
}

.profile-card {
    padding: 26px;
}

.profile-card:nth-child(2) {
    animation-delay: .05s;
}

.profile-card:nth-child(3) {
    animation-delay: .1s;
}

.profile-card.danger {
    border-color: rgba(242, 89, 97, .22);
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

@media (max-width: 980px) {
    .profile-head {
        padding: 30px 18px 52px;
    }

    .profile-content {
        padding: 0 18px 40px;
    }

    .profile-grid {
        grid-template-columns: 1fr;
    }

    .profile-summary {
        position: static;
    }
}
</style>
