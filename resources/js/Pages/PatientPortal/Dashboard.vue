<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    HeartPulse,
    FileText,
    Calendar,
    Printer,
    User,
    Phone,
    Mail,
    MapPin,
    Clock,
    AlertCircle,
    Activity,
    Pill,
    CheckCircle2,
    Shield,
    Stethoscope,
    Building2,
    ChevronRight,
    ExternalLink
} from 'lucide-vue-next';

const props = defineProps({
    patient: {
        type: Object,
        default: () => ({}),
    },
    prescriptions: {
        type: Array,
        default: () => [],
    },
    appointments: {
        type: Array,
        default: () => [],
    },
    upcomingAppointment: {
        type: Object,
        default: () => null,
    },
});

const activeTab = ref('prescriptions'); // 'prescriptions' | 'appointments' | 'profile'
</script>

<template>
    <Head title="Patient Health Portal - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6 pb-16">
            
            <!-- Welcome Patient Profile Header Banner -->
            <div class="bg-gradient-to-r from-teal-600 via-emerald-600 to-cyan-700 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
                <!-- Background ambient decorative circles -->
                <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-start sm:items-center gap-4">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/20 backdrop-blur-md text-white font-black text-2xl sm:text-3xl flex items-center justify-center shrink-0 border border-white/30 shadow-inner">
                            {{ patient.name ? patient.name.charAt(0).toUpperCase() : 'P' }}
                        </div>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-xl sm:text-2xl font-black tracking-tight">
                                    {{ patient.name }}
                                </h1>
                                <span class="px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-xs text-white text-[11px] font-bold border border-white/30">
                                    {{ patient.patient_code }}
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-teal-100 flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span v-if="patient.age">{{ patient.age }} Years</span>
                                <span v-if="patient.gender">• {{ patient.gender }}</span>
                                <span v-if="patient.blood_group">• Blood Group: <strong class="text-white">{{ patient.blood_group }}</strong></span>
                            </p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <Link
                            :href="route('profile.edit')"
                            class="px-4 py-2.5 rounded-xl bg-white text-teal-900 font-bold text-xs shadow-md hover:bg-teal-50 transition cursor-pointer flex items-center gap-2"
                        >
                            <User class="w-4 h-4 text-teal-700" />
                            <span>My Profile & Password</span>
                        </Link>
                    </div>
                </div>

                <!-- Emergency Contact & Allergies Bar -->
                <div class="mt-6 pt-5 border-t border-white/20 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                    <div class="flex items-center gap-2.5 bg-white/10 backdrop-blur-xs p-2.5 rounded-xl border border-white/15">
                        <AlertCircle class="w-4 h-4 text-amber-300 shrink-0" />
                        <div class="truncate">
                            <p class="text-[10px] text-teal-100 uppercase tracking-wider font-semibold">Known Allergies</p>
                            <p class="font-bold text-white truncate">{{ patient.allergies || 'No known allergies' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 bg-white/10 backdrop-blur-xs p-2.5 rounded-xl border border-white/15">
                        <Activity class="w-4 h-4 text-cyan-200 shrink-0" />
                        <div class="truncate">
                            <p class="text-[10px] text-teal-100 uppercase tracking-wider font-semibold">Medical History</p>
                            <p class="font-bold text-white truncate">{{ patient.medical_history || 'None recorded' }}</p>
                        </div>
                    </div>

                    <div v-if="patient.emergency_contact_phone" class="flex items-center gap-2.5 bg-white/10 backdrop-blur-xs p-2.5 rounded-xl border border-white/15 sm:col-span-2 md:col-span-1">
                        <Phone class="w-4 h-4 text-emerald-200 shrink-0" />
                        <div class="truncate">
                            <p class="text-[10px] text-teal-100 uppercase tracking-wider font-semibold">Emergency Contact</p>
                            <p class="font-bold text-white truncate">{{ patient.emergency_contact_name || 'Contact' }}: {{ patient.emergency_contact_phone }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Summary Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Total Digital Prescriptions -->
                <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold shrink-0">
                        <FileText class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Digital Prescriptions</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ prescriptions.length }}
                        </h3>
                        <p class="text-[10px] text-teal-600 dark:text-teal-400 font-medium mt-0.5">
                            {{ prescriptions.length > 0 ? `Latest: ${prescriptions[0].prescription_date}` : 'No prescriptions yet' }}
                        </p>
                    </div>
                </div>

                <!-- Total Appointments -->
                <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shrink-0">
                        <Calendar class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Consultations</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ appointments.length }}
                        </h3>
                        <p class="text-[10px] text-indigo-600 dark:text-indigo-400 font-medium mt-0.5">
                            {{ upcomingAppointment ? `Next on ${upcomingAppointment.appointment_date}` : 'No upcoming queue' }}
                        </p>
                    </div>
                </div>

                <!-- Active Token / Next Visit -->
                <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex items-center gap-4 sm:col-span-2 lg:col-span-1">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold shrink-0">
                        <Stethoscope class="w-6 h-6" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Queue & Token Status</p>
                        <h3 class="text-base font-black text-slate-900 dark:text-white truncate">
                            {{ upcomingAppointment ? upcomingAppointment.token_code : 'No Active Token' }}
                        </h3>
                        <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium mt-0.5 truncate">
                            {{ upcomingAppointment ? `${upcomingAppointment.chamber_name} (${upcomingAppointment.status_label})` : 'All consultations up-to-date' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Portal Tabs -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <!-- Navigation Tabs Bar -->
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-[#21262D] px-5 pt-3">
                    <button
                        type="button"
                        @click="activeTab = 'prescriptions'"
                        class="pb-3 px-3 text-xs font-bold transition-all relative cursor-pointer flex items-center gap-2"
                        :class="activeTab === 'prescriptions' ? 'text-teal-600 dark:text-teal-400 border-b-2 border-teal-600' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                    >
                        <FileText class="w-4 h-4" />
                        <span>My Prescriptions ({{ prescriptions.length }})</span>
                    </button>

                    <button
                        type="button"
                        @click="activeTab = 'appointments'"
                        class="pb-3 px-3 text-xs font-bold transition-all relative cursor-pointer flex items-center gap-2"
                        :class="activeTab === 'appointments' ? 'text-teal-600 dark:text-teal-400 border-b-2 border-teal-600' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'"
                    >
                        <Calendar class="w-4 h-4" />
                        <span>My Appointments & Queue ({{ appointments.length }})</span>
                    </button>
                </div>

                <!-- Tab 1: Prescriptions List -->
                <div v-if="activeTab === 'prescriptions'" class="p-5 sm:p-6 space-y-4">
                    <div v-if="prescriptions.length > 0" class="space-y-4">
                        <div
                            v-for="rx in prescriptions"
                            :key="rx.id"
                            class="bg-slate-50/70 dark:bg-[#21262D]/40 rounded-2xl p-5 border border-slate-200/80 dark:border-[#30363D] hover:border-teal-300 dark:hover:border-teal-700 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4"
                        >
                            <div class="space-y-2 max-w-2xl">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-black text-slate-900 dark:text-white font-mono bg-white dark:bg-[#161B22] px-2.5 py-1 rounded-lg border border-slate-200 dark:border-[#30363D]">
                                        {{ rx.prescription_code }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                        {{ rx.prescription_date }}
                                    </span>
                                </div>

                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <span>{{ rx.doctor_title }} {{ rx.doctor_name }}</span>
                                        <span class="text-xs text-teal-600 font-normal">({{ rx.doctor_specialty }})</span>
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        Chamber: <strong class="text-slate-700 dark:text-slate-300">{{ rx.chamber_name }}</strong>
                                    </p>
                                </div>

                                <!-- Diagnoses & Medicines Snippet -->
                                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                                    <span class="px-2 py-0.5 rounded-md bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-semibold border border-teal-200/60 dark:border-teal-800">
                                        Dx: {{ rx.diagnoses }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-semibold border border-indigo-200/60 dark:border-indigo-800 flex items-center gap-1">
                                        <Pill class="w-3 h-3" />
                                        <span>{{ rx.medicines_count }} Prescribed Medication(s)</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Print / View Rx Action -->
                            <div class="flex items-center gap-2 shrink-0">
                                <a
                                    :href="rx.print_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer"
                                >
                                    <Printer class="w-4 h-4" />
                                    <span>View & Print Prescription</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-12 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 mx-auto flex items-center justify-center font-bold">
                            <FileText class="w-6 h-6" />
                        </div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">No Prescriptions Yet</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                            When your consulting doctor writes a digital prescription for you, it will immediately appear here for viewing and printing.
                        </p>
                    </div>
                </div>

                <!-- Tab 2: Appointments & Queue -->
                <div v-if="activeTab === 'appointments'" class="p-5 sm:p-6 space-y-4">
                    <div v-if="appointments.length > 0" class="space-y-4">
                        <div
                            v-for="appt in appointments"
                            :key="appt.id"
                            class="bg-slate-50/70 dark:bg-[#21262D]/40 rounded-2xl p-5 border border-slate-200/80 dark:border-[#30363D] flex flex-col md:flex-row md:items-center justify-between gap-4"
                        >
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-black text-indigo-700 dark:text-indigo-300 font-mono bg-indigo-50 dark:bg-indigo-950/50 px-2.5 py-1 rounded-lg border border-indigo-200 dark:border-indigo-800">
                                        Token: {{ appt.token_code }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                        {{ appt.appointment_date }} {{ appt.appointment_time ? `at ${appt.appointment_time}` : '' }}
                                    </span>
                                    <span
                                        class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
                                        :class="[
                                            appt.status === 'completed'
                                                ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800'
                                                : (appt.status === 'in_progress'
                                                    ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800 animate-pulse'
                                                    : 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800')
                                        ]"
                                    >
                                        {{ appt.status_label }}
                                    </span>
                                </div>

                                <div>
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ appt.doctor_name }} <span class="text-xs text-slate-400 font-normal">({{ appt.doctor_specialty }})</span>
                                    </h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1.5">
                                        <Building2 class="w-3.5 h-3.5 text-slate-400" />
                                        <span>{{ appt.chamber_name }} — {{ appt.chamber_address }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-12 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 mx-auto flex items-center justify-center font-bold">
                            <Calendar class="w-6 h-6" />
                        </div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">No Appointment History</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                            You currently have no scheduled or past appointments on file.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>