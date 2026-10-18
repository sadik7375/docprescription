<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    Plus,
    Trash2,
    Save,
    Pill,
    AlertCircle,
    X,
    Sparkles
} from '@lucide/vue';

const props = defineProps({
    lookups: Object,
});

const form = useForm({
    trade_name: '',
    generic_name: '',
    note: '',
    warning: '',
    side_effect: '',
    additional_advice: '',
    variations: [
        {
            type: '',
            strength: '',
            dose: '',
            duration: '',
            advice: '',
        }
    ]
});

const addVariation = () => {
    form.variations.push({
        type: '',
        strength: '',
        dose: '',
        duration: '',
        advice: '',
    });
};

const removeVariation = (index) => {
    if (form.variations.length > 1) {
        form.variations.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('drugs.store'));
};
</script>

<template>
    <Head title="Create Drug - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Form Container Card (Exact Screenshot 1 design) -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <!-- Card Header -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        Create Drug
                    </h2>

                    <Link
                        :href="route('drugs.index')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0A0D36] dark:bg-slate-800 hover:bg-[#12164A] text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer"
                    >
                        <ChevronLeft class="w-3.5 h-3.5" /> Back
                    </Link>
                </div>

                <form @submit.prevent="submit" class="p-5 sm:p-6 space-y-6">
                    <!-- Top Form Grid: Row 1 & Row 2 -->
                    <div class="space-y-4">
                        <!-- Row 1: Trade Name, Generic Name, Note -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Trade name <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.trade_name"
                                    type="text"
                                    placeholder="Trade name"
                                    required
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                                <p v-if="form.errors.trade_name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.trade_name }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Generic name
                                </label>
                                <input
                                    v-model="form.generic_name"
                                    type="text"
                                    placeholder="Generic name"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                                <p v-if="form.errors.generic_name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.generic_name }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Note
                                </label>
                                <input
                                    v-model="form.note"
                                    type="text"
                                    placeholder="Note"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>
                        </div>

                        <!-- Row 2: Warning, Side effect, Additional advice -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Warning
                                </label>
                                <input
                                    v-model="form.warning"
                                    type="text"
                                    placeholder="Warning"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Side effect
                                </label>
                                <input
                                    v-model="form.side_effect"
                                    type="text"
                                    placeholder="Side effect"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Additional advice
                                </label>
                                <input
                                    v-model="form.additional_advice"
                                    type="text"
                                    placeholder="Additional advice"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Drug Variations Section (Exact Screenshot 1) -->
                    <div class="pt-4 border-t border-slate-100 dark:border-[#21262D] space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                Drug Variations
                            </h3>
                        </div>

                        <!-- Variations Repeatable Rows -->
                        <div class="space-y-3">
                            <div
                                v-for="(v, index) in form.variations"
                                :key="index"
                                class="p-3.5 rounded-xl bg-slate-50/70 dark:bg-[#21262D]/60 border border-slate-200/70 dark:border-[#30363D] space-y-3 transition-all"
                            >
                                <!-- First Row of Variation: Type, Strength, Dose -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <!-- Type Dropdown -->
                                    <div>
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Drug Type</label>
                                        <select
                                            v-model="v.type"
                                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                                        >
                                            <option value="">Please select Type</option>
                                            <option v-for="t in lookups.types" :key="t.id" :value="t.name">
                                                {{ t.short_name ? t.short_name + ' (' + t.name + ')' : t.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Strength (with Dropdown Options) -->
                                    <div>
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Strength (Select or Type)</label>
                                        <input
                                            v-model="v.strength"
                                            list="strength-options"
                                            type="text"
                                            placeholder="e.g. 500mg, 20mg, 10mg"
                                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                        />
                                        <datalist id="strength-options">
                                            <option v-for="s in lookups.strengths" :key="s.id" :value="s.name">{{ s.name }}</option>
                                        </datalist>
                                    </div>

                                    <!-- Dose (with Dropdown Options) -->
                                    <div>
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Dose (Select or Type)</label>
                                        <input
                                            v-model="v.dose"
                                            list="dose-options"
                                            type="text"
                                            placeholder="e.g. 1+0+1, 1+1+1, 0+0+1"
                                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                        />
                                        <datalist id="dose-options">
                                            <option v-for="d in lookups.doses" :key="d.id" :value="d.name">{{ d.name }}</option>
                                        </datalist>
                                    </div>
                                </div>

                                <!-- Second Row of Variation: Duration, Advice, Delete Button -->
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3">
                                    <!-- Duration (with Dropdown Options) -->
                                    <div class="sm:w-1/3">
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Duration</label>
                                        <input
                                            v-model="v.duration"
                                            list="duration-options"
                                            type="text"
                                            placeholder="e.g. 7 Days, 15 Days, 1 Month"
                                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                        />
                                        <datalist id="duration-options">
                                            <option v-for="dur in lookups.durations" :key="dur.id" :value="dur.name">{{ dur.name }}</option>
                                        </datalist>
                                    </div>

                                    <!-- Advice (with Dropdown Options) -->
                                    <div class="flex-1">
                                        <label class="block text-[11px] font-medium text-slate-600 dark:text-slate-400 mb-1">Advice / Instructions</label>
                                        <input
                                            v-model="v.advice"
                                            list="advice-options"
                                            type="text"
                                            placeholder="e.g. After meal, 15 mins before meal"
                                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                        />
                                        <datalist id="advice-options">
                                            <option v-for="a in lookups.advices" :key="a.id" :value="a.name">{{ a.name }}</option>
                                        </datalist>
                                    </div>

                                    <!-- Delete Row Button (Red x button matching Screenshot 1) -->
                                    <button
                                        type="button"
                                        @click="removeVariation(index)"
                                        :disabled="form.variations.length === 1"
                                        class="p-2.5 bg-[#DC2626] hover:bg-rose-700 text-white rounded-lg transition-colors flex items-center justify-center shrink-0 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed mb-0.5"
                                        title="Remove Variation"
                                    >
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- + Add More Button (Green button from Screenshot 1) -->
                        <div>
                            <button
                                type="button"
                                @click="addVariation"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#059669] hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-xs"
                            >
                                <Plus class="w-3.5 h-3.5" /> Add More
                            </button>
                        </div>
                    </div>

                    <!-- Footer Action: Save Button (Navy button from Screenshot 1) -->
                    <div class="pt-4 border-t border-slate-100 dark:border-[#21262D]">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#0A0D36] hover:bg-[#12164A] text-white text-xs font-bold rounded-lg transition-all cursor-pointer shadow-sm disabled:opacity-50"
                        >
                            <Save class="w-4 h-4" />
                            <span>{{ form.processing ? 'Saving...' : 'Save' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
