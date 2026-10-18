<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Sparkles,
    Key,
    CheckCircle2,
    AlertCircle,
    Save,
    Eye,
    EyeOff,
    ExternalLink,
    Zap,
    Check,
    Lock,
    Unlock,
    ShieldCheck,
    Layers,
    Crown,
    Flame
} from 'lucide-vue-next';

const props = defineProps({
    settings: Object,
    packages: Array,
});

const form = useForm({
    ai_enabled: props.settings.ai_enabled ?? true,
    ai_provider: props.settings.ai_provider || 'gemini',
    gemini_api_key: props.settings.gemini_api_key || '',
    openai_api_key: props.settings.openai_api_key || '',
    ai_allowed_plans: Array.isArray(props.settings.ai_allowed_plans)
        ? [...props.settings.ai_allowed_plans]
        : ['free_trial', 'pro', 'enterprise'],
});

const showGeminiKey = ref(false);
const showOpenAiKey = ref(false);

// Live Connection Test State
const isTesting = ref(false);
const testResult = ref(null);

const testApiConnection = async (provider) => {
    isTesting.value = true;
    testResult.value = null;

    try {
        const payload = {
            provider: provider,
            api_key: provider === 'gemini' ? form.gemini_api_key : form.openai_api_key,
        };

        const response = await axios.post(route('admin.ai-settings.test'), payload);
        testResult.value = {
            success: true,
            provider: response.data.provider,
            latency_ms: response.data.latency_ms,
            message: response.data.message,
        };
    } catch (error) {
        const errorData = error.response?.data;
        testResult.value = {
            success: false,
            provider: provider === 'gemini' ? 'Google Gemini' : 'OpenAI',
            latency_ms: errorData?.latency_ms || 0,
            message: errorData?.message || error.message || 'Connection test failed. Please verify the API key and network connectivity.',
        };
    } finally {
        isTesting.value = false;
    }
};

const togglePlan = (planId) => {
    const idx = form.ai_allowed_plans.indexOf(planId);
    if (idx > -1) {
        form.ai_allowed_plans.splice(idx, 1);
    } else {
        form.ai_allowed_plans.push(planId);
    }
};

const selectAllPlans = () => {
    form.ai_allowed_plans = props.packages.map(p => p.id);
};

const deselectAllPlans = () => {
    form.ai_allowed_plans = [];
};

const selectProPlusOnly = () => {
    form.ai_allowed_plans = props.packages.filter(p => p.id !== 'solo').map(p => p.id);
};

const submit = () => {
    form.post(route('admin.ai-settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            testResult.value = null;
        },
    });
};
</script>

<template>
    <Head title="AI & Plan Permissions - Super Admin Console" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto space-y-6 pb-16">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-teal-500 to-cyan-500 text-white flex items-center justify-center shadow-xs">
                            <Sparkles class="w-4 h-4" />
                        </div>
                        <h1 class="text-lg font-black text-slate-900 dark:text-white">
                            AI Clinical Engine & Plan Access Permissions
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Configure central AI credentials and specify which subscription packages have access to clinical AI features.
                    </p>
                </div>

                <!-- Master AI Toggle -->
                <div class="flex items-center gap-3 bg-slate-50 dark:bg-[#21262D] px-4 py-2 rounded-xl border border-slate-200/60 dark:border-[#30363D]">
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-900 dark:text-white">AI Services</p>
                        <p class="text-[10px] text-slate-400">{{ form.ai_enabled ? 'Active Globally' : 'Disabled Globally' }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.ai_enabled"
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                    </label>
                </div>
            </div>

            <!-- Flash Success Message -->
            <div v-if="$page.props.flash?.success" class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>{{ $page.props.flash.success }}</span>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- 1. Select Active Primary AI Engine -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                        1. Select AI Engine
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Google Gemini Card -->
                        <div
                            @click="form.ai_provider = 'gemini'"
                            class="p-4 rounded-2xl border-2 transition-all cursor-pointer relative flex flex-col justify-between"
                            :class="[
                                form.ai_provider === 'gemini'
                                    ? 'border-teal-500 bg-teal-50/40 dark:bg-teal-950/20 shadow-sm'
                                    : 'border-slate-200 dark:border-[#21262D] bg-white dark:bg-[#161B22] hover:border-slate-300'
                            ]"
                        >
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                                            G
                                        </div>
                                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Google Gemini</h3>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">
                                        Free Tier (Recommended)
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                                    High speed & accuracy for clinical drug checks and copilot inquiries. Free 1,500 daily requests from Google AI Studio.
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between text-xs">
                                <span class="text-[11px] font-medium" :class="form.gemini_api_key ? 'text-teal-600 dark:text-teal-400 font-bold' : 'text-slate-400'">
                                    {{ form.gemini_api_key ? '✓ API Key Set' : '• Key Missing' }}
                                </span>
                                <div class="w-4 h-4 rounded-full border flex items-center justify-center" :class="form.ai_provider === 'gemini' ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300'">
                                    <Check v-if="form.ai_provider === 'gemini'" class="w-3 h-3 stroke-[3]" />
                                </div>
                            </div>
                        </div>

                        <!-- OpenAI ChatGPT Card -->
                        <div
                            @click="form.ai_provider = 'openai'"
                            class="p-4 rounded-2xl border-2 transition-all cursor-pointer relative flex flex-col justify-between"
                            :class="[
                                form.ai_provider === 'openai'
                                    ? 'border-teal-500 bg-teal-50/40 dark:bg-teal-950/20 shadow-sm'
                                    : 'border-slate-200 dark:border-[#21262D] bg-white dark:bg-[#161B22] hover:border-slate-300'
                            ]"
                        >
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-700 text-white flex items-center justify-center font-bold text-xs">
                                            AI
                                        </div>
                                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">OpenAI (ChatGPT)</h3>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">
                                        GPT-4o Mini
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                                    Conversational medical reasoning powered by OpenAI. Requires an active OpenAI API billing key.
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between text-xs">
                                <span class="text-[11px] font-medium" :class="form.openai_api_key ? 'text-teal-600 dark:text-teal-400 font-bold' : 'text-slate-400'">
                                    {{ form.openai_api_key ? '✓ API Key Set' : '• Key Missing' }}
                                </span>
                                <div class="w-4 h-4 rounded-full border flex items-center justify-center" :class="form.ai_provider === 'openai' ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300'">
                                    <Check v-if="form.ai_provider === 'openai'" class="w-3 h-3 stroke-[3]" />
                                </div>
                            </div>
                        </div>

                        <!-- Offline Rule Engine Card -->
                        <div
                            @click="form.ai_provider = 'offline'"
                            class="p-4 rounded-2xl border-2 transition-all cursor-pointer relative flex flex-col justify-between"
                            :class="[
                                form.ai_provider === 'offline'
                                    ? 'border-teal-500 bg-teal-50/40 dark:bg-teal-950/20 shadow-sm'
                                    : 'border-slate-200 dark:border-[#21262D] bg-white dark:bg-[#161B22] hover:border-slate-300'
                            ]"
                        >
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                                            ⚡
                                        </div>
                                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Offline Rule Engine</h3>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        Zero Cost
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                                    100% internal pharmacology rule base. Runs instantly offline without any external API key or internet connection.
                                </p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between text-xs">
                                <span class="text-[11px] font-bold text-teal-600 dark:text-teal-400">
                                    Ready & Free
                                </span>
                                <div class="w-4 h-4 rounded-full border flex items-center justify-center" :class="form.ai_provider === 'offline' ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300'">
                                    <Check v-if="form.ai_provider === 'offline'" class="w-3 h-3 stroke-[3]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Simple API Key Input Card -->
                <div v-if="form.ai_provider !== 'offline'" class="bg-white dark:bg-[#161B22] p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-3">
                        <div class="flex items-center gap-2">
                            <Key class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                2. {{ form.ai_provider === 'gemini' ? 'Google Gemini API Key' : 'OpenAI Secret Key' }}
                            </h2>
                        </div>
                        <a
                            v-if="form.ai_provider === 'gemini'"
                            href="https://aistudio.google.com/app/apikey"
                            target="_blank"
                            class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 font-bold"
                        >
                            <span>Get Free Gemini Key (1-Click)</span>
                            <ExternalLink class="w-3.5 h-3.5" />
                        </a>
                        <a
                            v-else
                            href="https://platform.openai.com/api-keys"
                            target="_blank"
                            class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-bold"
                        >
                            <span>OpenAI API Keys Portal</span>
                            <ExternalLink class="w-3.5 h-3.5" />
                        </a>
                    </div>

                    <!-- Gemini Key Input -->
                    <div v-if="form.ai_provider === 'gemini'" class="space-y-3">
                        <div class="relative">
                            <input
                                :type="showGeminiKey ? 'text' : 'password'"
                                v-model="form.gemini_api_key"
                                placeholder="Paste your Google Gemini Key here (starts with AIzaSy...)"
                                class="w-full pl-3 pr-10 py-3 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                            <button
                                type="button"
                                @click="showGeminiKey = !showGeminiKey"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <EyeOff v-if="showGeminiKey" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1">
                            <p class="text-[11px] text-slate-400">
                                Google Gemini 1.5 Flash is automatically utilized as the fast medical clinical engine.
                            </p>
                            <button
                                type="button"
                                @click="testApiConnection('gemini')"
                                :disabled="isTesting || !form.gemini_api_key"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 hover:bg-blue-100 rounded-xl text-xs font-bold transition-colors disabled:opacity-50 cursor-pointer shrink-0"
                            >
                                <Zap class="w-3.5 h-3.5" :class="isTesting ? 'animate-spin' : ''" />
                                <span>{{ isTesting ? 'Testing Key...' : '⚡ Test Gemini Key' }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- OpenAI Key Input -->
                    <div v-else class="space-y-3">
                        <div class="relative">
                            <input
                                :type="showOpenAiKey ? 'text' : 'password'"
                                v-model="form.openai_api_key"
                                placeholder="Paste your OpenAI Secret Key here (starts with sk-...)"
                                class="w-full pl-3 pr-10 py-3 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                            <button
                                type="button"
                                @click="showOpenAiKey = !showOpenAiKey"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <EyeOff v-if="showOpenAiKey" class="w-4 h-4" />
                                <Eye v-else class="w-4 h-4" />
                            </button>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1">
                            <p class="text-[11px] text-slate-400">
                                OpenAI GPT-4o Mini is automatically utilized for cost efficiency and clinical accuracy.
                            </p>
                            <button
                                type="button"
                                @click="testApiConnection('openai')"
                                :disabled="isTesting || !form.openai_api_key"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 rounded-xl text-xs font-bold transition-colors disabled:opacity-50 cursor-pointer shrink-0"
                            >
                                <Zap class="w-3.5 h-3.5" :class="isTesting ? 'animate-spin' : ''" />
                                <span>{{ isTesting ? 'Testing Key...' : '⚡ Test OpenAI Key' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Test Connection Live Result Box -->
                <div
                    v-if="testResult"
                    class="p-4 rounded-2xl border text-xs space-y-1.5 transition-all"
                    :class="[
                        testResult.success
                            ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-300 dark:border-emerald-800'
                            : 'bg-rose-50 dark:bg-rose-950/30 border-rose-300 dark:border-rose-800'
                    ]"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <CheckCircle2 v-if="testResult.success" class="w-4 h-4 text-emerald-600" />
                            <AlertCircle v-else class="w-4 h-4 text-rose-600" />
                            <span class="font-bold" :class="testResult.success ? 'text-emerald-900 dark:text-emerald-200' : 'text-rose-900 dark:text-rose-200'">
                                {{ testResult.success ? `${testResult.provider} Verified & Working!` : `${testResult.provider} Verification Failed` }}
                            </span>
                        </div>
                        <span v-if="testResult.latency_ms" class="font-mono font-bold text-[11px] px-2 py-0.5 rounded bg-white dark:bg-[#161B22] border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                            Latency: {{ testResult.latency_ms }} ms
                        </span>
                    </div>
                    <p class="text-slate-700 dark:text-slate-300 text-xs">
                        {{ testResult.message }}
                    </p>
                </div>

                <!-- 3. Package Permissions Access Control (100% English & Dynamic Packages) -->
                <div class="bg-white dark:bg-[#161B22] p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-[#21262D] pb-3">
                        <div class="flex items-center gap-2">
                            <ShieldCheck class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                                    3. Subscription Package AI Access
                                </h2>
                                <p class="text-[11px] text-slate-400">
                                    Select which doctor subscription tiers get access to the live AI Drug Checker and Clinical Chat Copilot.
                                </p>
                            </div>
                        </div>

                        <!-- Quick select shortcuts -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <button
                                type="button"
                                @click="selectAllPlans"
                                class="px-2.5 py-1 text-[11px] rounded-lg bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold transition-colors cursor-pointer"
                            >
                                Allow All
                            </button>
                            <button
                                type="button"
                                @click="selectProPlusOnly"
                                class="px-2.5 py-1 text-[11px] rounded-lg bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 hover:bg-teal-100 font-bold transition-colors cursor-pointer"
                            >
                                Pro & Above Only
                            </button>
                            <button
                                type="button"
                                @click="deselectAllPlans"
                                class="px-2.5 py-1 text-[11px] rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100 font-bold transition-colors cursor-pointer"
                            >
                                Disable All
                            </button>
                        </div>
                    </div>

                    <!-- Package Access Grid List -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div
                            v-for="pkg in packages"
                            :key="pkg.id"
                            @click="togglePlan(pkg.id)"
                            class="p-4 rounded-xl border-2 transition-all cursor-pointer flex items-start justify-between gap-3"
                            :class="[
                                form.ai_allowed_plans.includes(pkg.id)
                                    ? 'border-teal-500 bg-teal-50/40 dark:bg-teal-950/20'
                                    : 'border-slate-200 dark:border-[#21262D] bg-slate-50/50 dark:bg-[#21262D]/40 opacity-70 hover:opacity-100'
                            ]"
                        >
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-slate-900 dark:text-white">
                                        {{ pkg.name }}
                                    </span>
                                    <span class="text-[10px] font-mono px-2 py-0.2 rounded-md font-bold bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-slate-700 dark:text-slate-300">
                                        {{ pkg.badge }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ pkg.description }}
                                </p>
                            </div>

                            <!-- Toggle Status Icon -->
                            <div
                                class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 transition-colors"
                                :class="[
                                    form.ai_allowed_plans.includes(pkg.id)
                                        ? 'bg-teal-600 text-white'
                                        : 'bg-slate-200 dark:bg-slate-700 text-slate-400'
                                ]"
                            >
                                <Check v-if="form.ai_allowed_plans.includes(pkg.id)" class="w-4 h-4 stroke-[3]" />
                                <Lock v-else class="w-3.5 h-3.5" />
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 italic pt-1">
                        * Note: If a doctor is on an unselected package (e.g. Solo), the AI Copilot will prompt them to upgrade to Pro/Enterprise to unlock full clinical AI features.
                    </p>
                </div>

                <!-- Bottom Save Bar -->
                <div class="flex items-center justify-between p-4 bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-xs text-slate-500">
                        Configuration is cached and applies immediately across all doctor accounts.
                    </p>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Saving...' : 'Save AI Settings' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
