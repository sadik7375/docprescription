<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import {
    ShieldAlert,
    ShieldCheck,
    AlertTriangle,
    Sparkles,
    RefreshCw,
    Info,
    ChevronDown,
    ChevronUp,
    CheckCircle2,
    X,
    ExternalLink
} from 'lucide-vue-next';

const props = defineProps({
    drugs: {
        type: Array,
        default: () => []
    },
    patient: {
        type: Object,
        default: () => ({})
    },
    diagnoses: {
        type: Array,
        default: () => []
    }
});

const isChecking = ref(false);
const isExpanded = ref(false);
const showModal = ref(false);
const checkResult = ref(null);
const lastCheckedDrugCount = ref(0);

// Debounced auto-checker
let debounceTimeout = null;

const runInteractionCheck = async (force = false) => {
    if (!props.drugs || props.drugs.length === 0) {
        checkResult.value = null;
        return;
    }

    if (!force && props.drugs.length === lastCheckedDrugCount.value && checkResult.value) {
        return;
    }

    isChecking.value = true;

    try {
        const response = await axios.post(route('ai.check-interactions'), {
            drugs: props.drugs,
            patient: props.patient,
            diagnoses: props.diagnoses,
        });

        checkResult.value = response.data;
        lastCheckedDrugCount.value = props.drugs.length;
    } catch (error) {
        console.error('AI Interaction check error:', error);
    } finally {
        isChecking.value = false;
    }
};

watch(
    () => props.drugs,
    (newDrugs) => {
        if (debounceTimeout) clearTimeout(debounceTimeout);
        if (newDrugs && newDrugs.length >= 2) {
            debounceTimeout = setTimeout(() => {
                runInteractionCheck(true);
            }, 600);
        } else if (newDrugs && newDrugs.length === 1) {
            checkResult.value = {
                status: 'safe',
                summary: '1 medication prescribed. No drug combinations to cross-reference.',
                interactions: [],
                duplicate_therapies: [],
                allergy_warnings: [],
            };
        } else {
            checkResult.value = null;
        }
    },
    { deep: true }
);

const severityConfig = computed(() => {
    if (!checkResult.value) return null;

    if (checkResult.value.status === 'severe') {
        return {
            bg: 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900',
            text: 'text-rose-900 dark:text-rose-200',
            badgeBg: 'bg-rose-600 text-white',
            badgeText: 'Severe Interaction Alert',
            icon: ShieldAlert,
            iconColor: 'text-rose-600 dark:text-rose-400',
        };
    }

    if (checkResult.value.status === 'moderate') {
        return {
            bg: 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900',
            text: 'text-amber-900 dark:text-amber-200',
            badgeBg: 'bg-amber-500 text-slate-950 font-bold',
            badgeText: 'Moderate Interaction Warning',
            icon: AlertTriangle,
            iconColor: 'text-amber-600 dark:text-amber-400',
        };
    }

    return {
        bg: 'bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-900/60',
        text: 'text-emerald-900 dark:text-emerald-200',
        badgeBg: 'bg-emerald-600 text-white',
        badgeText: 'AI Safety Verified',
        icon: ShieldCheck,
        iconColor: 'text-emerald-600 dark:text-emerald-400',
    };
});
</script>

<template>
    <div v-if="drugs && drugs.length > 0" class="transition-all duration-200">
        <!-- AI Interaction Alert Bar -->
        <div
            v-if="checkResult"
            :class="[
                'p-3.5 rounded-2xl border transition-all shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3',
                severityConfig?.bg
            ]"
        >
            <div class="flex items-start sm:items-center gap-3">
                <div class="p-2 rounded-xl bg-white dark:bg-[#161B22] shadow-2xs shrink-0">
                    <component
                        :is="severityConfig?.icon || ShieldCheck"
                        :class="['w-5 h-5', severityConfig?.iconColor]"
                    />
                </div>
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span :class="['px-2 py-0.5 rounded-md text-[10px] uppercase font-black tracking-wider', severityConfig?.badgeBg]">
                            {{ severityConfig?.badgeText }}
                        </span>
                        <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400">
                            {{ checkResult.checked_count || drugs.length }} Medications Checked
                        </span>
                        <span v-if="checkResult.engine" class="hidden md:inline-flex items-center gap-1 text-[10px] text-teal-700 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/50 px-1.5 py-0.5 rounded border border-teal-200 dark:border-teal-800">
                            <Sparkles class="w-3 h-3" />
                            <span>{{ checkResult.engine.includes('Live') ? 'GPT-4o' : 'Pharmacology AI' }}</span>
                        </span>
                    </div>
                    <p :class="['text-xs font-semibold leading-tight', severityConfig?.text]">
                        {{ checkResult.summary }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                <!-- Detailed Findings Modal Trigger -->
                <button
                    v-if="checkResult.interactions?.length > 0 || checkResult.duplicate_therapies?.length > 0 || checkResult.allergy_warnings?.length > 0"
                    type="button"
                    @click="showModal = true"
                    class="px-3 py-1.5 rounded-xl bg-white dark:bg-[#161B22] hover:bg-slate-50 dark:hover:bg-[#21262D] text-xs font-bold shadow-2xs border border-slate-200 dark:border-[#30363D] text-slate-800 dark:text-slate-100 transition-colors cursor-pointer flex items-center gap-1.5"
                >
                    <span>View Breakdown ({{ (checkResult.interactions?.length || 0) + (checkResult.duplicate_therapies?.length || 0) + (checkResult.allergy_warnings?.length || 0) }})</span>
                </button>

                <!-- Refresh / Re-check button -->
                <button
                    type="button"
                    @click="runInteractionCheck(true)"
                    :disabled="isChecking"
                    class="p-2 rounded-xl bg-white dark:bg-[#161B22] hover:bg-slate-50 dark:hover:bg-[#21262D] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-[#30363D] transition-colors cursor-pointer shadow-2xs"
                    title="Re-run AI Pharmacology Analysis"
                >
                    <RefreshCw class="w-4 h-4" :class="{ 'animate-spin text-teal-600': isChecking }" />
                </button>
            </div>
        </div>

        <!-- Initial Placeholder when only 1 item exists -->
        <div
            v-else-if="drugs.length === 1"
            class="p-3 rounded-2xl bg-slate-50 dark:bg-[#161B22] border border-slate-200/60 dark:border-[#21262D] flex items-center justify-between text-xs text-slate-500"
        >
            <div class="flex items-center gap-2">
                <Sparkles class="w-4 h-4 text-teal-600" />
                <span>AI Pharmacology Engine is active. Add more medications to enable real-time cross-interaction analysis.</span>
            </div>
        </div>

        <!-- Detailed Modal for Findings -->
        <div
            v-if="showModal && checkResult"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs animate-fadeIn"
            @click.self="showModal = false"
        >
            <div class="w-full max-w-2xl bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200 dark:border-[#30363D] shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
                <!-- Modal Header -->
                <div class="p-5 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between bg-slate-50/50 dark:bg-[#1A202C]">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 border border-teal-200 dark:border-teal-800">
                            <ShieldAlert class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                AI Drug Interaction & Safety Report
                            </h3>
                            <p class="text-[11px] text-slate-400">
                                Comprehensive analysis for {{ drugs.length }} prescribed clinical medications.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="showModal = false"
                        class="p-1.5 rounded-xl hover:bg-slate-200 dark:hover:bg-[#21262D] text-slate-400 hover:text-slate-700 transition cursor-pointer"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-5 overflow-y-auto space-y-4 custom-scrollbar">
                    <!-- 1. Drug-to-Drug Interactions -->
                    <div v-if="checkResult.interactions && checkResult.interactions.length > 0" class="space-y-3">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <ShieldAlert class="w-3.5 h-3.5 text-rose-500" />
                            <span>Pharmacological Drug Interactions</span>
                        </h4>

                        <div
                            v-for="(interaction, idx) in checkResult.interactions"
                            :key="idx"
                            :class="[
                                'p-4 rounded-2xl border space-y-2',
                                interaction.severity === 'severe'
                                    ? 'bg-rose-50/70 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900'
                                    : 'bg-amber-50/70 dark:bg-amber-950/30 border-amber-200 dark:border-amber-900'
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-900 dark:text-white">
                                    {{ interaction.drugs ? interaction.drugs.join('  ⚡  ') : 'Drug Combination' }}
                                </span>
                                <span :class="[
                                    'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider',
                                    interaction.severity === 'severe' ? 'bg-rose-600 text-white' : 'bg-amber-500 text-slate-950'
                                ]">
                                    {{ interaction.severity }} Risk
                                </span>
                            </div>

                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-100">
                                {{ interaction.title }}
                            </p>

                            <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">
                                <strong>Mechanism:</strong> {{ interaction.mechanism }}
                            </p>

                            <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800 flex items-start gap-2 text-[11px] text-teal-800 dark:text-teal-300 bg-teal-500/10 p-2.5 rounded-xl">
                                <Info class="w-4 h-4 shrink-0 mt-0.5" />
                                <div>
                                    <strong class="font-bold">Clinical Recommendation:</strong>
                                    <span> {{ interaction.recommendation }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Allergy Warnings -->
                    <div v-if="checkResult.allergy_warnings && checkResult.allergy_warnings.length > 0" class="space-y-2">
                        <h4 class="text-xs font-black uppercase tracking-wider text-rose-600 flex items-center gap-1.5">
                            <AlertTriangle class="w-3.5 h-3.5" />
                            <span>Patient Drug Allergy Alerts</span>
                        </h4>

                        <div
                            v-for="(warn, idx) in checkResult.allergy_warnings"
                            :key="idx"
                            class="p-3.5 rounded-2xl bg-rose-100/60 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-900 text-xs text-rose-900 dark:text-rose-200 space-y-1"
                        >
                            <p class="font-bold flex items-center gap-2">
                                <span>{{ warn.drug }}</span>
                                <span class="px-2 py-0.5 rounded bg-rose-600 text-white text-[10px] font-mono uppercase">Allergic to {{ warn.allergen }}</span>
                            </p>
                            <p class="text-[11px] text-slate-700 dark:text-slate-300">{{ warn.message }}</p>
                        </div>
                    </div>

                    <!-- 3. Therapeutic Duplications -->
                    <div v-if="checkResult.duplicate_therapies && checkResult.duplicate_therapies.length > 0" class="space-y-2">
                        <h4 class="text-xs font-black uppercase tracking-wider text-purple-600 flex items-center gap-1.5">
                            <Info class="w-3.5 h-3.5" />
                            <span>Therapeutic Duplication & Class Redundancy</span>
                        </h4>

                        <div
                            v-for="(dup, idx) in checkResult.duplicate_therapies"
                            :key="idx"
                            class="p-3.5 rounded-2xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-900 text-xs space-y-1"
                        >
                            <p class="font-bold text-purple-900 dark:text-purple-300">
                                Duplicate Class: {{ dup.class }} ({{ dup.drugs.join(', ') }})
                            </p>
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">{{ dup.warning }}</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 border-t border-slate-100 dark:border-[#21262D] bg-slate-50/50 dark:bg-[#1A202C] flex items-center justify-between">
                    <p class="text-[10px] text-slate-400">
                        *AI suggestions provide clinical decision support. Final prescribing judgment rests with the attending doctor.
                    </p>
                    <button
                        type="button"
                        @click="showModal = false"
                        class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition cursor-pointer"
                    >
                        Acknowledge & Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
