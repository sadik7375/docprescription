<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    BarChart3,
    Calendar,
    CalendarDays,
    TrendingUp,
    Users,
    UserCheck,
    Pill,
    FileText,
    DollarSign,
    CreditCard,
    CheckCircle2,
    AlertCircle,
    Building2,
    Download,
    Printer,
    Sparkles,
    Zap,
    Search,
    ChevronRight,
    ArrowUpRight,
    Filter,
    Stethoscope,
    Clock
} from 'lucide-vue-next';

const props = defineProps({
    filters: Object,
    chambers: Array,
    kpis: Object,
    daily_income: Object,
    top_medications: Array,
    top_diagnoses: Array,
    appointments_log: Array,
    ai_summary: Object,
});

const activeTab = ref('medications'); // 'medications', 'appointments', 'financials', 'diagnoses'
const searchMedQuery = ref('');
const searchAppQuery = ref('');

// Filter reactive state
const selectedRange = ref(props.filters.date_range || 'this_month');
const customStart = ref(props.filters.start_date || '');
const customEnd = ref(props.filters.end_date || '');
const selectedChamber = ref(props.filters.chamber_id || '');

const applyFilter = (range = selectedRange.value) => {
    selectedRange.value = range;
    router.get(
        route('reports.index'),
        {
            date_range: selectedRange.value,
            start_date: selectedRange.value === 'custom' ? customStart.value : undefined,
            end_date: selectedRange.value === 'custom' ? customEnd.value : undefined,
            chamber_id: selectedChamber.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

// Filtered medications
const filteredMedications = computed(() => {
    if (!props.top_medications) return [];
    if (!searchMedQuery.value.trim()) return props.top_medications;
    const q = searchMedQuery.value.toLowerCase();
    return props.top_medications.filter(
        (m) =>
            m.medicine_name?.toLowerCase().includes(q) ||
            m.generic_name?.toLowerCase().includes(q) ||
            m.dosage_form?.toLowerCase().includes(q)
    );
});

// Filtered appointments
const filteredAppointments = computed(() => {
    if (!props.appointments_log) return [];
    if (!searchAppQuery.value.trim()) return props.appointments_log;
    const q = searchAppQuery.value.toLowerCase();
    return props.appointments_log.filter(
        (a) =>
            a.patient_name?.toLowerCase().includes(q) ||
            a.patient_phone?.toLowerCase().includes(q) ||
            a.token_code?.toLowerCase().includes(q) ||
            a.chamber_name?.toLowerCase().includes(q)
    );
});

// Download CSV helper
const downloadCsv = (type) => {
    const params = new URLSearchParams({
        type: type,
        date_range: selectedRange.value,
    });
    if (selectedRange.value === 'custom') {
        params.append('start_date', customStart.value);
        params.append('end_date', customEnd.value);
    }
    if (selectedChamber.value) {
        params.append('chamber_id', selectedChamber.value);
    }
    window.location.href = route('reports.export') + '?' + params.toString();
};

const togglePayment = (app) => {
    router.patch(
        route('appointments.toggle-payment', app.id),
        {},
        {
            preserveScroll: true,
        }
    );
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Practice Reports & AI Intelligence" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6 pb-16">
            <!-- Header Section (Screen only) -->
            <div class="print:hidden p-6 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20 shrink-0">
                        <BarChart3 class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                                Practice Reports & AI Analytics
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-100 dark:bg-teal-900/60 text-teal-800 dark:text-teal-200 border border-teal-200 dark:border-teal-800 flex items-center gap-1">
                                <Sparkles class="w-3 h-3 text-amber-500" />
                                AI Powered
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 font-medium mt-0.5">
                            Comprehensive diagnostic volume, running medicine trends, patient demographics & income reports.
                        </p>
                    </div>
                </div>

                <!-- Export & Print Actions -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button
                        type="button"
                        @click="printReport"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-[#30363D] text-slate-800 dark:text-slate-200 font-bold text-xs transition cursor-pointer shadow-2xs"
                    >
                        <Printer class="w-3.5 h-3.5" />
                        <span>Print Report</span>
                    </button>

                    <button
                        type="button"
                        @click="downloadCsv('all')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition cursor-pointer shadow-md shadow-teal-600/20"
                    >
                        <Download class="w-3.5 h-3.5" />
                        <span>Export Full Report (CSV)</span>
                    </button>
                </div>
            </div>

            <!-- Printable Header (Visible only on Print) -->
            <div class="hidden print:block text-center border-b pb-4 mb-6">
                <h1 class="text-2xl font-black text-slate-900">Doctor Practice Clinical & Executive Report</h1>
                <p class="text-sm text-slate-600 mt-1">
                    Report Range: {{ filters.start_date }} to {{ filters.end_date }} | Generated: {{ new Date().toLocaleString() }}
                </p>
            </div>

            <!-- Date & Chamber Filter Toolbar (Screen only) -->
            <div class="print:hidden p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] shadow-xs space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <!-- Date Presets -->
                    <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                        <span class="text-slate-500 dark:text-slate-400 mr-1 text-[11px] uppercase tracking-wider">Date Range:</span>
                        <button
                            v-for="range in [
                                { key: 'today', label: 'Today' },
                                { key: 'yesterday', label: 'Yesterday' },
                                { key: 'last_7_days', label: 'Last 7 Days' },
                                { key: 'this_month', label: 'This Month' },
                                { key: 'last_30_days', label: 'Last 30 Days' },
                                { key: 'this_year', label: 'This Year' },
                                { key: 'custom', label: 'Custom' },
                            ]"
                            :key="range.key"
                            type="button"
                            @click="applyFilter(range.key)"
                            class="px-3 py-1.5 rounded-xl transition cursor-pointer border"
                            :class="selectedRange === range.key ? 'bg-teal-600 text-white border-teal-600 font-extrabold shadow-xs' : 'bg-slate-50 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 border-slate-200 dark:border-[#30363D] hover:bg-slate-100 dark:hover:bg-[#30363D]'"
                        >
                            {{ range.label }}
                        </button>
                    </div>

                    <!-- Chamber Filter -->
                    <div class="flex items-center gap-2">
                        <Building2 class="w-4 h-4 text-slate-400" />
                        <select
                            v-model="selectedChamber"
                            @change="applyFilter()"
                            class="text-xs font-bold rounded-xl border border-slate-200 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-800 dark:text-slate-200 px-3 py-1.5 focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                        >
                            <option value="">All Chambers</option>
                            <option v-for="ch in chambers" :key="ch.id" :value="ch.id">
                                {{ ch.name }} {{ ch.is_default ? '(Default)' : '' }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Custom Date Inputs (when Custom is selected) -->
                <div v-if="selectedRange === 'custom'" class="pt-3 border-t border-slate-100 dark:border-[#21262D] flex flex-wrap items-center gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <label class="font-bold text-slate-700 dark:text-slate-300">From:</label>
                        <input
                            type="date"
                            v-model="customStart"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="font-bold text-slate-700 dark:text-slate-300">To:</label>
                        <input
                            type="date"
                            v-model="customEnd"
                            class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500"
                        />
                    </div>
                    <button
                        type="button"
                        @click="applyFilter('custom')"
                        class="px-4 py-1.5 rounded-xl bg-teal-600 text-white font-bold transition hover:bg-teal-700 cursor-pointer shadow-xs"
                    >
                        Apply Date Range
                    </button>
                </div>
            </div>

            <!-- Top 4 KPI Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Total Realized Income -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#161B22] border-l-4 border-l-emerald-500 border border-slate-200 dark:border-[#21262D] shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Total Income Collected
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <DollarSign class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">
                        {{ kpis.total_income?.toLocaleString() }}
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-[#21262D]">
                        <span>Billed: <strong>{{ kpis.total_billed?.toLocaleString() }}</strong></span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ kpis.collection_rate }}% rate</span>
                    </div>
                </div>

                <!-- Total Appointments -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#161B22] border-l-4 border-l-indigo-500 border border-slate-200 dark:border-[#21262D] shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Total Appointments
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <Calendar class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">
                        {{ kpis.total_appointments }}
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-[#21262D]">
                        <span class="text-emerald-600 dark:text-emerald-400 font-bold">{{ kpis.completed_appointments }} completed</span>
                        <span class="text-slate-400">{{ kpis.scheduled_appointments }} queued</span>
                    </div>
                </div>

                <!-- Patients Visited -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#161B22] border-l-4 border-l-sky-500 border border-slate-200 dark:border-[#21262D] shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Patients Visited
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                            <Users class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">
                        {{ kpis.total_patients }}
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-[#21262D]">
                        <span class="text-sky-600 dark:text-sky-400 font-bold">{{ kpis.new_patients }} New</span>
                        <span>{{ kpis.returning_patients }} Follow-up</span>
                    </div>
                </div>

                <!-- Prescriptions Issued -->
                <div class="p-5 rounded-2xl bg-white dark:bg-[#161B22] border-l-4 border-l-teal-500 border border-slate-200 dark:border-[#21262D] shadow-xs space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Prescriptions (Rx)
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                            <FileText class="w-4 h-4" />
                        </div>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white">
                        {{ kpis.total_prescriptions }}
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 pt-1 border-t border-slate-100 dark:border-[#21262D]">
                        <span>Avg Fee / Visit:</span>
                        <strong class="text-teal-700 dark:text-teal-300 font-bold">{{ kpis.avg_fee }}</strong>
                    </div>
                </div>
            </div>

            <!-- Tabbed Detailed Tables Section (Placed at the Top) -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200 dark:border-[#21262D] shadow-xs overflow-hidden">
                <!-- Navigation Tabs Bar -->
                <div class="print:hidden border-b border-slate-200 dark:border-[#21262D] bg-slate-50/70 dark:bg-[#1C2128] px-4 pt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                        <button
                            type="button"
                            @click="activeTab = 'medications'"
                            class="px-4 py-2.5 font-extrabold text-xs rounded-t-xl transition-all flex items-center gap-2 border-b-2 cursor-pointer"
                            :class="activeTab === 'medications' ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-900 dark:hover:text-white'"
                        >
                            <Pill class="w-4 h-4" />
                            <span>Running Medications ({{ top_medications?.length || 0 }})</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'appointments'"
                            class="px-4 py-2.5 font-extrabold text-xs rounded-t-xl transition-all flex items-center gap-2 border-b-2 cursor-pointer"
                            :class="activeTab === 'appointments' ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-900 dark:hover:text-white'"
                        >
                            <CalendarDays class="w-4 h-4" />
                            <span>Appointments Log ({{ appointments_log?.length || 0 }})</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'diagnoses'"
                            class="px-4 py-2.5 font-extrabold text-xs rounded-t-xl transition-all flex items-center gap-2 border-b-2 cursor-pointer"
                            :class="activeTab === 'diagnoses' ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-900 dark:hover:text-white'"
                        >
                            <Stethoscope class="w-4 h-4" />
                            <span>Disease & Diagnoses ({{ top_diagnoses?.length || 0 }})</span>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'financials'"
                            class="px-4 py-2.5 font-extrabold text-xs rounded-t-xl transition-all flex items-center gap-2 border-b-2 cursor-pointer"
                            :class="activeTab === 'financials' ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 border-teal-600 dark:border-teal-400 shadow-2xs' : 'text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-900 dark:hover:text-white'"
                        >
                            <CreditCard class="w-4 h-4" />
                            <span>Financials & Income</span>
                        </button>
                    </div>

                    <!-- Tab-specific CSV Export -->
                    <button
                        type="button"
                        @click="downloadCsv(activeTab)"
                        class="mb-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-[#21262D] hover:bg-slate-100 dark:hover:bg-[#30363D] text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-[#30363D] text-xs font-bold transition cursor-pointer shadow-xs"
                    >
                        <Download class="w-3.5 h-3.5" />
                        <span>Export {{ activeTab }} (CSV)</span>
                    </button>
                </div>

                <!-- TAB 1: RUNNING MEDICATIONS (Active / Ongoing Medications) -->
                <div v-show="activeTab === 'medications'" class="p-5 space-y-4">
                    <div class="print:hidden flex flex-wrap items-center justify-between gap-3">
                        <div class="relative w-full sm:w-72">
                            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input
                                type="text"
                                v-model="searchMedQuery"
                                placeholder="Search medicine, generic or form..."
                                class="w-full pl-9 pr-3.5 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500"
                            />
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            Showing <strong>{{ filteredMedications.length }}</strong> most prescribed drugs in selected date range
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-[#1E2633] text-slate-700 dark:text-slate-300 uppercase tracking-wider font-extrabold border-y border-slate-200 dark:border-[#30363D]">
                                <tr>
                                    <th class="py-3 px-4">#</th>
                                    <th class="py-3 px-4">Medicine Name & Form</th>
                                    <th class="py-3 px-4">Generic / Molecule</th>
                                    <th class="py-3 px-4">Typical Dosage</th>
                                    <th class="py-3 px-4">Typical Duration</th>
                                    <th class="py-3 px-4 text-center">Prescribed Count</th>
                                    <th class="py-3 px-4 text-right">Rx Share (%)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-[#21262D] font-medium text-slate-800 dark:text-slate-200">
                                <tr
                                    v-for="(med, mIdx) in filteredMedications"
                                    :key="mIdx"
                                    class="hover:bg-slate-50/80 dark:hover:bg-[#1C2128] transition"
                                >
                                    <td class="py-3 px-4 font-bold text-slate-400">{{ mIdx + 1 }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <Pill class="w-4 h-4 text-teal-600 dark:text-teal-400 shrink-0" />
                                            <div>
                                                <span>{{ med.medicine_name }}</span>
                                                <span v-if="med.strength" class="text-[11px] text-slate-500 ml-1">({{ med.strength }})</span>
                                                <span class="block text-[10px] text-slate-400 font-normal uppercase">{{ med.dosage_form }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-medium">{{ med.generic_name }}</td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-700 dark:text-slate-300">{{ med.common_dosage }}</td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400">{{ med.common_duration }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-lg bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-200 font-bold border border-teal-200 dark:border-teal-800">
                                            {{ med.total_prescribed }} times
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-teal-700 dark:text-teal-300">
                                        {{ med.prescription_percentage }}%
                                    </td>
                                </tr>
                                <tr v-if="filteredMedications.length === 0">
                                    <td colspan="7" class="py-8 text-center text-slate-400 font-medium">
                                        No prescription medications found for this date range or search filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: APPOINTMENTS & PATIENTS LOG -->
                <div v-show="activeTab === 'appointments'" class="p-5 space-y-4">
                    <div class="print:hidden flex flex-wrap items-center justify-between gap-3">
                        <div class="relative w-full sm:w-72">
                            <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                            <input
                                type="text"
                                v-model="searchAppQuery"
                                placeholder="Search patient, phone, token..."
                                class="w-full pl-9 pr-3.5 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500"
                            />
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                            Showing <strong>{{ filteredAppointments.length }}</strong> appointments
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-[#1E2633] text-slate-700 dark:text-slate-300 uppercase tracking-wider font-extrabold border-y border-slate-200 dark:border-[#30363D]">
                                <tr>
                                    <th class="py-3 px-4">Token</th>
                                    <th class="py-3 px-4">Date & Time</th>
                                    <th class="py-3 px-4">Patient Details</th>
                                    <th class="py-3 px-4">Chamber</th>
                                    <th class="py-3 px-4">Type</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Fee & Payment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-[#21262D] font-medium text-slate-800 dark:text-slate-200">
                                <tr
                                    v-for="app in filteredAppointments"
                                    :key="app.id"
                                    class="hover:bg-slate-50/80 dark:hover:bg-[#1C2128] transition"
                                >
                                    <td class="py-3 px-4 font-mono font-bold text-teal-700 dark:text-teal-300">
                                        {{ app.token_code || ('#' + app.serial_no) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ app.date }}</div>
                                        <div class="text-[11px] text-slate-400">{{ app.time }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                        <div>{{ app.patient_name }}</div>
                                        <div class="text-[11px] text-slate-500 font-normal">{{ app.patient_phone }} • {{ app.patient_age_gender }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">{{ app.chamber_name }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-[#30363D]">
                                            {{ app.type }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase"
                                            :class="app.status === 'completed' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300'"
                                        >
                                            {{ app.status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="font-black text-slate-900 dark:text-white">{{ app.payable_amount }}</div>
                                        <button
                                            type="button"
                                            @click="togglePayment(app)"
                                            class="inline-flex items-center gap-1 text-[10px] font-black uppercase px-2 py-0.5 rounded-md cursor-pointer transition shadow-2xs border"
                                            :class="app.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800 hover:bg-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800 hover:bg-amber-200'"
                                            :title="app.payment_status === 'paid' ? 'Click to mark as UNPAID' : 'Click to mark as PAID'"
                                        >
                                            <span>{{ app.payment_status }}</span>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="filteredAppointments.length === 0">
                                    <td colspan="7" class="py-8 text-center text-slate-400 font-medium">
                                        No appointments found for this filter.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: DISEASE & DIAGNOSES -->
                <div v-show="activeTab === 'diagnoses'" class="p-5 space-y-4">
                    <p class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                        Top clinical disease presentations diagnosed during this reporting period:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div
                            v-for="(diag, dIdx) in top_diagnoses"
                            :key="dIdx"
                            class="p-4 rounded-xl bg-slate-50 dark:bg-[#1E2633] border border-slate-200 dark:border-[#30363D] space-y-2"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-900 dark:text-white">
                                    #{{ dIdx + 1 }}. {{ diag.diagnosis }}
                                </span>
                                <span class="px-2 py-0.5 rounded-lg bg-teal-100 dark:bg-teal-950 text-teal-800 dark:text-teal-300 font-bold text-xs">
                                    {{ diag.count }} cases ({{ diag.percentage }}%)
                                </span>
                            </div>
                            <!-- Percentage Progress Bar -->
                            <div class="w-full bg-slate-200 dark:bg-[#30363D] rounded-full h-2 overflow-hidden">
                                <div
                                    class="bg-teal-600 h-2 rounded-full"
                                    :style="{ width: Math.min(100, diag.percentage) + '%' }"
                                ></div>
                            </div>
                        </div>
                        <div v-if="!top_diagnoses || top_diagnoses.length === 0" class="col-span-2 py-8 text-center text-slate-400 text-xs">
                            No diagnoses recorded in prescriptions for this period.
                        </div>
                    </div>
                </div>

                <!-- TAB 4: FINANCIALS & REVENUE -->
                <div v-show="activeTab === 'financials'" class="p-5 space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800">
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">Realized / Paid Revenue</span>
                            <p class="text-2xl font-black text-emerald-900 dark:text-emerald-200 mt-1">{{ kpis.total_income?.toLocaleString() }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800">
                            <span class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wider">Unpaid / Due Fees</span>
                            <p class="text-2xl font-black text-amber-900 dark:text-amber-200 mt-1">{{ kpis.total_pending?.toLocaleString() }}</p>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#1E2633] border border-slate-200 dark:border-[#30363D]">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Average Ticket per Patient</span>
                            <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ kpis.avg_fee }}</p>
                        </div>
                    </div>

                    <!-- Daily Revenue Breakdown -->
                    <div v-if="daily_income && Object.keys(daily_income).length > 0" class="space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Daily Realized Collections:
                        </h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
                            <div
                                v-for="(amount, day) in daily_income"
                                :key="day"
                                class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#1E2633] border border-slate-200 dark:border-[#30363D] text-center"
                            >
                                <span class="text-[10px] text-slate-400 font-bold block">{{ day }}</span>
                                <strong class="text-xs font-extrabold text-slate-900 dark:text-white">{{ amount }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Practice Intelligence Executive Summary Bulletin (Placed Below Tables) -->
            <div v-if="ai_summary" class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#161B22] border-2 border-teal-500/40 dark:border-teal-500/50 shadow-sm space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 dark:border-[#21262D] pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20 shrink-0">
                            <Sparkles class="w-4 h-4 text-amber-300" />
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white uppercase tracking-wide">
                                {{ ai_summary.headline }}
                            </h3>
                            <span class="text-[11px] font-bold text-teal-700 dark:text-teal-300">
                                Status: {{ ai_summary.status }}
                            </span>
                        </div>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-[#21262D] px-2.5 py-1 rounded-lg">
                        {{ ai_summary.generated_at }}
                    </span>
                </div>

                <div class="space-y-3 text-xs sm:text-sm">
                    <p class="text-slate-800 dark:text-slate-200 leading-relaxed font-medium">
                        {{ ai_summary.summary }}
                    </p>
                    <p class="text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-[#1E2633] p-3 rounded-xl border border-slate-200 dark:border-[#30363D]">
                        🩺 <strong>Clinical & Pharmacotherapy Cluster:</strong> {{ ai_summary.clinical_insight }}
                    </p>
                </div>

                <!-- Actionable Practice Recommendations -->
                <div class="space-y-2 pt-1">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-teal-800 dark:text-teal-300 flex items-center gap-1.5">
                        <Zap class="w-3.5 h-3.5" />
                        <span>AI Practice & Revenue Optimization Recommendations:</span>
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div
                            v-for="(rec, rIdx) in ai_summary.recommendations"
                            :key="rIdx"
                            class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#1E2633] border border-slate-200 dark:border-[#30363D] text-xs text-slate-800 dark:text-slate-200 font-semibold flex items-start gap-2.5 shadow-2xs"
                        >
                            <span class="w-5 h-5 rounded-full bg-teal-600 text-white font-black text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                {{ rIdx + 1 }}
                            </span>
                            <span class="leading-relaxed">{{ rec }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>