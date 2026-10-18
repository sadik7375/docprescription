<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { User, Mail, Phone, ShieldCheck, CheckCircle2 } from 'lucide-vue-next';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    phone: user.phone || '',
});
</script>

<template>
    <section class="space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-[#21262D]">
            <div class="space-y-1">
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <User class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                    <span>Personal & Account Information</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Update your account's display name, email address, and phone number.
                </p>
            </div>

            <!-- Role / Designation Pill -->
            <div v-if="user.staff_profile" class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 text-xs font-bold">
                <ShieldCheck class="w-3.5 h-3.5" />
                <span>{{ user.staff_profile.designation || 'Staff Member' }}</span>
            </div>
        </div>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="space-y-4 max-w-xl"
        >
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Full Name *
                </label>
                <div class="relative">
                    <User class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="form.name"
                        required
                        placeholder="e.g. John Doe"
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                    />
                </div>
                <p v-if="form.errors.name" class="text-[10px] text-rose-500 font-medium">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Email Address *
                </label>
                <div class="relative">
                    <Mail class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="email"
                        v-model="form.email"
                        required
                        placeholder="e.g. user@example.com"
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                    />
                </div>
                <p v-if="form.errors.email" class="text-[10px] text-rose-500 font-medium">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                    Phone / Mobile Number
                </label>
                <div class="relative">
                    <Phone class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="form.phone"
                        placeholder="e.g. +1 (555) 234-5678"
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                    />
                </div>
                <p v-if="form.errors.phone" class="text-[10px] text-rose-500 font-medium">{{ form.errors.phone }}</p>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-5 py-2.5 text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white rounded-xl shadow-xs transition cursor-pointer disabled:opacity-50 flex items-center gap-2"
                >
                    <CheckCircle2 class="w-4 h-4" />
                    <span>{{ form.processing ? 'Saving...' : 'Save Profile Changes' }}</span>
                </button>

                <p
                    v-if="form.recentlySuccessful"
                    class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1"
                >
                    <CheckCircle2 class="w-3.5 h-3.5" />
                    <span>Profile saved successfully.</span>
                </p>
            </div>
        </form>
    </section>
</template>
