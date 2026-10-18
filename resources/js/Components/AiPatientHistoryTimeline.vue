<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import {
    Sparkles,
    Activity,
    AlertCircle,
    Pill,
    Calendar,
    ChevronDown,
    ChevronUp,
    FileText,
    Printer,
    Copy,
    Check,
    RotateCcw,
    Stethoscope,
    Building2,
    Clock,
    ShieldAlert,
    ExternalLink
} from 'lucide-vue-next';

const props = defineProps({
    patient: {
        type: Object,
        required: true,
    },
    prescriptions: {
        type: Array,
        default: () => [],
    },
    appointments: {
        type: Array,
        default: () => [],
    },
});

const isLoading = ref(false);
const summaryData = ref(null);
const errorMessage = ref('');
const isCopied = ref(false);
const expandedNodes = ref({});

const toggleNode = (id) => {
    expandedNodes.value[id] = !expandedNodes.value[id];
};

const fetchAiSummary = async () => {
    if (!props.patient?.id) return;
    isLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('ai.patient-history-summary'), {
            patient_id: props.patient.id,
        });

        if (response.data?.success) {
            summaryData.value = response.data.data;
        } else {
            errorMessage.value = response.data?.message || 'Unable to generate AI summary.';
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'AI service temporarily unavailable.';
    } finally {
        isLoading.value = false;
    }
};

const copySummaryText = () => {
    if (!summaryData.value?.summary) return;
    navigator.clipboard.writeText(summaryData.value.summary);
    isCopied.value = true;
    setTimeout(() => {
        isCopied.value = false;
    }, 2000);
};

onMounted(() => {
    if (props.prescriptions && props.prescriptions.length > 0) {
        fetchAiSummary();
    }
});
</script>

<template>
    <div class="space-y-6">
        <!-- ===================================================================== -->
        <!-- AI Longitudinal Summary Card                                          -->
        <!-- ===================================================================== -->
        <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
            <!-- Header -->
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-[#21262D] bg-gradient-to-r from-teal-50/50 via-cyan-50/20 to-transparent dark:from-teal-950/20 dark:via-cyan-950/10 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-600 to-cyan-500 text-white flex items-center justify-center shadow-md shadow-teal-600/20 shrink-0">
                        <Sparkles class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                AI Longitudinal Medical History Summary
                            </h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-500/20 text-teal-700 dark:text-teal-300">
                                Smart Timeline
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">
                            Synthesized clinical trajectory across {{ prescriptions.length }} prescription records
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        v-if="summaryData"
                        type="button"
                        @click="copySummaryText"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-[#21262D] hover:bg-slate-50 dark:hover:bg-[#30363D] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-[#30363D] text-xs font-semibold transition cursor-pointer shadow-2xs"
                        title="Copy Summary to Clipboard"
                    >
                        <Check v-if="isCopied" class="w-3.5 h-3.5 text-emerald-600" />
                        <Copy v-else class="w-3.5 h-3.5" />
                        <span>{{ isCopied ? 'Copied!' : 'Copy Summary' }}</span>
                    </button>

                    <button
                        type="button"
                        @click="fetchAiSummary"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition cursor-pointer"
                    >
                        <RotateCcw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
                        <span>{{ isLoading ? 'Synthesizing...' : 'Regenerate Summary' }}</span>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-5 sm:p-6 space-y-5">
                <!-- Loading State -->
                <div v-if="isLoading" class="py-8 flex flex-col items-center justify-center gap-3 text-center">
                    <div class="w-8 h-8 rounded-full border-2 border-teal-500 border-t-transparent animate-spin"></div>
                    <p class="text-xs font-medium text-slate-600 dark:text-slate-300">
                        AI Clinical Copilot is analyzing longitudinal patient visits, diagnoses, and medication responses...
                    </p>
                </div>

                <!-- Error State -->
                <div v-else-if="errorMessage" class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-xs flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <AlertCircle class="w-4 h-4 shrink-0 text-amber-600" />
                        <span>{{ errorMessage }}</span>
                    </div>
                    <button
                        type="button"
                        @click="fetchAiSummary"
                        class="underline font-bold text-amber-900 dark:text-amber-100 cursor-pointer"
                    >
                        Retry
                    </button>
                </div>

                <!-- Summary Content -->
                <div v-else-if="summaryData" class="space-y-5">
                    <!-- High-level narrative -->
                    <div class="p-4 rounded-2xl bg-slate-50/80 dark:bg-[#11141A] border border-slate-200/80 dark:border-[#21262D] space-y-2">
                        <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-teal-700 dark:text-teal-400">
                            <span class="flex items-center gap-1.5">
                                <Stethoscope class="w-3.5 h-3.5" />
                                Clinical Trajectory Overview
                            </span>
                            <span class="text-slate-400 font-normal">
                                First Visit: {{ summaryData.first_visit }} • Latest: {{ summaryData.latest_visit }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-700 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                            {{ summaryData.summary }}
                        </div>
                    </div>

                    <!-- 3-Column Highlights Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <!-- Chronic Conditions -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#1E2633]/60 border border-slate-200/70 dark:border-[#30363D] space-y-2">
                            <div class="flex items-center gap-1.5 font-bold text-indigo-700 dark:text-indigo-400">
                                <Activity class="w-3.5 h-3.5" />
                                <span>Chronic Illnesses</span>
                            </div>
                            <div v-if="summaryData.chronic_conditions?.length > 0" class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="(cond, idx) in summaryData.chronic_conditions"
                                    :key="idx"
                                    class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800"
                                >
                                    {{ cond }}
                                </span>
                            </div>
                            <p v-else class="text-[11px] text-slate-400 italic">No chronic illnesses identified.</p>
                        </div>

                        <!-- Active Medications -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#1E2633]/60 border border-slate-200/70 dark:border-[#30363D] space-y-2">
                            <div class="flex items-center gap-1.5 font-bold text-teal-700 dark:text-teal-400">
                                <Pill class="w-3.5 h-3.5" />
                                <span>Active Medications (Rx)</span>
                            </div>
                            <div v-if="summaryData.active_medications?.length > 0" class="space-y-1">
                                <div
                                    v-for="(drug, idx) in summaryData.active_medications.slice(0, 3)"
                                    :key="idx"
                                    class="text-[11px] font-medium text-slate-700 dark:text-slate-300 flex items-center justify-between"
                                >
                                    <span class="truncate">{{ drug.name }} {{ drug.strength }}</span>
                                    <span class="text-[10px] text-teal-600 dark:text-teal-400 font-mono">{{ drug.dose }}</span>
                                </div>
                                <p v-if="summaryData.active_medications.length > 3" class="text-[10px] text-slate-400 font-semibold">
                                    +{{ summaryData.active_medications.length - 3 }} more medications
                                </p>
                            </div>
                            <p v-else class="text-[11px] text-slate-400 italic">No active medications.</p>
                        </div>

                        <!-- Critical Allergies & Risks -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#1E2633]/60 border border-slate-200/70 dark:border-[#30363D] space-y-2">
                            <div class="flex items-center gap-1.5 font-bold text-rose-700 dark:text-rose-400">
                                <ShieldAlert class="w-3.5 h-3.5" />
                                <span>Allergies & Cautions</span>
                            </div>
                            <p class="text-[11px] text-slate-700 dark:text-slate-300">
                                {{ summaryData.allergies || 'No known drug allergies reported.' }}
                            </p>
                        </div>
                    </div>

                    <!-- AI Clinical Recommendations -->
                    <div v-if="summaryData.key_recommendations?.length > 0" class="p-3.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/70 dark:border-emerald-800/60 space-y-1.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-400 flex items-center gap-1.5">
                            <Sparkles class="w-3 h-3" />
                            AI Clinical Focus for Next Consultation:
                        </p>
                        <ul class="text-xs text-emerald-900 dark:text-emerald-200 space-y-1 list-disc list-inside">
                            <li v-for="(rec, idx) in summaryData.key_recommendations" :key="idx">
                                {{ rec }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Empty State (No Prescriptions Yet) -->
                <div v-else class="py-8 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-[#21262D] text-slate-400 flex items-center justify-center mx-auto">
                        <FileText class="w-6 h-6" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">No Historical Prescriptions Yet</h3>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">
                            Once you create prescriptions for this patient, the AI will automatically track their longitudinal timeline and drug responses.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- Chronological Longitudinal Timeline View                              -->
        <!-- ===================================================================== -->
        <div class="bg-white dark:bg-[#161B22] p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#21262D]">
                <div class="flex items-center gap-2">
                    <Calendar class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Consultation & Prescription Timeline
                    </h3>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300">
                        {{ prescriptions.length }} Visits
                    </span>
                </div>

                <Link
                    :href="route('prescriptions.create', { patient_id: patient.id })"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] text-white dark:text-slate-950 text-xs font-bold rounded-xl transition cursor-pointer shadow-xs"
                >
                    <FileText class="w-3.5 h-3.5" />
                    <span>New Prescription</span>
                </Link>
            </div>

            <!-- Vertical Timeline Track -->
            <div v-if="prescriptions.length > 0" class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-[#30363D]">
                <div
                    v-for="(rx, index) in prescriptions"
                    :key="rx.id"
                    class="relative group"
                >
                    <!-- Timeline Dot -->
                    <div
                        class="absolute -left-6 top-1.5 w-5 h-5 rounded-full border-2 bg-white dark:bg-[#161B22] flex items-center justify-center transition-colors"
                        :class="index === 0 ? 'border-teal-600 text-teal-600' : 'border-slate-300 dark:border-slate-600 text-slate-400'"
                    >
                        <div class="w-2 h-2 rounded-full" :class="index === 0 ? 'bg-teal-600 animate-pulse' : 'bg-slate-300 dark:bg-slate-600'"></div>
                    </div>

                    <!-- Timeline Card -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#1E2633]/50 border border-slate-200/80 dark:border-[#30363D] space-y-3 hover:border-teal-300 dark:hover:border-teal-700 transition">
                        <!-- Card Header -->
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">
                                    {{ rx.prescription_date ? new Date(rx.prescription_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Consultation' }}
                                </span>
                                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-white dark:bg-[#21262D] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-[#30363D]">
                                    {{ rx.prescription_code }}
                                </span>
                                <span v-if="rx.chamber?.name" class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                                    <Building2 class="w-3 h-3" />
                                    {{ rx.chamber.name }}
                                </span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <a
                                    :href="route('prescriptions.print', rx.id)"
                                    target="_blank"
                                    class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-[#30363D] text-slate-500 hover:text-slate-900 transition"
                                    title="Print Prescription"
                                >
                                    <Printer class="w-3.5 h-3.5" />
                                </a>
                                <Link
                                    :href="route('prescriptions.edit', rx.id)"
                                    class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-[#30363D] text-slate-500 hover:text-slate-900 transition"
                                    title="Edit Prescription"
                                >
                                    <ExternalLink class="w-3.5 h-3.5" />
                                </Link>
                                <button
                                    type="button"
                                    @click="toggleNode(rx.id)"
                                    class="p-1.5 rounded-lg hover:bg-slate-200 dark:hover:bg-[#30363D] text-slate-500 hover:text-slate-900 transition cursor-pointer"
                                >
                                    <ChevronUp v-if="expandedNodes[rx.id]" class="w-3.5 h-3.5" />
                                    <ChevronDown v-else class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Diagnoses Tags -->
                        <div v-if="rx.diagnoses && rx.diagnoses.length > 0" class="flex flex-wrap gap-1.5">
                            <span
                                v-for="(diag, dIdx) in rx.diagnoses"
                                :key="dIdx"
                                class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800"
                            >
                                {{ typeof diag === 'object' ? (diag.diagnosis || diag.name) : diag }}
                            </span>
                        </div>

                        <!-- Chief Complaints -->
                        <div v-if="rx.chief_complaints && rx.chief_complaints.length > 0" class="text-xs text-slate-600 dark:text-slate-400">
                            <strong>Symptoms:</strong>
                            {{ rx.chief_complaints.map(c => typeof c === 'object' ? (c.complaint || c.name) : c).join(', ') }}
                        </div>

                        <!-- Expandable Medicines List -->
                        <div v-if="expandedNodes[rx.id] || index === 0" class="pt-2 border-t border-slate-200/60 dark:border-[#30363D] space-y-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Prescribed Medications ({{ rx.items?.length || 0 }}):
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div
                                    v-for="it in rx.items"
                                    :key="it.id"
                                    class="p-2 rounded-xl bg-white dark:bg-[#161B22] border border-slate-200/70 dark:border-[#21262D] text-xs space-y-0.5"
                                >
                                    <div class="flex items-center justify-between font-bold text-slate-800 dark:text-slate-200">
                                        <span class="truncate">{{ it.type }} {{ it.name }} {{ it.strength }}</span>
                                        <span class="text-[10px] text-teal-600 dark:text-teal-400 font-mono">{{ it.dose }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 flex items-center justify-between">
                                        <span>{{ it.duration || 'Standard course' }}</span>
                                        <span v-if="it.advice">{{ it.advice }}</span>
                                    </div>
                                </div>
                            </div>

                            <div v-if="rx.follow_up_date" class="text-[11px] text-slate-500 dark:text-slate-400 pt-1 flex items-center gap-1.5">
                                <Clock class="w-3 h-3 text-teal-600" />
                                <span><strong>Follow-up:</strong> {{ rx.follow_up_date }} {{ rx.follow_up_notes ? '(' + rx.follow_up_notes + ')' : '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="py-6 text-center text-xs text-slate-400">
                No past consultations recorded for this patient.
            </div>
        </div>
    </div>
</template>