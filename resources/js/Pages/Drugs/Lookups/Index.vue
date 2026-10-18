<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Plus,
    Trash2,
    Sliders,
    Pill,
    Calendar,
    MessageSquare,
    Zap,
    Pipette,
    Tag,
    Clock
} from '@lucide/vue';

const props = defineProps({
    activeSection: String,
    items: Object,
});

const sectionMeta = {
    types: { title: 'Drug Types', icon: Pill, desc: 'Tablet, Capsule, Syrup, Injection, etc.', placeholder: 'e.g. Syrup', hasShortName: true },
    doses: { title: 'Drug Doses', icon: Pipette, desc: '1+0+1, 1+1+1, 0+0+1, etc.', placeholder: 'e.g. 1+0+1' },
    strengths: { title: 'Drug Strengths', icon: Zap, desc: '500mg, 20mg, 10mg, 250mg, etc.', placeholder: 'e.g. 500mg' },
    durations: { title: 'Drug Durations', icon: Clock, desc: '3 Days, 7 Days, 1 Month, Continue, etc.', placeholder: 'e.g. 7 Days' },
    advices: { title: 'Drug Advices', icon: MessageSquare, desc: 'After meal, 15 mins before meal, Before bedtime, etc.', placeholder: 'Write full advice / description here...', isTextarea: true },
};

const currentMeta = sectionMeta[props.activeSection] || sectionMeta.types;

const form = useForm({
    name: '',
    short_name: '',
});

const submit = () => {
    form.post(route('drug-lookups.store', props.activeSection), {
        onSuccess: () => form.reset(),
    });
};

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this item?')) {
        router.delete(route('drug-lookups.destroy', { type: props.activeSection, id: id }));
    }
};
</script>

<template>
    <Head :title="`${currentMeta.title} - Doc Helper AI`" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <component :is="currentMeta.icon" class="w-6 h-6 text-teal-600 dark:text-teal-400" />
                        {{ currentMeta.title }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ currentMeta.desc }}
                    </p>
                </div>
            </div>

            <!-- Tabs Nav for Sub-modules -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1">
                <Link
                    v-for="(meta, key) in sectionMeta"
                    :key="key"
                    :href="route('drug-lookups.index', key)"
                    :class="[
                        activeSection === key
                            ? 'bg-slate-900 dark:bg-teal-600 text-white font-semibold shadow-xs'
                            : 'bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] text-slate-700 dark:text-slate-300 hover:bg-slate-50',
                        'px-4 py-2 text-xs rounded-xl transition-all cursor-pointer shrink-0 flex items-center gap-2'
                    ]"
                >
                    <component :is="meta.icon" class="w-3.5 h-3.5" />
                    <span>{{ meta.title }}</span>
                </Link>
            </div>

            <!-- Content Grid: Form (Left) & List (Right) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Add New Item Form -->
                <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4 h-fit">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <Plus class="w-4 h-4 text-teal-600" /> Add New {{ currentMeta.title.replace('Drug ', '') }}
                    </h3>

                    <form @submit.prevent="submit" class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                {{ currentMeta.isTextarea ? 'Advice Description / Instruction' : 'Name / Value' }} <span class="text-rose-500">*</span>
                            </label>

                            <!-- Large Textarea for Advices / Descriptions -->
                            <textarea
                                v-if="currentMeta.isTextarea"
                                v-model="form.name"
                                rows="4"
                                :placeholder="currentMeta.placeholder"
                                required
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                            ></textarea>

                            <!-- Standard Input for other short lookups -->
                            <input
                                v-else
                                v-model="form.name"
                                type="text"
                                :placeholder="currentMeta.placeholder"
                                required
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>

                        <!-- Short name for Types (e.g. Tab., Cap.) -->
                        <div v-if="currentMeta.hasShortName">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Short Abbreviation (Optional)
                            </label>
                            <input
                                v-model="form.short_name"
                                type="text"
                                placeholder="e.g. Tab., Syp."
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-2.5 bg-slate-900 hover:bg-black dark:bg-teal-600 dark:hover:bg-teal-700 text-white text-xs font-semibold rounded-xl transition-all cursor-pointer shadow-sm disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save ' + currentMeta.title.replace('Drug ', '') }}
                        </button>
                    </form>
                </div>

                <!-- Existing Items Table -->
                <div class="md:col-span-2 bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                    <div class="p-4 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                        <span class="font-bold text-xs text-slate-900 dark:text-white">Active {{ currentMeta.title }}</span>
                        <span class="text-xs text-slate-400">{{ items.total || 0 }} total entries</span>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-[#21262D]">
                        <div
                            v-for="item in items.data"
                            :key="item.id"
                            class="p-3.5 px-4 hover:bg-slate-50/70 dark:hover:bg-[#21262D]/50 transition-colors flex items-center justify-between gap-3 text-xs"
                        >
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ item.name }}</span>
                                    <span v-if="item.short_name" class="px-1.5 py-0.5 rounded-md bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300 font-mono text-[10px] font-bold">
                                        {{ item.short_name }}
                                    </span>
                                </div>
                            </div>

                            <button
                                @click="deleteItem(item.id)"
                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer"
                                title="Delete"
                            >
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>

                        <div v-if="!items.data || items.data.length === 0" class="p-8 text-center text-slate-400">
                            No entries found. Add your first item using the form on the left.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
