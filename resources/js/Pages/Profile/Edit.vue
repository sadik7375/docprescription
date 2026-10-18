<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateDoctorProfileForm from './Partials/UpdateDoctorProfileForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { UserCircle2, User, Shield, Lock, Trash2, ArrowLeft, LogOut, ShieldCheck } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    doctorProfile: {
        type: Object,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
</script>

<template>
    <Head :title="user.role === 'doctor' ? 'Doctor Profile & Credentials' : (user.role === 'staff' ? 'Staff Account & Settings' : 'Account Settings')" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-5xl mx-auto pb-12">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('dashboard')"
                        class="p-2 rounded-xl bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-[#30363D] transition-colors text-slate-700 dark:text-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <UserCircle2 v-if="user.role === 'doctor'" class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                            <User v-else class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                            <span>{{ user.role === 'doctor' ? 'Doctor Profile & Credentials' : (user.role === 'staff' ? 'Staff Profile & Security' : 'Administrator Account Settings') }}</span>
                        </h1>
                        <p class="text-[11px] text-slate-400">
                            {{ user.role === 'doctor' ? 'Configure your doctor title, registration number, degrees, and prescription pad credentials.' : 'Manage your login credentials, personal details, and account password.' }}
                        </p>
                    </div>
                </div>

                <!-- Profile Header Quick Action: Log Out -->
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-xs font-bold transition-colors cursor-pointer border border-rose-200/60 dark:border-rose-900/50"
                    >
                        <LogOut class="w-3.5 h-3.5" />
                        <span>Log Out</span>
                    </Link>
                </div>
            </div>

            <!-- Doctor Credentials Form Card (Doctor Only) -->
            <div v-if="user.role === 'doctor'" class="bg-white dark:bg-[#161B22] p-6 sm:p-8 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <UpdateDoctorProfileForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    :doctor-profile="doctorProfile"
                />
            </div>

            <!-- General Profile Information Form Card (Staff & Admin Only) -->
            <div v-else class="bg-white dark:bg-[#161B22] p-6 sm:p-8 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <!-- Security & Password Card -->
            <div class="bg-white dark:bg-[#161B22] p-6 sm:p-8 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-6">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-[#21262D] pb-3">
                    <Lock class="w-4 h-4 text-teal-600" />
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                        Update Password & Security
                    </h2>
                </div>
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <!-- Danger Zone: Delete Account (Doctor Only) -->
            <div v-if="user.role === 'doctor'" class="bg-white dark:bg-[#161B22] p-6 sm:p-8 rounded-2xl border border-rose-200/60 dark:border-rose-900/40 shadow-xs space-y-6">
                <div class="flex items-center gap-2 border-b border-rose-100 dark:border-rose-950 pb-3">
                    <Trash2 class="w-4 h-4 text-rose-600" />
                    <h2 class="text-sm font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider">
                        Danger Zone
                    </h2>
                </div>
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

