<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Plus,
    Search,
    Edit2,
    Trash2,
    Pill,
    Filter,
    ChevronLeft,
    ChevronRight,
    AlertCircle,
    CheckCircle2,
    X,
    Eye,
    Layers
} from '@lucide/vue';

const props = defineProps({
    drugs: Object,
    filters: Object,
    types: Array,
});

const search = ref(props.filters?.search || '');
const selectedType = ref(props.filters?.type || '');

// Delete confirmation modal state
const deleteModalOpen = ref(false);
const drugToDelete = ref(null);
const isDeleting = ref(false);

const applyFilters = () => {
    router.get(
        route('drugs.index'),
        {
            search: search.value || undefined,
            type: selectedType.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

// Debounce search
let searchTimer = null;
watch(search, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        applyFilters();
    }, 400);
});

watch(selectedType, () => {
    applyFilters();
});

const confirmDelete = (drug) => {
    drugToDelete.value = drug;
    deleteModalOpen.value = true;
};

const handleDelete = () => {
    if (!drugToDelete.value) return;
    isDeleting.value = true;
    router.delete(route('drugs.destroy', drugToDelete.value.id), {
        onSuccess: () => {
            deleteModalOpen.value = false;
            drugToDelete.value = null;
            isDeleting.value = false;
        },
        onError: () => {
            isDeleting.value = false;
        }
    });
};
</script>

<template>
    <Head title="Drugs Management - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <Pill class="w-6 h-6 text-teal-600 dark:text-teal-400" />
                        Drug Database & Formulations
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage your trade names, generic formulas, standard dosages, and variations.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('drugs.create')"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-black dark:bg-teal-600 dark:hover:bg-teal-700 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Add New Drug</span>
                    </Link>
                </div>
            </div>

            <!-- Search & Filters Strip -->
            <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="relative w-full sm:w-80">
                    <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search by Trade Name, Generic, Note..."
                        class="w-full pl-10 pr-4 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200/80 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                    />
                </div>

                <!-- Type Filter Dropdown / Pills -->
                <div class="flex items-center gap-2 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0">
                    <button
                        @click="selectedType = ''"
                        :class="[
                            selectedType === ''
                                ? 'bg-slate-900 dark:bg-teal-600 text-white font-semibold'
                                : 'bg-slate-100 dark:bg-[#21262D] text-slate-600 dark:text-slate-300 hover:bg-slate-200',
                            'px-3 py-1.5 text-xs rounded-xl transition-colors cursor-pointer shrink-0'
                        ]"
                    >
                        All Types
                    </button>
                    <button
                        v-for="t in types"
                        :key="t.id"
                        @click="selectedType = t.name"
                        :class="[
                            selectedType === t.name
                                ? 'bg-slate-900 dark:bg-teal-600 text-white font-semibold'
                                : 'bg-slate-100 dark:bg-[#21262D] text-slate-600 dark:text-slate-300 hover:bg-slate-200',
                            'px-3 py-1.5 text-xs rounded-xl transition-colors cursor-pointer shrink-0'
                        ]"
                    >
                        {{ t.name }}
                    </button>
                </div>
            </div>

            <!-- Drugs Table Card -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-[#21262D] bg-slate-50/70 dark:bg-[#1C2128] text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-3.5 px-4">Trade & Generic Name</th>
                                <th class="py-3.5 px-4">Variations / Formulations</th>
                                <th class="py-3.5 px-4">Standard Dose & Advice</th>
                                <th class="py-3.5 px-4">Note / Precautions</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr
                                v-for="drug in drugs.data"
                                :key="drug.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-[#21262D]/50 transition-colors"
                            >
                                <!-- Trade & Generic Name -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 font-bold flex items-center justify-center text-xs shrink-0 border border-teal-200/60 dark:border-teal-800/60">
                                            Rx
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white text-sm">{{ drug.trade_name }}</p>
                                            <p class="text-[11px] text-teal-600 dark:text-teal-400 font-medium">{{ drug.generic_name || 'No generic assigned' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Variations / Formulations Badges -->
                                <td class="py-3.5 px-4">
                                    <div v-if="drug.variations && drug.variations.length > 0" class="flex flex-wrap gap-1.5 max-w-xs">
                                        <span
                                            v-for="(v, index) in drug.variations"
                                            :key="index"
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-[#21262D] text-slate-800 dark:text-slate-200 text-[11px] font-medium border border-slate-200/60 dark:border-[#30363D]"
                                        >
                                            <span class="font-bold text-teal-600 dark:text-teal-400">{{ v.type }}</span>
                                            <span>{{ v.strength }}</span>
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400 italic text-[11px]">No variations</span>
                                </td>

                                <!-- Default Dose & Duration -->
                                <td class="py-3.5 px-4">
                                    <div v-if="drug.variations && drug.variations[0]" class="space-y-0.5">
                                        <p class="font-mono font-semibold text-slate-800 dark:text-slate-200">
                                            {{ drug.variations[0].dose || '—' }}
                                            <span v-if="drug.variations[0].duration" class="text-slate-400 font-normal font-sans">({{ drug.variations[0].duration }})</span>
                                        </p>
                                        <p v-if="drug.variations[0].advice" class="text-[11px] text-slate-500 dark:text-slate-400 truncate max-w-xs">
                                            {{ drug.variations[0].advice }}
                                        </p>
                                    </div>
                                    <span v-else class="text-slate-400">—</span>
                                </td>

                                <!-- Note / Precautions -->
                                <td class="py-3.5 px-4">
                                    <p class="text-slate-600 dark:text-slate-300 text-xs max-w-xs line-clamp-2">
                                        {{ drug.note || drug.warning || '—' }}
                                    </p>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <Link
                                            :href="route('drugs.edit', drug.id)"
                                            class="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#21262D] hover:text-teal-600 dark:hover:text-teal-400 transition-colors"
                                            title="Edit Drug"
                                        >
                                            <Edit2 class="w-4 h-4" />
                                        </Link>

                                        <button
                                            @click="confirmDelete(drug)"
                                            class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer"
                                            title="Delete Drug"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="!drugs.data || drugs.data.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-400">
                                    <Pill class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" />
                                    <p class="font-semibold text-slate-700 dark:text-slate-300 text-sm">No drugs found</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Try searching with a different trade name or click below to add one.</p>
                                    <Link
                                        :href="route('drugs.create')"
                                        class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold"
                                    >
                                        <Plus class="w-4 h-4" /> Add Drug
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Strip -->
                <div v-if="drugs.links && drugs.links.length > 3" class="p-4 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between text-xs text-slate-500">
                    <span>Showing {{ drugs.from || 0 }} to {{ drugs.to || 0 }} of {{ drugs.total || 0 }} drugs</span>

                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in drugs.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active
                                    ? 'bg-slate-900 dark:bg-teal-600 text-white font-bold'
                                    : link.url
                                    ? 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D]'
                                    : 'text-slate-300 dark:text-slate-700 pointer-events-none',
                                'px-3 py-1.5 rounded-lg transition-colors'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div
            v-if="deleteModalOpen && drugToDelete"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
            @click.self="deleteModalOpen = false"
        >
            <div class="bg-white dark:bg-[#161B22] w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-[#30363D] p-6 space-y-4">
                <div class="w-12 h-12 rounded-full bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto">
                    <Trash2 class="w-6 h-6" />
                </div>

                <div class="text-center">
                    <h3 class="font-bold text-base text-slate-900 dark:text-white">Delete Drug</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Are you sure you want to delete <b class="text-slate-800 dark:text-slate-200">{{ drugToDelete.trade_name }}</b>? All associated formulations and variations will also be removed.
                    </p>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button
                        @click="deleteModalOpen = false"
                        class="flex-1 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleDelete"
                        :disabled="isDeleting"
                        class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-sm transition-colors flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50"
                    >
                        <Trash2 class="w-4 h-4" />
                        <span>{{ isDeleting ? 'Deleting...' : 'Confirm Delete' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
