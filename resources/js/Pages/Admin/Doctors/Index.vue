<script setup>
import { ref, reactive } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import {
    Users,
    UserPlus,
    Search,
    Filter,
    Clock,
    CheckCircle2,
    AlertTriangle,
    XCircle,
    Building2,
    FileText,
    MoreVertical,
    Edit,
    Trash2,
    LogIn,
    SlidersHorizontal,
    Plus,
    X,
    Sparkles,
    Calendar,
    Crown,
    ShieldCheck,
    Check
} from 'lucide-vue-next';

const props = defineProps({
    doctors: Object,
    filters: Object,
    stats: Object,
});

// Search and filter reactive states
const search = ref(props.filters.search || '');
const selectedPlan = ref(props.filters.plan || 'all');
const selectedStatus = ref(props.filters.status || 'all');

const applyFilters = () => {
    router.get(route('admin.doctors.index'), {
        search: search.value,
        plan: selectedPlan.value,
        status: selectedStatus.value,
    }, {
        preserveState: true,
        replace: true
    });
};

const resetFilters = () => {
    search.value = '';
    selectedPlan.value = 'all';
    selectedStatus.value = 'all';
    applyFilters();
};

// Modal: Create Doctor
const isCreateModalOpen = ref(false);
const createForm = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    title: 'Dr.',
    specialty: 'General Physician',
    bmdc_reg_no: '',
    subscription_plan: 'free_trial',
    subscription_status: 'trialing',
    trial_days: 14,
    admin_notes: '',
});

const submitCreateDoctor = () => {
    createForm.post(route('admin.doctors.store'), {
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        }
    });
};

// Modal: Manage Subscription & Trial
const isManageModalOpen = ref(false);
const selectedDoctor = ref(null);
const manageForm = useForm({
    subscription_plan: 'free_trial',
    subscription_status: 'trialing',
    trial_ends_at: '',
    subscription_ends_at: '',
    extend_days: null,
    is_active: true,
    admin_notes: '',
});

const openManageModal = (doctor) => {
    selectedDoctor.value = doctor;
    manageForm.subscription_plan = doctor.subscription_plan || 'free_trial';
    manageForm.subscription_status = doctor.subscription_status || 'trialing';
    manageForm.trial_ends_at = doctor.trial_ends_at ? doctor.trial_ends_at.substring(0, 10) : '';
    manageForm.subscription_ends_at = doctor.subscription_ends_at ? doctor.subscription_ends_at.substring(0, 10) : '';
    manageForm.extend_days = null;
    manageForm.is_active = Boolean(doctor.is_active);
    manageForm.admin_notes = doctor.admin_notes || '';
    isManageModalOpen.value = true;
};

const submitUpdateSubscription = () => {
    if (!selectedDoctor.value) return;
    manageForm.patch(route('admin.doctors.update-subscription', selectedDoctor.value.id), {
        onSuccess: () => {
            isManageModalOpen.value = false;
        }
    });
};

const extendTrialBy = (days) => {
    manageForm.extend_days = days;
    manageForm.subscription_status = 'trialing';
    submitUpdateSubscription();
};

const toggleAccountActive = (doctor) => {
    router.patch(route('admin.doctors.toggle-status', doctor.id), {}, {
        preserveScroll: true
    });
};

const impersonateDoctor = (doctor) => {
    if (confirm(`Are you sure you want to log in as ${doctor.name}? You can return to Super Admin anytime.`)) {
        router.post(route('admin.doctors.impersonate', doctor.id));
    }
};

const deleteDoctor = (doctor) => {
    if (confirm(`Are you sure you want to permanently delete ${doctor.name}? All doctor prescriptions and data will be removed.`)) {
        router.delete(route('admin.doctors.destroy', doctor.id));
    }
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
    <Head title="Doctor & Trial Management - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            
            <!-- Header & Create Action -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Users class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        Doctor & Trial Management
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage doctor accounts, trial expiration dates, subscription tiers, and administrative access.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="isCreateModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm transition"
                    >
                        <UserPlus class="w-4 h-4" />
                        <span>Add New Doctor</span>
                    </button>
                </div>
            </div>

            <!-- Stats Summary Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-4 rounded-xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between shadow-xs">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Total Doctors</span>
                        <div class="text-xl font-black text-slate-900 dark:text-white font-mono mt-0.5">{{ stats.total }}</div>
                    </div>
                    <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400">
                        <Users class="w-4 h-4" />
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between shadow-xs">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Active Trials</span>
                        <div class="text-xl font-black text-amber-600 dark:text-amber-400 font-mono mt-0.5">{{ stats.trialing }}</div>
                    </div>
                    <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                        <Clock class="w-4 h-4" />
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between shadow-xs">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Paid Subscribers</span>
                        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5">{{ stats.active }}</div>
                    </div>
                    <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                        <CheckCircle2 class="w-4 h-4" />
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between shadow-xs">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Expired Accounts</span>
                        <div class="text-xl font-black text-rose-600 dark:text-rose-400 font-mono mt-0.5">{{ stats.expired }}</div>
                    </div>
                    <div class="p-2 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400">
                        <AlertTriangle class="w-4 h-4" />
                    </div>
                </div>
            </div>

            <!-- Search & Filters Toolbar -->
            <div class="p-4 rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="relative flex-1">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Search doctor by name, email, phone, specialty, reg no..."
                        class="w-full pl-10 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-1 focus:ring-teal-500 focus:border-teal-500 transition"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Plan Filter -->
                    <select
                        v-model="selectedPlan"
                        @change="applyFilters"
                        class="py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 focus:ring-1 focus:ring-teal-500"
                    >
                        <option value="all">All Plans</option>
                        <option value="free_trial">Free Trial</option>
                        <option value="solo">Solo Physician</option>
                        <option value="pro">Pro Practice</option>
                        <option value="enterprise">Hospital / Enterprise</option>
                        <option value="lifetime">Lifetime</option>
                    </select>

                    <!-- Status Filter -->
                    <select
                        v-model="selectedStatus"
                        @change="applyFilters"
                        class="py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 focus:ring-1 focus:ring-teal-500"
                    >
                        <option value="all">All Statuses</option>
                        <option value="trialing">Trialing</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="suspended">Suspended</option>
                    </select>

                    <button
                        v-if="search || selectedPlan !== 'all' || selectedStatus !== 'all'"
                        @click="resetFilters"
                        class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 dark:hover:text-white transition"
                        title="Reset filters"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Doctor Listing Table -->
            <div class="rounded-2xl bg-white dark:bg-[#0A0D36]/60 border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">
                                <th class="py-3 px-4">Doctor & Account</th>
                                <th class="py-3 px-3">Credentials</th>
                                <th class="py-3 px-3">Plan</th>
                                <th class="py-3 px-3">Status</th>
                                <th class="py-3 px-3">Trial / Exp. Date</th>
                                <th class="py-3 px-3 text-center">Chambers</th>
                                <th class="py-3 px-3 text-center">Prescriptions</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-if="doctors.data.length === 0">
                                <td colspan="8" class="text-center py-10 text-slate-400 text-xs">
                                    No doctors found matching the filter criteria.
                                </td>
                            </tr>
                            <tr
                                v-for="doc in doctors.data"
                                :key="doc.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition"
                            >
                                <!-- Doctor identity -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 font-bold flex items-center justify-center shrink-0 text-sm">
                                            {{ doc.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                                <span>{{ doc.name }}</span>
                                                <span v-if="!doc.is_active" class="px-1.5 py-0.2 text-[9px] font-bold rounded bg-rose-100 text-rose-700">Deactivated</span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ doc.email }}</div>
                                            <div v-if="doc.phone" class="text-[10px] text-slate-400 font-mono">{{ doc.phone }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Credentials -->
                                <td class="py-3.5 px-3">
                                    <div class="text-slate-800 dark:text-slate-200 font-semibold">
                                        {{ doc.doctor_profile?.specialty || 'General Practice' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-mono">
                                        Reg No: <span class="font-bold text-slate-700 dark:text-slate-300">{{ doc.doctor_profile?.bmdc_reg_no || 'N/A' }}</span>
                                    </div>
                                </td>

                                <!-- Plan -->
                                <td class="py-3.5 px-3">
                                    <span :class="['px-2.5 py-1 rounded-md text-[11px] font-bold border uppercase tracking-wider', planColors[doc.subscription_plan] || '']">
                                        {{ doc.subscription_plan?.replace('_', ' ') }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-3">
                                    <span :class="['px-2.5 py-1 rounded-md text-[11px] font-bold border capitalize', statusColors[doc.subscription_status] || '']">
                                        {{ doc.subscription_status }}
                                    </span>
                                </td>

                                <!-- Trial / Expiration -->
                                <td class="py-3.5 px-3 font-mono">
                                    <div v-if="doc.subscription_status === 'trialing'" class="text-amber-600 dark:text-amber-400 font-bold">
                                        {{ getDaysLeft(doc.trial_ends_at) }} Days Left
                                        <span class="block text-[10px] text-slate-400 font-normal">Ends: {{ formatDate(doc.trial_ends_at) }}</span>
                                    </div>
                                    <div v-else-if="doc.subscription_ends_at" class="text-slate-700 dark:text-slate-300 font-semibold">
                                        {{ formatDate(doc.subscription_ends_at) }}
                                    </div>
                                    <div v-else class="text-slate-400">
                                        Unlimited Lifetime
                                    </div>
                                </td>

                                <!-- Chambers -->
                                <td class="py-3.5 px-3 text-center font-bold text-slate-800 dark:text-slate-200 font-mono">
                                    {{ doc.chambers_count }}
                                </td>

                                <!-- Prescriptions -->
                                <td class="py-3.5 px-3 text-center font-bold text-teal-600 dark:text-teal-400 font-mono">
                                    {{ doc.prescriptions_count }}
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Manage Subscription Button -->
                                        <button
                                            @click="openManageModal(doc)"
                                            class="px-2.5 py-1.5 rounded-lg bg-teal-50 dark:bg-teal-950/50 hover:bg-teal-100 text-teal-700 dark:text-teal-300 font-bold text-xs transition border border-teal-200 dark:border-teal-800"
                                            title="Manage Subscription & Trial"
                                        >
                                            Manage Plan
                                        </button>

                                        <!-- Impersonate Button -->
                                        <button
                                            @click="impersonateDoctor(doc)"
                                            class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 hover:text-teal-600 transition"
                                            title="Log in as Doctor (Support)"
                                        >
                                            <LogIn class="w-4 h-4" />
                                        </button>

                                        <!-- Delete Button -->
                                        <button
                                            @click="deleteDoctor(doc)"
                                            class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 hover:text-rose-700 transition"
                                            title="Delete Doctor Account"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination footer if needed -->
                <div v-if="doctors.links && doctors.links.length > 3" class="p-4 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <div>
                        Showing {{ doctors.from }} to {{ doctors.to }} of {{ doctors.total }} doctors
                    </div>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in doctors.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-lg font-bold transition',
                                link.active
                                    ? 'bg-teal-600 text-white'
                                    : link.url
                                        ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'
                                        : 'text-slate-400 cursor-not-allowed opacity-50'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <!-- MODAL: Manage Trial & Subscription -->
            <div v-if="isManageModalOpen && selectedDoctor" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
                <div class="w-full max-w-xl rounded-3xl bg-white dark:bg-[#0A0D36] border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-6 sm:p-8 space-y-6">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <Crown class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                Manage Subscription & Trial
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ selectedDoctor.name }} ({{ selectedDoctor.email }})
                            </p>
                        </div>
                        <button @click="isManageModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Quick Trial Extensions -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                            ⚡ Quick Extend Free Trial
                        </label>
                        <div class="grid grid-cols-4 gap-2">
                            <button
                                @click="extendTrialBy(7)"
                                type="button"
                                class="py-2 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs font-bold border border-amber-200 dark:border-amber-800 transition"
                            >
                                +7 Days
                            </button>
                            <button
                                @click="extendTrialBy(14)"
                                type="button"
                                class="py-2 rounded-xl bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/40 text-teal-800 dark:text-teal-300 text-xs font-bold border border-teal-200 dark:border-teal-800 transition"
                            >
                                +14 Days
                            </button>
                            <button
                                @click="extendTrialBy(30)"
                                type="button"
                                class="py-2 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 text-xs font-bold border border-blue-200 dark:border-blue-800 transition"
                            >
                                +30 Days
                            </button>
                            <button
                                @click="extendTrialBy(60)"
                                type="button"
                                class="py-2 rounded-xl bg-purple-50 hover:bg-purple-100 dark:bg-purple-950/40 text-purple-800 dark:text-purple-300 text-xs font-bold border border-purple-200 dark:border-purple-800 transition"
                            >
                                +60 Days
                            </button>
                        </div>
                    </div>

                    <!-- Form Controls -->
                    <form @submit.prevent="submitUpdateSubscription" class="space-y-4">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Plan -->
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Subscription Plan</label>
                                <select
                                    v-model="manageForm.subscription_plan"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                >
                                    <option value="free_trial">Free Trial</option>
                                    <option value="solo">Solo Physician ($19/mo)</option>
                                    <option value="pro">Pro Practice ($39/mo)</option>
                                    <option value="enterprise">Hospital / Enterprise</option>
                                    <option value="lifetime">Lifetime License</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Account Status</label>
                                <select
                                    v-model="manageForm.subscription_status"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                >
                                    <option value="trialing">Trialing (Active Trial)</option>
                                    <option value="active">Active (Paid / Allowed)</option>
                                    <option value="expired">Expired (Requires Renewal)</option>
                                    <option value="suspended">Suspended (Blocked)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Trial Ends Date -->
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Trial Expiration Date</label>
                                <input
                                    v-model="manageForm.trial_ends_at"
                                    type="date"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>

                            <!-- Subscription Ends Date -->
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Subscription Renewal Date</label>
                                <input
                                    v-model="manageForm.subscription_ends_at"
                                    type="date"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>
                        </div>

                        <!-- Active Switch -->
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <div>
                                <div class="text-xs font-bold text-slate-900 dark:text-white">Account Enabled</div>
                                <div class="text-[11px] text-slate-500">Allow doctor to log in and create prescriptions</div>
                            </div>
                            <input
                                v-model="manageForm.is_active"
                                type="checkbox"
                                class="w-4 h-4 rounded text-teal-600 focus:ring-teal-500"
                            />
                        </div>

                        <!-- Admin Notes -->
                        <div>
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Admin Internal Notes</label>
                            <textarea
                                v-model="manageForm.admin_notes"
                                rows="2"
                                placeholder="e.g. Paid invoice #1049, special 30-day clinic demo extension..."
                                class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-1 focus:ring-teal-500"
                            ></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button
                                @click="isManageModalOpen = false"
                                type="button"
                                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="manageForm.processing"
                                class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition disabled:opacity-50"
                            >
                                Save Changes
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            <!-- MODAL: Create New Doctor -->
            <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
                <div class="w-full max-w-xl rounded-3xl bg-white dark:bg-[#0A0D36] border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden p-6 sm:p-8 space-y-6">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <UserPlus class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                Add New Doctor Account
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Provision a new medical practitioner with custom subscription plan.
                            </p>
                        </div>
                        <button @click="isCreateModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCreateDoctor" class="space-y-4">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Doctor Full Name *</label>
                                <input
                                    v-model="createForm.name"
                                    type="text"
                                    required
                                    placeholder="e.g. Dr. John Doe"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Email Address *</label>
                                <input
                                    v-model="createForm.email"
                                    type="email"
                                    required
                                    placeholder="doctor@clinic.com"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Phone Number</label>
                                <input
                                    v-model="createForm.phone"
                                    type="text"
                                    placeholder="+1 234 567 890"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Password *</label>
                                <input
                                    v-model="createForm.password"
                                    type="password"
                                    required
                                    placeholder="Minimum 8 characters"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Title</label>
                                <input
                                    v-model="createForm.title"
                                    type="text"
                                    placeholder="Dr. / Prof. Dr."
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Specialty</label>
                                <input
                                    v-model="createForm.specialty"
                                    type="text"
                                    placeholder="e.g. Cardiologist & Medicine"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Plan</label>
                                <select
                                    v-model="createForm.subscription_plan"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                >
                                    <option value="free_trial">Free Trial</option>
                                    <option value="solo">Solo Physician</option>
                                    <option value="pro">Pro Practice</option>
                                    <option value="enterprise">Enterprise</option>
                                    <option value="lifetime">Lifetime</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Status</label>
                                <select
                                    v-model="createForm.subscription_status"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                >
                                    <option value="trialing">Trialing</option>
                                    <option value="active">Active Paid</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1">Trial Days</label>
                                <input
                                    v-model.number="createForm.trial_days"
                                    type="number"
                                    min="1"
                                    max="365"
                                    class="w-full py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:ring-1 focus:ring-teal-500"
                                />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-3">
                            <button
                                @click="isCreateModalOpen = false"
                                type="button"
                                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="createForm.processing"
                                class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-sm transition disabled:opacity-50"
                            >
                                Create Doctor Account
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
