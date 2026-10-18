<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    FileText,
    Plus,
    Search,
    Printer,
    Eye,
    Calendar,
    Building2,
    Users,
    Trash2,
    Edit3,
    Pill,
    Stethoscope,
    Filter,
    ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    prescriptions: Object,
    chambers: Array,
    selectedChamberId: Number,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters?.search || '');
const chamberId = ref(props.selectedChamberId || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

const applyFilters = () => {
    router.get(
        route('prescriptions.index'),
        {
            search: search.value,
            chamber_id: chamberId.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilters = () => {
    search.value = '';
    chamberId.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

const deletePrescription = (prescription) => {
    if (confirm(`Are you sure you want to delete prescription ${prescription.prescription_code} for ${prescription.patient?.name}?`)) {
        router.delete(route('prescriptions.destroy', prescription.id));
    }
};

const statusClasses = {
    finalized: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
    draft: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
    archived: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
};
</script>

<template>
    <Head title="Prescriptions (Rx) - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header & Action -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <FileText class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        Prescriptions & Rx Pad
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage patient prescriptions, interactive clinical writer, and instant print templates.
                    </p>
                </div>

                <Link
                    :href="route('prescriptions.create')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
                >
                    <Plus class="w-4 h-4" />
                    <span>+ Write New Prescription</span>
                </Link>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Prescriptions</p>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xl font-bold text-slate-900 dark:text-white">{{ stats.total }}</p>
                        <FileText class="w-5 h-5 text-slate-400" />
                    </div>
                </div>
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-teal-600 dark:text-teal-400">Prescriptions Today</p>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xl font-bold text-teal-600 dark:text-teal-400">{{ stats.today }}</p>
                        <Calendar class="w-5 h-5 text-teal-500" />
                    </div>
                </div>
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">Total Patients Treated</p>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.patients }}</p>
                        <Users class="w-5 h-5 text-emerald-500" />
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div class="relative">
                        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search RX code, patient, phone..."
                            class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:bg-white"
                        />
                    </div>

                    <!-- Chamber Filter -->
                    <div>
                        <select
                            v-model="chamberId"
                            @change="applyFilters"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 font-medium"
                        >
                            <option value="">All Chambers</option>
                            <option v-for="ch in chambers" :key="ch.id" :value="ch.id">
                                {{ ch.name }} {{ ch.branch ? '(' + ch.branch + ')' : '' }}
                            </option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <input
                            v-model="dateFrom"
                            @change="applyFilters"
                            type="date"
                            placeholder="From date"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 font-medium"
                        />
                    </div>

                    <!-- Date To -->
                    <div>
                        <input
                            v-model="dateTo"
                            @change="applyFilters"
                            type="date"
                            placeholder="To date"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500 font-medium"
                        />
                    </div>
                </div>
            </div>

            <!-- Prescriptions Table -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/75 dark:bg-[#21262D]/60 text-[11px] font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider border-b border-slate-200/80 dark:border-[#30363D]">
                            <tr>
                                <th class="py-3 px-4">RX Code & Date</th>
                                <th class="py-3 px-4">Patient</th>
                                <th class="py-3 px-4">Clinical Diagnosis</th>
                                <th class="py-3 px-4">Medications</th>
                                <th class="py-3 px-4">Chamber</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr
                                v-for="rx in prescriptions.data"
                                :key="rx.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-[#21262D]/40 transition-colors"
                            >
                                <!-- RX Code & Date -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 flex items-center justify-center font-bold text-xs shrink-0">
                                            Rx
                                        </div>
                                        <div>
                                            <Link
                                                :href="route('prescriptions.show', rx.id)"
                                                class="font-mono font-bold text-slate-900 dark:text-white hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                                            >
                                                {{ rx.prescription_code }}
                                            </Link>
                                            <p class="text-[10px] text-slate-400">{{ rx.prescription_date }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Patient -->
                                <td class="py-3 px-4">
                                    <div>
                                        <Link
                                            v-if="rx.patient"
                                            :href="route('patients.show', rx.patient.id)"
                                            class="font-bold text-slate-900 dark:text-white hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                                        >
                                            {{ rx.patient.name }}
                                        </Link>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                            <span>{{ rx.patient?.age ? rx.patient.age + 'y' : '' }} {{ rx.patient?.gender }}</span>
                                            <span v-if="rx.patient?.phone">• {{ rx.patient.phone }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Diagnosis -->
                                <td class="py-3 px-4 max-w-xs">
                                    <div v-if="rx.diagnoses?.length" class="flex flex-wrap gap-1">
                                        <span
                                            v-for="(d, idx) in rx.diagnoses.slice(0, 2)"
                                            :key="idx"
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800"
                                        >
                                            {{ d.diagnosis }}
                                        </span>
                                        <span v-if="rx.diagnoses.length > 2" class="text-[10px] text-slate-400 font-medium">
                                            +{{ rx.diagnoses.length - 2 }} more
                                        </span>
                                    </div>
                                    <span v-else class="text-[11px] text-slate-400 italic">No diagnosis recorded</span>
                                </td>

                                <!-- Medications Count -->
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300">
                                        <Pill class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                                        <span>{{ rx.items?.length || 0 }} Medicines</span>
                                    </span>
                                </td>

                                <!-- Chamber -->
                                <td class="py-3 px-4">
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                                        {{ rx.chamber?.name || 'Main Chamber' }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-4">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border capitalize"
                                        :class="statusClasses[rx.status] || 'bg-slate-100 text-slate-700 border-slate-200'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                        {{ rx.status }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Print Action -->
                                        <Link
                                            :href="route('prescriptions.print', rx.id)"
                                            class="p-1.5 text-slate-500 hover:text-teal-600 hover:bg-teal-50 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                            title="Print Prescription Pad"
                                        >
                                            <Printer class="w-4 h-4" />
                                        </Link>

                                        <!-- View Action -->
                                        <Link
                                            :href="route('prescriptions.show', rx.id)"
                                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                            title="View Prescription"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>

                                        <!-- Edit Action -->
                                        <Link
                                            :href="route('prescriptions.edit', rx.id)"
                                            class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                            title="Edit Prescription"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </Link>

                                        <!-- Delete Action -->
                                        <button
                                            type="button"
                                            @click="deletePrescription(rx)"
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                            title="Delete Prescription"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="!prescriptions.data?.length">
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <FileText class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                                    <p class="font-medium text-slate-600 dark:text-slate-400">No prescriptions found</p>
                                    <p class="text-xs mt-1">Start writing a new prescription for your patients.</p>
                                    <Link
                                        :href="route('prescriptions.create')"
                                        class="inline-flex items-center gap-1.5 mt-3 px-3.5 py-1.5 bg-[#0A0D36] dark:bg-teal-500 text-white dark:text-slate-950 text-xs font-bold rounded-xl"
                                    >
                                        <Plus class="w-3.5 h-3.5" />
                                        <span>Write First Prescription</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="prescriptions.links?.length > 3" class="px-4 py-3 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Showing <span class="font-medium">{{ prescriptions.from || 0 }}</span> to <span class="font-medium">{{ prescriptions.to || 0 }}</span> of <span class="font-medium">{{ prescriptions.total }}</span> prescriptions
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="(link, key) in prescriptions.links" :key="key">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-2.5 py-1 text-xs rounded-lg transition-colors"
                                :class="link.active ? 'bg-[#0A0D36] dark:bg-teal-500 text-white dark:text-slate-950 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D]'"
                            />
                            <span
                                v-else
                                v-html="link.label"
                                class="px-2.5 py-1 text-xs text-slate-400"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
