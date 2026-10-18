<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    FileText,
    Users,
    Calendar,
    Activity,
    Plus,
    Printer,
    Eye,
    TrendingUp,
    TrendingDown,
    ChevronDown,
    Stethoscope,
    Clock,
    Pill,
    Sparkles,
    Building2,
    X,
    CheckCircle2
} from 'lucide-vue-next';
import AiDiseaseOutbreakWidget from '@/Components/AiDiseaseOutbreakWidget.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            today_prescriptions: 0,
            prescriptions_change: 0,
            total_prescriptions: 0,
            month_prescriptions: 0,
            total_patients: 0,
            new_patients_month: 0,
            today_appointments: 0,
            waiting_appointments: 0,
        })
    },
    monthlyTrend: {
        type: Array,
        default: () => []
    },
    topDiagnoses: {
        type: Array,
        default: () => []
    },
    liveQueue: {
        type: Array,
        default: () => []
    },
    recentPrescriptions: {
        type: Array,
        default: () => []
    },
});

const selectedPeriod = ref('Today');
const periods = ['Today', 'This Week', 'This Month', 'All Time'];
const isPeriodDropdownOpen = ref(false);

const activeChartTab = ref('Prescriptions');
const chartTabs = ['Prescriptions', 'Patient Flow'];

// 4 Top Cards (ByeWind light-blue & soft-periwinkle pastel colors)
const kpiCards = computed(() => [
    {
        title: "Today's Prescriptions",
        value: props.stats.today_prescriptions,
        subtitle: `${props.stats.prescriptions_change >= 0 ? '+' : ''}${props.stats.prescriptions_change}% vs yesterday`,
        trend: props.stats.prescriptions_change >= 0 ? 'up' : 'down',
        bg: 'bg-[#E3F5FF] dark:bg-[#162B3D]',
        icon: FileText,
        iconColor: 'text-cyan-700 dark:text-cyan-300',
    },
    {
        title: 'Total Prescriptions Written',
        value: props.stats.total_prescriptions,
        subtitle: `${props.stats.month_prescriptions} this month`,
        trend: 'up',
        bg: 'bg-[#E5ECF6] dark:bg-[#1E2633]',
        icon: Stethoscope,
        iconColor: 'text-indigo-700 dark:text-indigo-300',
    },
    {
        title: 'Registered Patients',
        value: props.stats.total_patients,
        subtitle: `+${props.stats.new_patients_month} new this month`,
        trend: 'up',
        bg: 'bg-[#E3F5FF] dark:bg-[#162B3D]',
        icon: Users,
        iconColor: 'text-teal-700 dark:text-teal-300',
    },
    {
        title: "Today's Patient Queue",
        value: props.stats.today_appointments,
        subtitle: `${props.stats.waiting_appointments} waiting in chamber`,
        trend: props.stats.waiting_appointments > 0 ? 'up' : 'down',
        bg: 'bg-[#E5ECF6] dark:bg-[#1E2633]',
        icon: Activity,
        iconColor: 'text-emerald-700 dark:text-emerald-300',
    },
]);

// Modal State for Quick Prescription Preview
const selectedRx = ref(null);
const isPreviewModalOpen = ref(false);

const openRxModal = (rx) => {
    selectedRx.value = rx;
    isPreviewModalOpen.value = true;
};

const closeRxModal = () => {
    isPreviewModalOpen.value = false;
    selectedRx.value = null;
};

// Calculate SVG Path for Monthly Trend
const chartPoints = computed(() => {
    if (!props.monthlyTrend || props.monthlyTrend.length === 0) {
        return [
            { x: 0, y: 150 },
            { x: 100, y: 130 },
            { x: 200, y: 110 },
            { x: 300, y: 80 },
            { x: 400, y: 90 },
            { x: 500, y: 60 },
            { x: 600, y: 40 },
        ];
    }
    const maxVal = Math.max(...props.monthlyTrend.map(m => m.count), 10);
    const count = props.monthlyTrend.length;
    return props.monthlyTrend.map((item, idx) => {
        const x = (idx / Math.max(1, count - 1)) * 600;
        const normalized = item.count / maxVal;
        const y = 170 - (normalized * 130);
        return { x, y, month: item.month, count: item.count };
    });
});

const svgLinePath = computed(() => {
    const pts = chartPoints.value;
    if (pts.length === 0) return '';
    let d = `M ${pts[0].x} ${pts[0].y}`;
    for (let i = 1; i < pts.length; i++) {
        const prev = pts[i - 1];
        const curr = pts[i];
        const cpX = (prev.x + curr.x) / 2;
        d += ` C ${cpX} ${prev.y}, ${cpX} ${curr.y}, ${curr.x} ${curr.y}`;
    }
    return d;
});

const svgAreaPath = computed(() => {
    const line = svgLinePath.value;
    if (!line) return '';
    return `${line} L 600 200 L 0 200 Z`;
});
</script>

<template>
    <Head title="Doctor Practice Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto pb-12">
            <!-- Top Hero Banner: Welcome Greeting + Fast Action Buttons -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                            <Stethoscope class="w-5 h-5" />
                        </div>
                        <div>
                            <h1 class="text-base font-bold text-slate-900 dark:text-white">
                                Welcome, {{ $page.props.auth?.user?.name || 'Doctor' }}
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $page.props.activeChamber ? $page.props.activeChamber.name : 'Clinical Practice' }} • Prescription & Practice Console
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fast Rx & Patient Action Shortcuts -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <Link
                        :href="route('prescriptions.create')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Write Prescription (Rx)</span>
                    </Link>

                    <Link
                        :href="route('patients.create')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-all cursor-pointer"
                    >
                        <Users class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                        <span>New Patient</span>
                    </Link>

                    <Link
                        :href="route('appointments.create')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-all cursor-pointer"
                    >
                        <Calendar class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" />
                        <span>Book Appointment</span>
                    </Link>
                </div>
            </div>

            <!-- Top 4 KPI Metrics (Exact ByeWind Pastel Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div
                    v-for="card in kpiCards"
                    :key="card.title"
                    :class="[card.bg, 'p-6 rounded-2xl transition-all duration-200 shadow-xs flex flex-col justify-between select-none']"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-300">{{ card.title }}</span>
                        <component :is="card.icon" class="w-4 h-4" :class="card.iconColor" />
                    </div>

                    <div class="mt-4 flex items-baseline justify-between">
                        <span class="text-2xl sm:text-3xl font-black tracking-tight text-[#1C1C1C] dark:text-white">
                            {{ card.value }}
                        </span>

                        <div class="flex items-center gap-1 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                            <span>{{ card.subtitle }}</span>
                            <TrendingUp v-if="card.trend === 'up'" class="w-3 h-3 text-emerald-600 dark:text-emerald-400" />
                            <TrendingDown v-else class="w-3 h-3 text-rose-500" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Chamber Disease Outbreak & Seasonal Trends -->
            <AiDiseaseOutbreakWidget v-if="$page.props.auth?.can_use_ai" />

            <!-- Middle Section: Prescription Trend Chart + Top Diagnoses (Dx) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left 2 Cols: Prescription Activity Curve -->
                <div class="lg:col-span-2 bg-white dark:bg-[#161B22] p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <!-- Chart Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <Activity class="w-4 h-4 text-teal-600" />
                                <span>Prescription Volume & Clinical Activity</span>
                            </h2>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Monthly digital prescriptions written across all active chambers
                            </p>
                        </div>

                        <div class="flex items-center gap-3 text-xs">
                            <div class="flex items-center gap-1.5 text-slate-800 dark:text-slate-200 font-semibold">
                                <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
                                <span>Prescriptions Written</span>
                            </div>
                        </div>
                    </div>

                    <!-- SVG Smooth Trend Chart -->
                    <div class="relative w-full h-56 sm:h-64">
                        <!-- Y-Axis Background Lines -->
                        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[10px] text-slate-400 font-mono">
                            <div class="flex items-center w-full"><span class="w-8">High</span><div class="flex-1 border-b border-slate-100 dark:border-[#21262D]"></div></div>
                            <div class="flex items-center w-full"><span class="w-8">Mid</span><div class="flex-1 border-b border-slate-100 dark:border-[#21262D]"></div></div>
                            <div class="flex items-center w-full"><span class="w-8">Low</span><div class="flex-1 border-b border-slate-100 dark:border-[#21262D]"></div></div>
                            <div class="flex items-center w-full"><span class="w-8">0</span><div class="flex-1 border-b border-slate-100 dark:border-[#21262D]"></div></div>
                        </div>

                        <!-- Smooth Curves -->
                        <svg class="absolute inset-0 w-full h-full pl-8 pb-4" viewBox="0 0 600 200" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartTealGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#0D9488" stop-opacity="0.25" />
                                    <stop offset="100%" stop-color="#0D9488" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>

                            <!-- Gradient Filled Area -->
                            <path :d="svgAreaPath" fill="url(#chartTealGrad)" />

                            <!-- Active Solid Curve -->
                            <path
                                :d="svgLinePath"
                                fill="none"
                                stroke="#0D9488"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            />

                            <!-- Data Points -->
                            <circle
                                v-for="pt in chartPoints"
                                :key="pt.month"
                                :cx="pt.x"
                                :cy="pt.y"
                                r="4"
                                fill="#0D9488"
                                stroke="#FFFFFF"
                                stroke-width="2"
                            />
                        </svg>

                        <!-- X-Axis Month Labels -->
                        <div class="absolute bottom-0 left-8 right-0 flex justify-between text-[11px] text-slate-400 font-semibold pt-1">
                            <span v-for="item in monthlyTrend" :key="item.month">{{ item.month }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right 1 Col: Top Diagnoses (Dx) Distribution -->
                <div class="bg-white dark:bg-[#161B22] p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    Top Clinical Diagnoses
                                </h3>
                                <p class="text-[11px] text-slate-400">Most frequent clinical encounters</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300">
                                Frequent
                            </span>
                        </div>

                        <div class="space-y-4">
                            <div v-for="diag in topDiagnoses" :key="diag.name" class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-slate-800 dark:text-slate-200 font-medium truncate max-w-[180px]">
                                        {{ diag.name }}
                                    </span>
                                    <span class="font-bold text-slate-600 dark:text-slate-400 text-[11px]">
                                        {{ diag.count > 0 ? `${diag.count} Rx` : 'Common' }}
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-[#21262D] h-2 rounded-full overflow-hidden">
                                    <div
                                        class="bg-gradient-to-r from-teal-500 to-cyan-500 h-2 rounded-full transition-all duration-500"
                                        :style="{ width: diag.barWidth }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between text-[11px] text-slate-400">
                        <span>Auto-categorized from Rx</span>
                        <Link :href="route('clinical-lookups.index')" class="text-teal-600 dark:text-teal-400 font-bold hover:underline">
                            Manage Lookups →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Live Chamber Patient Queue + Recent Prescriptions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 1. Live Patient Waiting Queue (Today's Chamber) -->
                <div class="bg-white dark:bg-[#161B22] p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                                <Users class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Live Patient Queue (Today)
                                </h2>
                                <p class="text-[11px] text-slate-400">Current chamber appointments awaiting consultation</p>
                            </div>
                        </div>

                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                            {{ liveQueue.length }} in Queue
                        </span>
                    </div>

                    <!-- Queue List -->
                    <div v-if="liveQueue.length > 0" class="space-y-2.5">
                        <div
                            v-for="patient in liveQueue"
                            :key="patient.id"
                            class="p-3.5 rounded-xl border border-slate-100 dark:border-[#21262D] hover:border-teal-200 dark:hover:border-teal-800 bg-slate-50/50 dark:bg-[#21262D]/40 transition-all flex items-center justify-between gap-3"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-slate-200 dark:bg-[#30363D] font-mono font-black text-xs text-slate-800 dark:text-slate-200 flex items-center justify-center shrink-0">
                                    {{ patient.serial_no }}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">
                                            {{ patient.patient_name }}
                                        </h4>
                                        <span class="text-[10px] text-slate-400 font-medium shrink-0">
                                            ({{ patient.patient_age }}, {{ patient.patient_gender }})
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                        {{ patient.problem }}
                                        <span v-if="patient.bp" class="ml-1 text-teal-600 font-semibold">• BP: {{ patient.bp }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <Link
                                    :href="route('prescriptions.create', { patient_id: patient.patient_id, appointment_id: patient.id })"
                                    class="px-3 py-1.5 bg-slate-900 hover:bg-teal-600 dark:bg-slate-100 dark:hover:bg-teal-500 text-white dark:text-slate-900 dark:hover:text-white text-xs font-bold rounded-lg transition-colors cursor-pointer"
                                >
                                    Prescribe
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-10 space-y-2">
                        <Users class="w-8 h-8 text-slate-300 mx-auto" />
                        <p class="text-xs text-slate-500 font-medium">No patients currently queued for today.</p>
                        <Link
                            :href="route('appointments.create')"
                            class="inline-flex items-center gap-1 text-xs text-teal-600 font-bold hover:underline"
                        >
                            + Add Appointment to Queue
                        </Link>
                    </div>
                </div>

                <!-- 2. Recent Prescriptions (Rx Pad Activity) -->
                <div class="bg-white dark:bg-[#161B22] p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                <FileText class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Recent Prescriptions (Rx)
                                </h2>
                                <p class="text-[11px] text-slate-400">Latest prescription pads generated</p>
                            </div>
                        </div>

                        <Link
                            :href="route('prescriptions.index')"
                            class="text-xs font-bold text-teal-600 dark:text-teal-400 hover:underline"
                        >
                            View All ({{ stats.total_prescriptions }}) →
                        </Link>
                    </div>

                    <!-- Prescriptions List -->
                    <div v-if="recentPrescriptions.length > 0" class="space-y-2.5">
                        <div
                            v-for="rx in recentPrescriptions"
                            :key="rx.id"
                            class="p-3.5 rounded-xl border border-slate-100 dark:border-[#21262D] hover:border-slate-300 dark:hover:border-slate-700 bg-slate-50/50 dark:bg-[#21262D]/40 transition-all flex items-center justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">
                                        {{ rx.patient_name }}
                                    </h4>
                                    <span class="font-mono text-[10px] text-slate-400 font-semibold shrink-0">
                                        ({{ rx.prescription_code }})
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                    <span class="text-teal-600 dark:text-teal-400 font-semibold truncate">{{ rx.diagnoses }}</span>
                                    <span>•</span>
                                    <span>{{ rx.medicines_count }} Medicines</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <Link
                                    :href="route('prescriptions.show', rx.id)"
                                    class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors"
                                    title="View Prescription Details"
                                >
                                    <Eye class="w-4 h-4" />
                                </Link>
                                <a
                                    :href="route('prescriptions.print', rx.id)"
                                    target="_blank"
                                    class="p-1.5 text-slate-400 hover:text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-950/40 rounded-lg transition-colors"
                                    title="Print Rx Pad"
                                >
                                    <Printer class="w-4 h-4" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="text-center py-10 space-y-2">
                        <FileText class="w-8 h-8 text-slate-300 mx-auto" />
                        <p class="text-xs text-slate-500 font-medium">No prescriptions written yet.</p>
                        <Link
                            :href="route('prescriptions.create')"
                            class="inline-flex items-center gap-1 text-xs text-teal-600 font-bold hover:underline"
                        >
                            + Write Your First Prescription
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
