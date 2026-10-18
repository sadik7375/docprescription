<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    LifeBuoy,
    Mail,
    Phone,
    MessageCircle,
    Clock,
    MapPin,
    Save,
    CheckCircle2,
    ShieldCheck
} from 'lucide-vue-next';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    support_email: props.settings.support_email || 'contact@cannyapps.com',
    support_phone: props.settings.support_phone || '+1 (800) 555-0199',
    support_whatsapp: props.settings.support_whatsapp || '+18005550199',
    support_hours: props.settings.support_hours || 'Monday - Friday: 9:00 AM - 8:00 PM EST',
    support_address: props.settings.support_address || '100 Medical Plaza, Suite 400, San Francisco, CA 94143',
});

const saveSettings = () => {
    form.post(route('admin.support-settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Support Channel Settings - Super Admin" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    Support Channel & Helpline Settings
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Configure your official helpline phone numbers, support email, WhatsApp number, and working hours displayed to doctors and landing page visitors.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Settings Form -->
                <div class="lg:col-span-2 bg-white dark:bg-[#161B22] p-6 sm:p-8 rounded-3xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <form @submit.prevent="saveSettings" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Official Support Email Address
                            </label>
                            <div class="relative">
                                <input
                                    type="email"
                                    v-model="form.support_email"
                                    required
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-mono font-bold focus:border-teal-500 focus:outline-none"
                                />
                                <Mail class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                            </div>
                            <p v-if="form.errors.support_email" class="text-xs text-rose-500 mt-1">{{ form.errors.support_email }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Support Helpline / Phone Number
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        v-model="form.support_phone"
                                        required
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-mono font-bold focus:border-teal-500 focus:outline-none"
                                    />
                                    <Phone class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                                </div>
                                <p v-if="form.errors.support_phone" class="text-xs text-rose-500 mt-1">{{ form.errors.support_phone }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    WhatsApp Helpline Number
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        v-model="form.support_whatsapp"
                                        placeholder="e.g. +18005550199"
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-mono font-bold focus:border-teal-500 focus:outline-none"
                                    />
                                    <MessageCircle class="w-4 h-4 text-emerald-500 absolute left-3.5 top-3" />
                                </div>
                                <p v-if="form.errors.support_whatsapp" class="text-xs text-rose-500 mt-1">{{ form.errors.support_whatsapp }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Support & Office Hours
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="form.support_hours"
                                    required
                                    placeholder="e.g. Saturday - Thursday: 9:00 AM - 10:00 PM"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-semibold focus:border-teal-500 focus:outline-none"
                                />
                                <Clock class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                            </div>
                            <p v-if="form.errors.support_hours" class="text-xs text-rose-500 mt-1">{{ form.errors.support_hours }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Physical Office / Headquarters Address
                            </label>
                            <div class="relative">
                                <input
                                    type="text"
                                    v-model="form.support_address"
                                    placeholder="e.g. 100 Medical Plaza, Suite 400, San Francisco, CA 94143"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs focus:border-teal-500 focus:outline-none"
                                />
                                <MapPin class="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 px-7 py-3 rounded-2xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition cursor-pointer"
                            >
                                <Save class="w-4 h-4" />
                                <span>{{ form.processing ? 'Saving...' : 'Save Support Channels' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Live Preview Card -->
                <div class="space-y-4">
                    <div class="bg-white dark:bg-[#161B22] p-6 rounded-3xl border border-slate-200/80 dark:border-[#21262D] space-y-4 shadow-xs">
                        <div class="flex items-center gap-2 pb-3 border-b border-slate-100 dark:border-[#21262D]">
                            <ShieldCheck class="w-4 h-4 text-teal-600" />
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Live Doctor Card Preview</h3>
                        </div>

                        <div class="p-4 rounded-2xl bg-gradient-to-br from-teal-900 via-slate-900 to-[#0A0D36] text-white space-y-3 shadow-md">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-teal-300">Support Email:</span>
                                <p class="text-xs font-bold truncate">{{ form.support_email }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-teal-300">Helpline:</span>
                                <p class="text-xs font-bold truncate">{{ form.support_phone }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">WhatsApp:</span>
                                <p class="text-xs font-bold truncate">{{ form.support_whatsapp }}</p>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-teal-300">Hours:</span>
                                <p class="text-[11px] text-slate-300">{{ form.support_hours }}</p>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            These details appear in the doctor portal, landing page contact footer, and system support tickets.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>