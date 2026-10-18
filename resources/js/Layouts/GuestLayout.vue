<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { HeartPulse, ShieldCheck, Stethoscope, Users, CalendarCheck, FileText } from '@lucide/vue';

const page = usePage();

const brandName = computed(() => {
    const b1 = page.props.appSettings?.brand_name || 'Doc Helper';
    const b2 = page.props.appSettings?.brand_name_highlight || 'AI';
    return `${b1} ${b2}`.trim();
});

const brandTagline = computed(() => page.props.appSettings?.brand_tagline || 'Next-Gen Medical Practice & Rx Platform');
const brandLogoUrl = computed(() => page.props.appSettings?.brand_logo_url || null);
const footerCopyright = computed(() => page.props.appSettings?.footer_copyright || `Doc Helper AI SaaS`);
</script>

<template>
    <div class="flex min-h-screen bg-slate-50 dark:bg-slate-900 font-sans text-slate-800 dark:text-slate-100">
        <!-- Left Hero Section (SaaS Branding) -->
        <div class="hidden lg:flex lg:w-1/2 flex-col justify-between bg-gradient-to-br from-indigo-900 via-slate-900 to-teal-900 p-12 text-white relative overflow-hidden">
            <!-- Decorative Background Orbs -->
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

            <!-- Brand Header -->
            <div class="relative z-10">
                <Link href="/" class="flex items-center gap-3 group">
                    <img v-if="brandLogoUrl" :src="brandLogoUrl" :alt="brandName" class="h-12 w-auto max-w-[180px] object-contain rounded-xl shadow-md group-hover:scale-105 transition-transform duration-300" />
                    <div v-else class="h-12 w-12 rounded-xl bg-gradient-to-tr from-teal-400 to-indigo-500 flex items-center justify-center shadow-lg shadow-teal-500/20 group-hover:scale-105 transition-transform duration-300">
                        <HeartPulse class="h-7 w-7 text-white animate-pulse" />
                    </div>
                    <div>
                        <span class="text-2xl font-bold tracking-tight text-white">{{ brandName }}</span>
                        <span class="block text-xs text-teal-300 font-medium tracking-wide">{{ brandTagline }}</span>
                    </div>
                </Link>
            </div>

            <!-- Value Proposition Features -->
            <div class="relative z-10 my-auto py-8 space-y-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/20 text-teal-300 text-xs font-semibold tracking-wide uppercase">
                    <ShieldCheck class="w-4 h-4" /> Global Medical SaaS Platform
                </div>

                <h1 class="text-4xl font-extrabold tracking-tight leading-tight">
                    Intelligent Practice & <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-300 to-indigo-300">AI Clinical Assistant</span>
                </h1>
                
                <p class="text-slate-300 text-base leading-relaxed max-w-lg">
                    Streamline multi-chamber appointments, patient records, clinical templates, and lightning-fast prescription generation.
                </p>

                <div class="grid grid-cols-2 gap-4 pt-4">
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                        <div class="p-2 rounded-lg bg-teal-500/20 text-teal-300 shrink-0">
                            <Stethoscope class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Multi-Chamber</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Manage schedules & consultation fees</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                        <div class="p-2 rounded-lg bg-indigo-500/20 text-indigo-300 shrink-0">
                            <FileText class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Prescription Engine</h4>
                            <p class="text-xs text-slate-400 mt-0.5">A4, A5 & Pad overlay printing</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                        <div class="p-2 rounded-lg bg-blue-500/20 text-blue-300 shrink-0">
                            <CalendarCheck class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Live Queue</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Serial numbers & patient check-in</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                        <div class="p-2 rounded-lg bg-purple-500/20 text-purple-300 shrink-0">
                            <Users class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-white">Patient Portal</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Self-service history & bookings</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Badge -->
            <div class="relative z-10 flex items-center justify-between text-xs text-slate-400 pt-4 border-t border-white/10">
                <span>© {{ new Date().getFullYear() }} {{ footerCopyright }}</span>
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Global Edition Ready</span>
            </div>
        </div>

        <!-- Right Form Container Section -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12">
            <!-- Mobile Brand Header -->
            <div class="lg:hidden mb-8 text-center">
                <Link href="/" class="inline-flex items-center gap-3">
                    <img v-if="brandLogoUrl" :src="brandLogoUrl" :alt="brandName" class="h-10 w-auto max-w-[140px] object-contain rounded-xl shadow-md" />
                    <div v-else class="h-10 w-10 rounded-xl bg-gradient-to-tr from-teal-500 to-indigo-600 flex items-center justify-center text-white shadow-md">
                        <HeartPulse class="h-6 w-6" />
                    </div>
                    <span class="text-xl font-bold text-slate-900 dark:text-white">{{ brandName }}</span>
                </Link>
            </div>

            <div class="w-full max-w-md">
                <slot />
            </div>
        </div>
    </div>
</template>
