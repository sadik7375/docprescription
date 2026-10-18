<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ShieldCheck,
    Users,
    UserPlus,
    Clock,
    CheckCircle2,
    AlertTriangle,
    XCircle,
    FileText,
    Building2,
    Calendar,
    ArrowUpRight,
    Search,
    Sparkles,
    SlidersHorizontal,
    TrendingUp,
    Zap,
    ExternalLink,
    Crown
} from 'lucide-vue-next';

const props = defineProps({
    stats: Object,
    plan_stats: Object,
    recent_doctors: Array,
    expiring_trials: Array,
});

const quickExtendTrial = (doctorId, days = 14) => {
    router.patch(route('admin.doctors.update-subscription', doctorId), {
        extend_days: days,
        subscription_plan: 'free_trial',
        subscription_status: 'trialing',
        is_active: true,
    }, {
        preserveScroll: true
    });
};

const planColors = {
    free_trial: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
    solo: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
    pro: 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800',
    enterprise: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
    lifetime: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
};

const statusColors = {
    trialing: 'bg-amber-500/10 text-amber-600 border-amber-500/20 dark:text-amber-400',
    active: 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20 dark:text-emerald-400',
    expired: 'bg-rose-500/10 text-rose-600 border-rose-500/20 dark:text-rose-400',
    suspended: 'bg-slate-500/10 text-slate-600 border-slate-500/20 dark:text-slate-400',
    cancelled: 'bg-slate-500/10 text-slate-600 border-slate-500/20 dark:text-slate-400',
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const getDaysLeft = (dateStr) => {
    if (!dateStr) return 0;
    const diff = new Date(dateStr) - new Date();
    return Math.max(0, Math.ceil(diff / (1000 * 60 * 60 * 24)));
};
</script>

<template>
    <Head title="Super Admin Dashboard - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                            <ShieldCheck class="w-6 h-6" />
                        </span>
                        <div>
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                Super Admin Control Center
                                <span class="px-2 py-0.5 rounded-md text-[10px] uppercase font-black tracking-wider bg-teal-500/20 text-teal-600 dark:text-teal-400 border border-teal-500/30">
                                    SaaS Master
                                </span>
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Global platform metrics, doctor practice accounts, free trial durations, and subscription statuses.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <Link
                        :href="route('admin.ai-settings.index')"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-[#30363D] text-slate-800 dark:text-slate-200 text-xs font-bold transition border border-slate-200/80 dark:border-[#30363D]"
                    >
                        <Sparkles class="w-4 h-4 text-amber-500" />
                        <span>AI & API Settings</span>
                    </Link>
                    <Link
                        :href="route('admin.doctors.index')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition"
                    >
                        <Users class="w-4 h-4" />
                        <span>Manage All Doctors & Trials</span>
                    </Link>
                </div>
            </div>

            <!-- Top SaaS Metrics Overview Grid -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- Metric 1: Total Doctors -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Registered Doctors</span>
                        <div class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                            <Users class="w-4 h-4" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white font-mono">
                            {{ stats.total_doctors }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            <strong class="text-teal-600 dark:text-teal-400">{{ stats.active_subscribers + stats.trialing_doctors }}</strong> active accounts
                        </div>
                    </div>
                </div>

                <!-- Metric 2: In-Trial Doctors -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Active Free Trials</span>
                        <div class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                            <Clock class="w-4 h-4" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono">
                            {{ stats.trialing_doctors }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            14-day trial period
                        </div>
                    </div>
                </div>

                <!-- Metric 3: Paid Active Subscriptions -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Paid Subscribers</span>
                        <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                            <Crown class="w-4 h-4" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono">
                            {{ stats.active_subscribers }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Solo, Pro & Enterprise
                        </div>
                    </div>
                </div>

                <!-- Metric 4: Expired / Suspended -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Expired / Suspended</span>
                        <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400">
                            <AlertTriangle class="w-4 h-4" />
                        </div>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-rose-600 dark:text-rose-400 font-mono">
                            {{ stats.expired_accounts + stats.suspended_accounts }}
                        </div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Requires plan upgrade
                        </div>
                    </div>
                </div>

            </div>

            <!-- Platform Utilization Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-white dark:bg-[#0A0D36]/40 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400">
                            <FileText class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Prescriptions (SaaS)</div>
                            <div class="text-lg font-bold text-slate-900 dark:text-white font-mono">{{ stats.total_prescriptions }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#0A0D36]/40 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                            <Users class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Registered Patients</div>
                            <div class="text-lg font-bold text-slate-900 dark:text-white font-mono">{{ stats.total_patients }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#0A0D36]/40 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-lg bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400">
                            <Building2 class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium">Total Active Chambers</div>
                            <div class="text-lg font-bold text-slate-900 dark:text-white font-mono">{{ stats.total_chambers }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Split: Expiring Trials Alert & Plan Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                <!-- Left: Expiring Trials Alert Card (7 cols) -->
                <div class="lg:col-span-7 rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 p-6 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <Clock class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                Urgent: Expiring Trials
                            </h2>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Expiring in &le; 3 Days</span>
                    </div>

                    <div v-if="expiring_trials.length === 0" class="text-center py-8 text-slate-500 dark:text-slate-400 text-xs">
                        <CheckCircle2 class="w-8 h-8 text-emerald-500 mx-auto mb-2" />
                        No trials expiring in the next 3 days. All doctor accounts are in healthy state.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="doctor in expiring_trials"
                            :key="doctor.id"
                            class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ doctor.name }}</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded font-mono font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        {{ getDaysLeft(doctor.trial_ends_at) }} Days Left
                                    </span>
                                </div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    {{ doctor.email }} • Ends: {{ formatDate(doctor.trial_ends_at) }}
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    @click="quickExtendTrial(doctor.id, 14)"
                                    class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-xs transition"
                                >
                                    +14 Days Trial
                                </button>
                                <Link
                                    :href="route('admin.doctors.index', { search: doctor.email })"
                                    class="p-1.5 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-teal-600 transition"
                                >
                                    <ExternalLink class="w-4 h-4" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Plan Breakdown (5 cols) -->
                <div class="lg:col-span-5 rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 p-6 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                            <Sparkles class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                            Plan Distribution
                        </h2>
                    </div>

                    <div class="space-y-3 text-xs">
                        
                        <!-- Free Trial -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Free Trial</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ plan_stats.free_trial }} Doctors</span>
                        </div>

                        <!-- Solo -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Solo Physician ($19/mo)</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ plan_stats.solo }} Doctors</span>
                        </div>

                        <!-- Pro -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Pro Practice ($39/mo)</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ plan_stats.pro }} Doctors</span>
                        </div>

                        <!-- Enterprise -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Hospital / Enterprise</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ plan_stats.enterprise }} Doctors</span>
                        </div>

                        <!-- Lifetime -->
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Lifetime CodeCanyon</span>
                            </div>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">{{ plan_stats.lifetime }} Doctors</span>
                        </div>

                    </div>
                </div>

            </div>

            <!-- Recent Registered Doctors Table -->
            <div class="rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 p-6 space-y-4 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                        <Users class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                        Recent Registered Doctors
                    </h2>
                    <Link
                        :href="route('admin.doctors.index')"
                        class="text-xs font-bold text-teal-600 dark:text-teal-400 hover:underline flex items-center gap-1"
                    >
                        <span>View All {{ stats.total_doctors }} Doctors</span>
                        <ArrowUpRight class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">
                                <th class="py-3 px-3">Doctor & Identity</th>
                                <th class="py-3 px-3">Specialty / Reg No</th>
                                <th class="py-3 px-3">Plan</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3">Trial / Exp. Date</th>
                                <th class="py-3 px-3 text-center">Chambers</th>
                                <th class="py-3 px-3 text-center">Rx Count</th>
                                <th class="py-3 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="doc in recent_doctors" :key="doc.id" class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ doc.name }}</div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ doc.email }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-slate-800 dark:text-slate-200 font-medium">{{ doc.doctor_profile?.specialty || 'General Physician' }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">Reg: {{ doc.doctor_profile?.bmdc_reg_no || 'N/A' }}</div>
                                </td>
                                <td class="py-3 px-3">
                                    <span :class="['px-2 py-0.5 rounded text-[11px] font-bold border uppercase tracking-wider', planColors[doc.subscription_plan] || '']">
                                        {{ doc.subscription_plan?.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span :class="['px-2 py-0.5 rounded text-[11px] font-bold border capitalize', statusColors[doc.subscription_status] || '']">
                                        {{ doc.subscription_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    <div v-if="doc.subscription_status === 'trialing'" class="text-amber-600 dark:text-amber-400 font-semibold">
                                        {{ getDaysLeft(doc.trial_ends_at) }}d left ({{ formatDate(doc.trial_ends_at) }})
                                    </div>
                                    <div v-else-if="doc.subscription_ends_at" class="text-slate-700 dark:text-slate-300">
                                        {{ formatDate(doc.subscription_ends_at) }}
                                    </div>
                                    <div v-else class="text-slate-400">
                                        Unlimited
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-slate-800 dark:text-slate-200">
                                    {{ doc.chambers_count }}
                                </td>
                                <td class="py-3 px-3 text-center font-bold text-teal-600 dark:text-teal-400 font-mono">
                                    {{ doc.prescriptions_count }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <Link
                                        :href="route('admin.doctors.index', { search: doc.email })"
                                        class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-teal-50 dark:hover:bg-teal-950 text-slate-700 dark:text-slate-300 hover:text-teal-600 font-bold transition"
                                    >
                                        Manage
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
