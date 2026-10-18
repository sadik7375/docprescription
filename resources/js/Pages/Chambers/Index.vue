<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    Building2,
    Plus,
    MapPin,
    Phone,
    Clock,
    Calendar,
    DollarSign,
    CheckCircle2,
    Star,
    Edit3,
    Trash2,
    Users,
    Check,
    Sparkles,
    ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    chambers: Array,
    limitCheck: {
        type: Object,
        default: () => ({ allowed: true })
    },
});

const page = usePage();
const activeChamber = ref(page.props.activeChamber);

const switchChamber = (chamber) => {
    router.post(route('chambers.switch', chamber.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            activeChamber.value = chamber;
        }
    });
};

const setDefaultChamber = (chamber) => {
    router.post(route('chambers.set-default', chamber.id), {}, {
        preserveScroll: true,
    });
};

const deleteChamber = (chamber) => {
    if (confirm(`Are you sure you want to delete "${chamber.name}"?`)) {
        router.delete(route('chambers.destroy', chamber.id));
    }
};
</script>

<template>
    <Head title="Chamber Management - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Limit Warning Banner if reached -->
            <div v-if="limitCheck && !limitCheck.allowed" class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <Sparkles class="w-4 h-4 text-amber-600 shrink-0" />
                    <span>{{ limitCheck.reason }}</span>
                </div>
                <Link
                    :href="route('subscription.index')"
                    class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-[11px] shrink-0"
                >
                    <span>Upgrade to Pro</span>
                    <ChevronRight class="w-3.5 h-3.5" />
                </Link>
            </div>

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Building2 class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        Chamber & Clinic Management
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage your practice locations, consultation schedules, fees, and switch your active chamber.
                    </p>
                </div>

                <Link
                    v-if="!limitCheck || limitCheck.allowed"
                    :href="route('chambers.create')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
                >
                    <Plus class="w-4 h-4" />
                    <span>Add New Chamber</span>
                </Link>
                <Link
                    v-else
                    :href="route('subscription.index')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
                >
                    <Sparkles class="w-4 h-4" />
                    <span>Upgrade to Add More Chambers</span>
                </Link>
            </div>

            <!-- Current Active Chamber Banner -->
            <div v-if="activeChamber" class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-teal-500/10 via-teal-500/5 to-transparent border border-teal-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-teal-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <Building2 class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-700 dark:text-teal-400 bg-teal-100 dark:bg-teal-950/60 px-2 py-0.5 rounded-md">
                                Current Active Chamber
                            </span>
                            <span v-if="activeChamber.room_no" class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                {{ activeChamber.room_no }}
                            </span>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white mt-0.5">
                            {{ activeChamber.name }} <span v-if="activeChamber.branch" class="text-xs font-normal text-slate-500">({{ activeChamber.branch }})</span>
                        </h2>
                    </div>
                </div>

                <div class="text-xs text-slate-600 dark:text-slate-300 sm:text-right">
                    <p class="font-medium flex items-center sm:justify-end gap-1.5">
                        <Clock class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                        {{ activeChamber.visiting_time || 'No time specified' }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        New prescriptions and patient queue automatically use this chamber.
                    </p>
                </div>
            </div>

            <!-- Chambers Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    v-for="c in chambers"
                    :key="c.id"
                    class="bg-white dark:bg-[#161B22] rounded-2xl border transition-all duration-200 overflow-hidden shadow-xs flex flex-col justify-between"
                    :class="[
                        activeChamber?.id === c.id
                            ? 'border-teal-500 ring-2 ring-teal-500/20'
                            : 'border-slate-200/80 dark:border-[#21262D] hover:border-slate-300 dark:hover:border-slate-700'
                    ]"
                >
                    <!-- Card Header -->
                    <div class="p-5 border-b border-slate-100 dark:border-[#21262D]">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                        {{ c.name }}
                                    </h3>
                                    <span v-if="c.is_default" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                        <Star class="w-2.5 h-2.5 fill-current" /> Default
                                    </span>
                                </div>
                                <p v-if="c.branch" class="text-xs font-medium text-teal-600 dark:text-teal-400 mt-0.5">
                                    {{ c.branch }} <span v-if="c.room_no" class="text-slate-400">• {{ c.room_no }}</span>
                                </p>
                            </div>

                            <div class="flex items-center gap-1">
                                <Link
                                    :href="route('chambers.edit', c.id)"
                                    class="p-1.5 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                    title="Edit Chamber"
                                >
                                    <Edit3 class="w-4 h-4" />
                                </Link>
                                <button
                                    type="button"
                                    @click="deleteChamber(c)"
                                    :disabled="chambers.length === 1"
                                    class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                                    title="Delete Chamber"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 space-y-4 text-xs">
                        <!-- Location & Contact -->
                        <div class="space-y-2">
                            <div class="flex items-start gap-2.5 text-slate-600 dark:text-slate-300">
                                <MapPin class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                                <span>{{ c.address || 'Address not provided' }}</span>
                            </div>

                            <div v-if="c.phone" class="flex items-center gap-2.5 text-slate-600 dark:text-slate-300">
                                <Phone class="w-4 h-4 text-slate-400 shrink-0" />
                                <span>{{ c.phone }}</span>
                            </div>
                        </div>

                        <!-- Schedule & Visiting Days -->
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200/70 dark:border-[#30363D] space-y-2">
                            <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1.5">
                                    <Calendar class="w-3.5 h-3.5" /> Visiting Days
                                </span>
                                <span class="flex items-center gap-1.5 text-slate-700 dark:text-slate-200">
                                    <Clock class="w-3.5 h-3.5 text-teal-500" /> {{ c.visiting_time || 'Schedule open' }}
                                </span>
                            </div>

                            <!-- Days Badges -->
                            <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                <template v-if="c.visiting_days && c.visiting_days.length > 0">
                                    <span
                                        v-for="day in c.visiting_days"
                                        :key="day"
                                        class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-slate-700 dark:text-slate-300"
                                    >
                                        {{ day.substring(0, 3) }}
                                    </span>
                                </template>
                                <span v-else class="text-slate-400 italic text-[11px]">All days / Open</span>
                            </div>
                        </div>

                        <!-- Fees & Patients Seen -->
                        <div class="grid grid-cols-3 gap-2 pt-1">
                            <div class="p-2.5 rounded-xl bg-slate-50/70 dark:bg-[#21262D]/40 border border-slate-200/60 dark:border-[#30363D]">
                                <p class="text-[10px] text-slate-400 font-medium">Consultation Fee</p>
                                <p class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ c.consultation_fee || '0' }}</p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50/70 dark:bg-[#21262D]/40 border border-slate-200/60 dark:border-[#30363D]">
                                <p class="text-[10px] text-slate-400 font-medium">Follow-up Fee</p>
                                <p class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ c.followup_fee || '0' }}</p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50/70 dark:bg-[#21262D]/40 border border-slate-200/60 dark:border-[#30363D]">
                                <p class="text-[10px] text-slate-400 font-medium">Patients Seen</p>
                                <p class="text-xs font-bold text-teal-600 dark:text-teal-400 mt-0.5">{{ c.patients_count || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="p-4 bg-slate-50/50 dark:bg-[#21262D]/30 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button
                                v-if="!c.is_default"
                                type="button"
                                @click="setDefaultChamber(c)"
                                class="text-[11px] text-slate-500 hover:text-amber-600 font-medium cursor-pointer transition-colors"
                            >
                                Set as Default
                            </button>
                        </div>

                        <div>
                            <button
                                v-if="activeChamber?.id === c.id"
                                type="button"
                                disabled
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800 text-xs font-bold rounded-xl cursor-default"
                            >
                                <CheckCircle2 class="w-3.5 h-3.5" />
                                <span>Active Now</span>
                            </button>

                            <button
                                v-else
                                type="button"
                                @click="switchChamber(c)"
                                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] text-white dark:text-slate-950 text-xs font-bold rounded-xl transition-all cursor-pointer shadow-xs"
                            >
                                <span>Switch to this Chamber</span>
                                <ChevronRight class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
