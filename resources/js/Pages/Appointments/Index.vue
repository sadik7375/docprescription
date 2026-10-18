<script setup>
import { ref, watch, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Calendar,
    Clock,
    UserCheck,
    Users,
    UserPlus,
    Building2,
    Plus,
    Search,
    CheckCircle2,
    Activity,
    FileText,
    Trash2,
    Phone,
    X,
    Filter,
    Stethoscope,
    AlertCircle,
    ChevronRight,
    Sparkles,
    HeartPulse,
    Eye
} from 'lucide-vue-next';

const props = defineProps({
    appointments: Array,
    chambers: Array,
    patients: Array,
    selectedDate: String,
    selectedChamberId: Number,
    selectedStatus: String,
    search: String,
    stats: Object,
});

const date = ref(props.selectedDate);
const chamberId = ref(props.selectedChamberId);
const statusFilter = ref(props.selectedStatus || '');
const searchQuery = ref(props.search || '');

const showBookModal = ref(false);
const showVitalsModal = ref(false);
const activeAppointmentForVitals = ref(null);

const patientMode = ref('existing'); // 'existing' or 'new'
const patientSearch = ref('');

const filteredPatients = computed(() => {
    if (!patientSearch.value) return props.patients.slice(0, 10);
    const q = patientSearch.value.toLowerCase();
    return props.patients.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.phone && p.phone.includes(q)) ||
        p.patient_code.toLowerCase().includes(q)
    ).slice(0, 10);
});

// Selected chamber object for modal
const selectedChamber = computed(() => {
    return props.chambers.find(c => c.id == bookForm.chamber_id) || props.chambers[0];
});

// Book Appointment Form
const bookForm = useForm({
    chamber_id: props.selectedChamberId,
    appointment_date: props.selectedDate,
    appointment_time: '18:00',
    type: 'new_consultation',
    fee_amount: 1000,
    discount_type: 'fixed', // 'fixed' or 'percentage'
    discount_value: 0,
    payable_amount: 1000,
    payment_status: 'paid',
    problem: '',
    doctor_notes: '',

    // Existing patient
    patient_id: null,

    // New patient
    new_patient_name: '',
    new_patient_phone: '',
    new_patient_gender: 'male',
    new_patient_age: '',
    new_patient_blood_group: '',

    // Vitals
    vitals: {
        bp: '',
        weight: '',
        pulse: '',
        temperature: '',
    }
});

const togglePayment = (app) => {
    router.patch(
        route('appointments.toggle-payment', app.id),
        {},
        {
            preserveScroll: true,
        }
    );
};

// Computed net payable amount after discount
const calculatedPayable = computed(() => {
    const fee = Number(bookForm.fee_amount) || 0;
    const disc = Number(bookForm.discount_value) || 0;
    if (bookForm.discount_type === 'percentage') {
        const discountVal = (fee * disc) / 100;
        return Math.max(0, Math.round(fee - discountVal));
    }
    return Math.max(0, Math.round(fee - disc));
});

// Auto adjust fee when chamber or type changes
watch(() => [bookForm.chamber_id, bookForm.type], ([newChamberId, newType]) => {
    const chamber = props.chambers.find(c => c.id == newChamberId);
    if (chamber) {
        if (newType === 'follow_up') {
            bookForm.fee_amount = chamber.followup_fee ?? 600;
        } else if (newType === 'report_review') {
            bookForm.fee_amount = 0;
        } else {
            bookForm.fee_amount = chamber.consultation_fee ?? 1000;
        }
    }
});

// Vitals Update Form
const vitalsForm = useForm({
    vitals: {
        bp: '',
        weight: '',
        pulse: '',
        temperature: '',
    },
    problem: '',
});

const applyFilters = () => {
    router.get(
        route('appointments.index'),
        {
            date: date.value,
            chamber_id: chamberId.value,
            status: statusFilter.value,
            search: searchQuery.value,
        },
        { preserveState: true, replace: true }
    );
};

const setDateQuick = (daysOffset) => {
    const d = new Date();
    d.setDate(d.getDate() + daysOffset);
    date.value = d.toISOString().split('T')[0];
    applyFilters();
};

const selectPatient = (patient) => {
    bookForm.patient_id = patient.id;
    patientSearch.value = `${patient.name} (${patient.patient_code}) - ${patient.phone || 'No phone'}`;
};

const formatDisplayTime = (timeStr) => {
    if (!timeStr) return 'Queue';
    // If it's already like 18:00
    if (/^\d{1,2}:\d{2}/.test(timeStr)) {
        const [h, m] = timeStr.split(':').map(Number);
        const period = h >= 12 ? 'PM' : 'AM';
        const displayH = h % 12 === 0 ? 12 : h % 12;
        const mStr = m < 10 ? '0' + m : m;
        return `${displayH}:${mStr} ${period} (${timeStr.slice(0, 5)})`;
    }
    return timeStr;
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('book') === '1' || urlParams.get('book') === 'true') {
        openBookModal();
    }
});

const openBookModal = () => {
    bookForm.reset();
    const defaultChamberId = props.selectedChamberId || (props.chambers[0]?.id ?? '');
    bookForm.chamber_id = defaultChamberId;
    bookForm.appointment_date = date.value;
    bookForm.discount_type = 'fixed';
    bookForm.discount_value = 0;

    const chamber = props.chambers.find(c => c.id == defaultChamberId);
    if (chamber) {
        bookForm.fee_amount = chamber.consultation_fee ?? 1000;
    }

    const now = new Date();
    const curHour = String(now.getHours()).padStart(2, '0');
    const curMin = String(now.getMinutes()).padStart(2, '0');
    bookForm.appointment_time = `${curHour}:${curMin}`;

    patientMode.value = 'existing';
    patientSearch.value = '';
    showBookModal.value = true;
};

const submitBooking = () => {
    bookForm.payable_amount = calculatedPayable.value;
    bookForm.post(route('appointments.store'), {
        onSuccess: () => {
            showBookModal.value = false;
            bookForm.reset();
        }
    });
};

const updateStatus = (appointment, newStatus) => {
    router.patch(route('appointments.update-status', appointment.id), {
        status: newStatus
    }, {
        preserveScroll: true,
    });
};

const openVitalsModal = (appointment) => {
    activeAppointmentForVitals.value = appointment;
    vitalsForm.vitals = {
        bp: appointment.vitals?.bp || '',
        weight: appointment.vitals?.weight || '',
        pulse: appointment.vitals?.pulse || '',
        temperature: appointment.vitals?.temperature || '',
    };
    vitalsForm.problem = appointment.problem || '';
    showVitalsModal.value = true;
};

const submitVitals = () => {
    if (activeAppointmentForVitals.value) {
        vitalsForm.patch(route('appointments.update-vitals', activeAppointmentForVitals.value.id), {
            onSuccess: () => {
                showVitalsModal.value = false;
            }
        });
    }
};

const cancelAppointment = (appointment) => {
    if (confirm(`Are you sure you want to cancel appointment ${appointment.token_code} for ${appointment.patient?.name}?`)) {
        router.delete(route('appointments.destroy', appointment.id));
    }
};

const typeLabels = {
    new_consultation: { label: 'New Consultation', class: 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800' },
    follow_up: { label: 'Follow-up', class: 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' },
    report_review: { label: 'Report Review', class: 'bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 border-purple-200 dark:border-purple-800' },
    emergency: { label: 'Emergency', class: 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800' },
};

const statusLabels = {
    scheduled: { label: 'Scheduled', class: 'bg-slate-100 text-slate-700 border-slate-200' },
    in_queue: { label: 'Waiting in Queue', class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800' },
    in_consultation: { label: 'In Doctor Room', class: 'bg-teal-50 text-teal-700 border-teal-200 dark:bg-teal-950/40 dark:text-teal-300 dark:border-teal-800 animate-pulse' },
    completed: { label: 'Completed', class: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' },
    canceled: { label: 'Canceled', class: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800' },
};
</script>

<template>
    <Head title="Appointments & Live Queue - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header & Action -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Calendar class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        Appointments & Live Queue
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage daily serial tokens, patient queue, vitals, and direct prescription flow.
                    </p>
                </div>

                <button
                    type="button"
                    @click="openBookModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
                >
                    <Plus class="w-4 h-4" />
                    <span>+ Book New Appointment</span>
                </button>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Bookings</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.total }}</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-amber-600 dark:text-amber-400">Waiting in Queue</p>
                    <p class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ stats.waiting }}</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-teal-600 dark:text-teal-400">In Doctor Room</p>
                    <p class="text-xl font-bold text-teal-600 dark:text-teal-400 mt-1">{{ stats.in_consultation }}</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">Completed Visits</p>
                    <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ stats.completed }}</p>
                </div>
            </div>

            <!-- Control Bar: Date Nav, Chamber Selector, Search -->
            <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Date Selector & Quick Buttons -->
                    <div class="flex items-center gap-2">
                        <input
                            v-model="date"
                            @change="applyFilters"
                            type="date"
                            class="px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 font-medium cursor-pointer"
                        />
                        <button
                            type="button"
                            @click="setDateQuick(0)"
                            class="px-2.5 py-2 text-[11px] font-bold rounded-xl border transition-colors cursor-pointer"
                            :class="date === new Date().toISOString().split('T')[0] ? 'bg-slate-900 dark:bg-teal-500 text-white dark:text-slate-950 border-transparent' : 'bg-slate-50 dark:bg-[#21262D] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-[#30363D]'"
                        >
                            Today
                        </button>
                        <button
                            type="button"
                            @click="setDateQuick(1)"
                            class="px-2.5 py-2 text-[11px] font-bold rounded-xl border transition-colors cursor-pointer bg-slate-50 dark:bg-[#21262D] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-[#30363D] hover:bg-slate-100"
                        >
                            Tomorrow
                        </button>
                    </div>

                    <!-- Chamber Filter -->
                    <div>
                        <select
                            v-model="chamberId"
                            @change="applyFilters"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 font-medium"
                        >
                            <option v-for="ch in chambers" :key="ch.id" :value="ch.id">
                                {{ ch.name }} {{ ch.branch ? '(' + ch.branch + ')' : '' }}
                            </option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <select
                            v-model="statusFilter"
                            @change="applyFilters"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 font-medium"
                        >
                            <option value="">All Statuses</option>
                            <option value="waiting">Waiting in Queue</option>
                            <option value="in_consultation">In Doctor Room</option>
                            <option value="completed">Completed</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>

                    <!-- Patient Search -->
                    <div class="relative">
                        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search token, name, phone..."
                            class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:bg-white"
                        />
                    </div>
                </div>
            </div>

            <!-- Queue Table -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/75 dark:bg-[#21262D]/60 text-[11px] font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider border-b border-slate-200/80 dark:border-[#30363D]">
                            <tr>
                                <th class="py-3 px-4">Token / Serial</th>
                                <th class="py-3 px-4">Patient</th>
                                <th class="py-3 px-4">Visit Type</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Fee & Payment</th>
                                <th class="py-3 px-4 text-right">Consultation Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr
                                v-for="app in appointments"
                                :key="app.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-[#21262D]/40 transition-colors"
                                :class="app.status === 'in_consultation' ? 'bg-teal-50/30 dark:bg-teal-950/20' : ''"
                            >
                                <!-- Token / Serial -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-8 h-8 rounded-xl bg-[#0A0D36] dark:bg-teal-500 text-white dark:text-slate-950 font-mono font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                            {{ String(app.serial_no).padStart(2, '0') }}
                                        </span>
                                        <div>
                                            <p class="font-mono font-bold text-slate-900 dark:text-white">{{ app.token_code }}</p>
                                            <p class="text-[10px] text-slate-400">{{ formatDisplayTime(app.appointment_time) }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Patient Name & Phone -->
                                <td class="py-3 px-4">
                                    <div>
                                        <Link
                                            v-if="app.patient"
                                            :href="route('patients.show', app.patient.id)"
                                            class="font-bold text-slate-900 dark:text-white hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                                        >
                                            {{ app.patient.name }}
                                        </Link>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                            <span>{{ app.patient?.age ? app.patient.age + 'y' : '' }} {{ app.patient?.gender }}</span>
                                            <span v-if="app.patient?.phone">• {{ app.patient.phone }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Visit Type -->
                                <td class="py-3 px-4">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border"
                                        :class="typeLabels[app.type]?.class || 'bg-slate-100 text-slate-700 border-slate-200'"
                                    >
                                        {{ typeLabels[app.type]?.label || app.type }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold border"
                                        :class="statusLabels[app.status]?.class || 'bg-slate-100 text-slate-700 border-slate-200'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ statusLabels[app.status]?.label || app.status }}
                                    </span>
                                </td>

                                <!-- Fee & Payment Status -->
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ app.payable_amount }}</div>
                                    <button
                                        type="button"
                                        @click="togglePayment(app)"
                                        class="inline-flex items-center gap-1 text-[10px] font-black uppercase px-2 py-0.5 rounded-md cursor-pointer transition shadow-2xs border mt-0.5"
                                        :class="app.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800 hover:bg-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800 hover:bg-amber-200'"
                                        :title="app.payment_status === 'paid' ? 'Click to mark as UNPAID' : 'Click to mark as PAID'"
                                    >
                                        <span>{{ app.payment_status }}</span>
                                    </button>
                                </td>

                                <!-- Consultation Actions -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Call to Doctor Room -->
                                        <button
                                            v-if="app.status === 'in_queue' || app.status === 'scheduled'"
                                            type="button"
                                            @click="updateStatus(app, 'in_consultation')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-bold rounded-lg transition-colors cursor-pointer shadow-2xs"
                                        >
                                            <Stethoscope class="w-3.5 h-3.5" /> Call In
                                        </button>

                                        <!-- Currently in Room - Write Prescription -->
                                        <template v-if="app.status === 'in_consultation'">
                                            <Link
                                                :href="route('prescriptions.create', { appointment_id: app.id, patient_id: app.patient_id })"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-[11px] font-bold rounded-lg transition-colors cursor-pointer shadow-2xs"
                                            >
                                                <FileText class="w-3.5 h-3.5" /> Write Rx
                                            </Link>
                                            <button
                                                type="button"
                                                @click="updateStatus(app, 'completed')"
                                                class="p-1.5 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition-colors cursor-pointer"
                                                title="Mark as Completed"
                                            >
                                                <CheckCircle2 class="w-4 h-4" />
                                            </button>
                                        </template>

                                        <!-- Cancel / Delete -->
                                        <button
                                            v-if="app.status !== 'completed'"
                                            type="button"
                                            @click="cancelAppointment(app)"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer"
                                            title="Cancel Appointment"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty Queue State -->
                            <tr v-if="appointments.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <Calendar class="w-8 h-8 mx-auto mb-2 opacity-30" />
                                    <p class="font-medium text-xs">No appointments found for this date & chamber.</p>
                                    <p class="text-[11px] mt-1">Click "+ Book New Appointment" to add a patient to queue.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Book Appointment Modal -->
        <div v-if="showBookModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] rounded-2xl max-w-lg w-full p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#21262D]">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400">
                            <Plus class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Book Patient Appointment / Token</h3>
                    </div>
                    <button
                        type="button"
                        @click="showBookModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitBooking" class="space-y-4 text-xs">
                    <!-- Patient Type Switcher -->
                    <div class="flex items-center p-1 bg-slate-100 dark:bg-[#21262D] rounded-xl">
                        <button
                            type="button"
                            @click="patientMode = 'existing'"
                            class="flex-1 py-1.5 rounded-lg font-bold text-xs transition-colors cursor-pointer"
                            :class="patientMode === 'existing' ? 'bg-white dark:bg-[#161B22] text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500'"
                        >
                            Existing Patient Search
                        </button>
                        <button
                            type="button"
                            @click="patientMode = 'new'"
                            class="flex-1 py-1.5 rounded-lg font-bold text-xs transition-colors cursor-pointer"
                            :class="patientMode === 'new' ? 'bg-white dark:bg-[#161B22] text-slate-900 dark:text-white shadow-2xs' : 'text-slate-500'"
                        >
                            + Quick New Patient
                        </button>
                    </div>

                    <!-- Existing Patient Search Autocomplete -->
                    <div v-if="patientMode === 'existing'" class="space-y-1.5 relative">
                        <label class="block font-medium text-slate-700 dark:text-slate-300">
                            Select Patient <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="patientSearch"
                            type="text"
                            placeholder="Type patient name, phone, or PID..."
                            class="w-full px-3 py-2 bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        />
                        <!-- Dropdown list -->
                        <div v-if="!bookForm.patient_id && patientSearch" class="border border-slate-200 dark:border-[#30363D] bg-white dark:bg-[#161B22] rounded-xl max-h-40 overflow-y-auto custom-scrollbar shadow-md">
                            <button
                                v-for="p in filteredPatients"
                                :key="p.id"
                                type="button"
                                @click="selectPatient(p)"
                                class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-[#21262D] border-b border-slate-100 dark:border-slate-800 last:border-none flex items-center justify-between"
                            >
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ p.name }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ p.patient_code }} • {{ p.phone || 'No phone' }}</p>
                                </div>
                                <span class="text-[10px] text-teal-600 dark:text-teal-400 font-semibold">Select</span>
                            </button>
                        </div>
                    </div>

                    <!-- New Patient Fields -->
                    <div v-if="patientMode === 'new'" class="grid grid-cols-2 gap-3 p-3 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200 dark:border-[#30363D]">
                        <div class="col-span-2">
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Patient Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="bookForm.new_patient_name"
                                type="text"
                                placeholder="Full Name"
                                class="w-full px-3 py-2 bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Phone</label>
                            <input
                                v-model="bookForm.new_patient_phone"
                                type="text"
                                placeholder="e.g. +1 (555) 234-5678"
                                class="w-full px-3 py-2 bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Age</label>
                            <input
                                v-model="bookForm.new_patient_age"
                                type="number"
                                placeholder="Years"
                                class="w-full px-3 py-2 bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl"
                            />
                        </div>
                    </div>

                    <!-- Chamber Selection -->
                    <div class="space-y-1.5">
                        <label class="block font-medium text-slate-700 dark:text-slate-300">
                            Chamber <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="bookForm.chamber_id"
                            required
                            class="w-full px-3 py-2 bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                        >
                            <option v-for="ch in chambers" :key="ch.id" :value="ch.id">
                                {{ ch.name }} {{ ch.branch ? '(' + ch.branch + ')' : '' }}
                            </option>
                        </select>
                        <!-- Chamber Visiting Info Banner -->
                        <div v-if="selectedChamber" class="flex flex-wrap items-center gap-2 pt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                            <span v-if="selectedChamber.visiting_time" class="inline-flex items-center gap-1 text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/40 px-2 py-0.5 rounded-md font-medium border border-teal-200/80 dark:border-teal-800">
                                <Clock class="w-3 h-3 text-teal-600 dark:text-teal-400" />
                                <span>Hours: {{ selectedChamber.visiting_time }}</span>
                            </span>
                            <span v-if="selectedChamber.visiting_days?.length" class="inline-flex items-center gap-1 text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-[#21262D] px-2 py-0.5 rounded-md">
                                <Calendar class="w-3 h-3 text-slate-400" />
                                <span>{{ selectedChamber.visiting_days.join(', ') }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Date & Time Slot -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Appointment Date <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="bookForm.appointment_date"
                                type="date"
                                required
                                class="w-full px-3 py-2 bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Time (24h) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="bookForm.appointment_time"
                                type="time"
                                required
                                class="w-full px-3 py-2 bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-medium"
                            />
                        </div>
                    </div>

                    <!-- Visit Type & Fee -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Visit Type</label>
                            <select
                                v-model="bookForm.type"
                                class="w-full px-3 py-2 bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                            >
                                <option value="new_consultation">New Consultation</option>
                                <option value="follow_up">Follow-up</option>
                                <option value="report_review">Report Review</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Fee</label>
                            <input
                                v-model="bookForm.fee_amount"
                                type="number"
                                placeholder="50"
                                class="w-full px-3 py-2 bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-medium"
                            />
                        </div>
                    </div>

                    <!-- Discount Section -->
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200 dark:border-[#30363D] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Discount Option</label>

                            <!-- Radio buttons: Fixed vs Percentage -->
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="bookForm.discount_type"
                                        value="fixed"
                                        class="text-teal-600 focus:ring-teal-500 rounded-full cursor-pointer"
                                    />
                                    <span :class="bookForm.discount_type === 'fixed' ? 'font-bold text-slate-900 dark:text-white' : ''">Fixed Amount</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 cursor-pointer">
                                    <input
                                        type="radio"
                                        v-model="bookForm.discount_type"
                                        value="percentage"
                                        class="text-teal-600 focus:ring-teal-500 rounded-full cursor-pointer"
                                    />
                                    <span :class="bookForm.discount_type === 'percentage' ? 'font-bold text-slate-900 dark:text-white' : ''">Percentage (%)</span>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 items-center">
                            <div class="relative">
                                <input
                                    v-model="bookForm.discount_value"
                                    type="number"
                                    min="0"
                                    :max="bookForm.discount_type === 'percentage' ? 100 : bookForm.fee_amount"
                                    placeholder="0"
                                    class="w-full px-3 py-1.5 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-medium"
                                />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-bold">
                                    {{ bookForm.discount_type === 'percentage' ? '%' : 'Amt' }}
                                </span>
                            </div>

                            <!-- Net Payable Display Badge -->
                            <div class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200/80 dark:border-teal-800/80">
                                <span class="text-[11px] font-medium text-teal-800 dark:text-teal-300">Net Payable:</span>
                                <span class="text-xs font-bold text-teal-700 dark:text-teal-300 font-mono">{{ calculatedPayable }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Status Selection -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200 dark:border-[#30363D]">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Consultation Fee Status:</span>
                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="bookForm.payment_status"
                                    value="paid"
                                    class="text-emerald-600 focus:ring-emerald-500 rounded-full cursor-pointer"
                                />
                                <span :class="bookForm.payment_status === 'paid' ? 'font-bold text-emerald-700 dark:text-emerald-400' : ''">Paid (Collected)</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="bookForm.payment_status"
                                    value="unpaid"
                                    class="text-amber-600 focus:ring-amber-500 rounded-full cursor-pointer"
                                />
                                <span :class="bookForm.payment_status === 'unpaid' ? 'font-bold text-amber-700 dark:text-amber-400' : ''">Unpaid (Due)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-[#21262D]">
                        <button
                            type="button"
                            @click="showBookModal = false"
                            class="px-3.5 py-1.5 font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-lg cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="bookForm.processing"
                            class="px-4 py-2 font-bold text-white bg-[#0A0D36] dark:bg-teal-500 dark:text-slate-950 hover:bg-[#12164A] rounded-xl shadow-xs cursor-pointer disabled:opacity-50"
                        >
                            <span>{{ bookForm.processing ? 'Booking...' : 'Generate Token & Book' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Vitals Update Modal -->
        <div v-if="showVitalsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] rounded-2xl max-w-sm w-full p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#21262D]">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <HeartPulse class="w-4 h-4 text-rose-500" />
                        Update Vitals: {{ activeAppointmentForVitals?.patient?.name }}
                    </h3>
                    <button type="button" @click="showVitalsModal = false" class="text-slate-400">✕</button>
                </div>

                <form @submit.prevent="submitVitals" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-medium mb-1">Blood Pressure (BP)</label>
                        <input v-model="vitalsForm.vitals.bp" type="text" placeholder="120/80" class="w-full px-3 py-1.5 border rounded-lg dark:bg-[#21262D]" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Weight (kg)</label>
                        <input v-model="vitalsForm.vitals.weight" type="text" placeholder="65" class="w-full px-3 py-1.5 border rounded-lg dark:bg-[#21262D]" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Pulse Rate (bpm)</label>
                        <input v-model="vitalsForm.vitals.pulse" type="text" placeholder="72" class="w-full px-3 py-1.5 border rounded-lg dark:bg-[#21262D]" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Temperature (°F)</label>
                        <input v-model="vitalsForm.vitals.temperature" type="text" placeholder="98.6" class="w-full px-3 py-1.5 border rounded-lg dark:bg-[#21262D]" />
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t">
                        <button type="button" @click="showVitalsModal = false" class="px-3 py-1 text-slate-500">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 bg-teal-600 text-white font-bold rounded-lg">Save Vitals</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
