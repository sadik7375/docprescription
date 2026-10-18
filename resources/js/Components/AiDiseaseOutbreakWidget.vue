<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import {
    Sparkles,
    TrendingUp,
    AlertTriangle,
    ShieldCheck,
    Stethoscope,
    Activity,
    RotateCcw,
    Check,
    Copy,
    ChevronRight,
    ChevronDown,
    ChevronUp,
    Zap,
    Users
} from 'lucide-vue-next';

const isLoading = ref(false);
const trendsData = ref(null);
const errorMessage = ref('');
const isCopied = ref(false);
const isCollapsed = ref(localStorage.getItem('ai_disease_outbreak_collapsed') === 'true');

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('ai_disease_outbreak_collapsed', isCollapsed.value);
};

const fetchTrends = async () => {
    isLoading.value = true;
    errorMessage.value = '';
    try {
        const res = await axios.get(route('ai.chamber-disease-trends'));
        if (res.data?.success) {
            trendsData.value = res.data;
        } else {
            errorMessage.value = res.data?.message || 'Unable to fetch disease trends.';
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'AI epidemiological service unavailable.';
    } finally {
        isLoading.value = false;
    }
};

const copyAdvice = () => {
    if (!trendsData.value?.advisory?.quick_rx_advice) return;
    navigator.clipboard.writeText(trendsData.value.advisory.quick_rx_advice);
    isCopied.value = true;
    setTimeout(() => {
        isCopied.value = false;
    }, 2000);
};

const getBadgeStyle = (level) => {
    switch (level) {
        case 'high_surge':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border-rose-300 dark:border-rose-800 font-bold';
        case 'moderate_surge':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-300 dark:border-amber-800 font-bold';
        case 'seasonal_watch':
            return 'bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border-teal-300 dark:border-teal-800 font-bold';
        default:
            return 'bg-slate-100 dark:bg-[#21262D] text-slate-800 dark:text-slate-200 border-slate-300 dark:border-[#30363D] font-bold';
    }
};

const getBadgeLabel = (level) => {
    switch (level) {
        case 'high_surge':
            return 'High Alert Surge';
        case 'moderate_surge':
            return 'Moderate Surge';
        case 'seasonal_watch':
            return 'Seasonal Watch';
        default:
            return 'Stable Baseline';
    }
};

onMounted(() => {
    fetchTrends();
});
</script>

<template>
    <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200 dark:border-[#21262D] shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-[#21262D] bg-slate-50/50 dark:bg-[#1C2128] flex flex-wrap items-center justify-between gap-3 select-none">
            <div class="flex items-center gap-3 cursor-pointer" @click="toggleCollapse">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20 shrink-0">
                    <Activity class="w-5 h-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                            Chamber Disease Outbreak & Seasonal Trends
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-teal-100 dark:bg-teal-900/60 text-teal-800 dark:text-teal-200 border border-teal-200 dark:border-teal-800 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-600 dark:bg-teal-400 animate-ping"></span>
                            AI Surveillance
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-400 font-medium mt-0.5">
                        14-day diagnostic surge analytics vs chamber historical baseline
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="fetchTrends"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white dark:bg-[#21262D] hover:bg-slate-100 dark:hover:bg-[#30363D] text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-[#30363D] text-xs font-bold transition cursor-pointer shadow-xs"
                    title="Refresh AI Trends"
                >
                    <RotateCcw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoading }" />
                    <span class="hidden sm:inline">{{ isLoading ? 'Refreshing...' : 'Refresh' }}</span>
                </button>

                <button
                    type="button"
                    @click="toggleCollapse"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-white dark:bg-[#21262D] hover:bg-slate-100 dark:hover:bg-[#30363D] text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-[#30363D] text-xs font-bold transition cursor-pointer shadow-xs"
                    :title="isCollapsed ? 'Expand section' : 'Hide / Collapse section'"
                >
                    <span class="text-xs">{{ isCollapsed ? 'Expand' : 'Hide' }}</span>
                    <ChevronDown v-if="isCollapsed" class="w-4 h-4 text-slate-600 dark:text-slate-300" />
                    <ChevronUp v-else class="w-4 h-4 text-slate-600 dark:text-slate-300" />
                </button>
            </div>
        </div>

        <!-- Body (Collapsible) -->
        <div v-show="!isCollapsed" class="p-5 sm:p-6 space-y-6 transition-all duration-300">
            <!-- Loading -->
            <div v-if="isLoading && !trendsData" class="py-8 flex flex-col items-center justify-center gap-2 text-center">
                <div class="w-8 h-8 rounded-full border-2 border-teal-500 border-t-transparent animate-spin"></div>
                <p class="text-xs font-medium text-slate-600 dark:text-slate-400">AI is evaluating chamber epidemiological diagnostic clusters...</p>
            </div>

            <!-- Error -->
            <div v-else-if="errorMessage" class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center justify-between">
                <span>{{ errorMessage }}</span>
                <button type="button" @click="fetchTrends" class="underline font-bold cursor-pointer hover:text-rose-900 dark:hover:text-white">Retry</button>
            </div>

            <!-- Trends Content -->
            <div v-else-if="trendsData" class="space-y-6">
                <!-- Clean Empty State (Fresh Chamber / No cases yet) -->
                <div v-if="!trendsData.trends || trendsData.trends.length === 0" class="py-8 px-4 text-center rounded-2xl bg-slate-50/70 dark:bg-[#1C2128]/50 border border-slate-200/80 dark:border-[#21262D] space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800/60 text-teal-600 dark:text-teal-400 flex items-center justify-center mx-auto shadow-xs">
                        <ShieldCheck class="w-6 h-6" />
                    </div>
                    <div class="max-w-md mx-auto space-y-1">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">AI Surveillance Active: Stable Baseline</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed font-normal">
                            {{ trendsData.message || 'No abnormal disease spikes or outbreak clusters detected in the last 14 days. As you prescribe for patients and record diagnoses, AI will automatically analyze local epidemiological trends.' }}
                        </p>
                    </div>
                </div>

                <!-- Surging Illnesses Grid Cards (When real data exists) -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    <div
                        v-for="(trend, idx) in trendsData.trends"
                        :key="idx"
                        class="p-4 rounded-2xl border transition-all duration-200 space-y-2.5 bg-white dark:bg-[#1C2128] shadow-xs"
                        :class="trend.alert_level === 'high_surge' ? 'border-l-4 border-l-rose-500 border-rose-200/90 dark:border-rose-900/50 hover:border-rose-400' : (trend.alert_level === 'moderate_surge' ? 'border-l-4 border-l-amber-500 border-amber-200/90 dark:border-amber-900/50 hover:border-amber-400' : 'border-l-4 border-l-teal-500 border-slate-200 dark:border-[#30363D] hover:border-teal-400')"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider border"
                                :class="getBadgeStyle(trend.alert_level)"
                            >
                                {{ getBadgeLabel(trend.alert_level) }}
                            </span>
                            <span
                                class="flex items-center gap-0.5 text-xs font-black"
                                :class="trend.surge_percent > 0 ? (trend.alert_level === 'high_surge' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400') : 'text-slate-600 dark:text-slate-400'"
                            >
                                <TrendingUp v-if="trend.surge_percent > 0" class="w-3.5 h-3.5" />
                                <span>{{ trend.surge_percent > 0 ? '+' : '' }}{{ trend.surge_percent }}%</span>
                            </span>
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white line-clamp-1" :title="trend.disease">
                                {{ trend.disease }}
                            </h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 font-medium mt-1">
                                <strong class="text-slate-900 dark:text-white font-bold">{{ trend.recent_cases }} cases</strong> in last 14d (vs {{ trend.baseline_cases }} baseline)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- AI Clinical Advisory Bulletin (High Contrast & Sharp Readability) -->
                <div v-if="trendsData.advisory" class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-[#161B22] border-2 border-teal-500/40 dark:border-teal-500/50 shadow-sm space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 dark:border-[#21262D] pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-teal-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <Zap class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white tracking-wide uppercase">
                                {{ trendsData.advisory.headline }}
                            </h3>
                        </div>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-[#21262D] px-2.5 py-1 rounded-lg">
                            Updated {{ trendsData.generated_at }}
                        </span>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed font-normal">
                        {{ trendsData.advisory.summary }}
                    </p>

                    <!-- Action Protocols -->
                    <div class="space-y-2 pt-1">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-teal-800 dark:text-teal-300 flex items-center gap-1.5">
                            Actionable Chamber Protocols:
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div
                                v-for="(proto, pIdx) in trendsData.advisory.action_protocols"
                                :key="pIdx"
                                class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#1E2633] border border-slate-200 dark:border-[#30363D] text-xs sm:text-sm text-slate-900 dark:text-slate-100 font-semibold flex items-start gap-2.5 shadow-2xs hover:border-teal-400 transition"
                            >
                                <span class="w-5 h-5 rounded-full bg-teal-600 text-white font-black text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                    {{ pIdx + 1 }}
                                </span>
                                <span class="leading-normal">{{ proto }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Rx Advice Copy -->
                    <div v-if="trendsData.advisory.quick_rx_advice" class="pt-3 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 dark:border-[#21262D]">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 text-xs sm:text-sm">
                            <span class="font-bold text-teal-800 dark:text-teal-300 shrink-0">1-Click Preventative Advice:</span>
                            <span class="text-slate-800 dark:text-slate-200 font-medium">"{{ trendsData.advisory.quick_rx_advice }}"</span>
                        </div>
                        <button
                            type="button"
                            @click="copyAdvice"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs transition cursor-pointer shadow-sm shadow-teal-600/20 shrink-0"
                        >
                            <Check v-if="isCopied" class="w-4 h-4 text-emerald-200" />
                            <Copy v-else class="w-4 h-4" />
                            <span>{{ isCopied ? 'Copied Advice!' : 'Copy to Rx Advice' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>