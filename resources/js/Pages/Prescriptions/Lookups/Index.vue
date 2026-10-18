<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Activity,
    Stethoscope,
    FlaskConical,
    FileHeart,
    Plus,
    Search,
    Trash2,
    Check,
    Save,
    X,
    Sparkles,
    ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    type: String,
    items: Object,
    counts: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const showAddModal = ref(false);

const tabs = [
    { key: 'complaints', label: 'Chief Complaints (C/C)', icon: Activity, count: props.counts.complaints },
    { key: 'diagnoses', label: 'Diagnoses (Dx)', icon: Stethoscope, count: props.counts.diagnoses },
    { key: 'investigations', label: 'Investigations / Tests (Ix)', icon: FlaskConical, count: props.counts.investigations },
    { key: 'advices', label: 'General Advice', icon: FileHeart, count: props.counts.advices },
];

const form = useForm({
    name: '',
    icd10_code: '',
    category: '',
    description: '',
});

const applySearch = () => {
    router.get(
        route('clinical-lookups.index', props.type),
        { search: search.value },
        { preserveState: true, replace: true }
    );
};

const openAddModal = () => {
    form.reset();
    showAddModal.value = true;
};

const submitAdd = () => {
    form.post(route('clinical-lookups.store', props.type), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this lookup item?')) {
        router.delete(route('clinical-lookups.destroy', { type: props.type, id }));
    }
};
</script>

<template>
    <Head title="Clinical Lookups - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Activity class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        Clinical Lookups & Rx Presets
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage pre-saved Chief Complaints, Clinical Diagnoses, Lab Tests, and Patient Advice.
                    </p>
                </div>

                <button
                    type="button"
                    @click="openAddModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
                >
                    <Plus class="w-4 h-4" />
                    <span>Add New {{ type === 'complaints' ? 'Complaint' : type === 'diagnoses' ? 'Diagnosis' : type === 'investigations' ? 'Investigation' : 'Advice' }}</span>
                </button>
            </div>

            <!-- Tabs Selection -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar">
                <Link
                    v-for="tab in tabs"
                    :key="tab.key"
                    :href="route('clinical-lookups.index', tab.key)"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all border cursor-pointer"
                    :class="[
                        type === tab.key
                            ? 'bg-[#0A0D36] dark:bg-teal-500 text-white dark:text-slate-950 border-transparent shadow-xs font-bold'
                            : 'bg-white dark:bg-[#161B22] text-slate-600 dark:text-slate-400 border-slate-200/80 dark:border-[#21262D] hover:bg-slate-50 dark:hover:bg-[#21262D]'
                    ]"
                >
                    <component :is="tab.icon" class="w-4 h-4" />
                    <span>{{ tab.label }}</span>
                    <span
                        class="px-1.5 py-0.5 rounded-md text-[10px] font-bold"
                        :class="type === tab.key ? 'bg-white/20 dark:bg-slate-900/30 text-white dark:text-slate-950' : 'bg-slate-100 dark:bg-[#21262D] text-slate-500'"
                    >
                        {{ tab.count }}
                    </span>
                </Link>
            </div>

            <!-- Search Bar -->
            <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex items-center justify-between gap-3">
                <div class="relative flex-1 max-w-md">
                    <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        @keyup.enter="applySearch"
                        type="text"
                        placeholder="Search lookup items..."
                        class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:bg-white"
                    />
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/75 dark:bg-[#21262D]/60 text-[11px] font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider border-b border-slate-200/80 dark:border-[#30363D]">
                            <tr>
                                <th class="py-3 px-4">Name / Title</th>
                                <th v-if="type === 'diagnoses'" class="py-3 px-4">ICD-10 Code</th>
                                <th v-if="type === 'investigations' || type === 'advices'" class="py-3 px-4">Category</th>
                                <th class="py-3 px-4">Description / Details</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr
                                v-for="item in items.data"
                                :key="item.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-[#21262D]/40 transition-colors"
                            >
                                <!-- Name -->
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ item.name }}
                                </td>

                                <!-- ICD-10 Code for Diagnoses -->
                                <td v-if="type === 'diagnoses'" class="py-3 px-4">
                                    <span v-if="item.icd10_code" class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-[#30363D]">
                                        {{ item.icd10_code }}
                                    </span>
                                    <span v-else class="text-slate-400">—</span>
                                </td>

                                <!-- Category for Investigations & Advices -->
                                <td v-if="type === 'investigations' || type === 'advices'" class="py-3 px-4">
                                    <span v-if="item.category" class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                        {{ item.category }}
                                    </span>
                                    <span v-else class="text-slate-400">—</span>
                                </td>

                                <!-- Description -->
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 max-w-md">
                                    {{ item.description || '—' }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right">
                                    <button
                                        type="button"
                                        @click="deleteItem(item.id)"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                        title="Delete"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!items.data || items.data.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <Activity class="w-8 h-8 mx-auto mb-2 opacity-30" />
                                    <p class="font-medium">No records found</p>
                                    <p class="text-[11px] mt-1">Click "Add New" to create one.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="items.links && items.links.length > 3" class="p-4 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Showing {{ items.from || 0 }} to {{ items.to || 0 }} of {{ items.total }} items
                    </p>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in items.links" :key="i">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="px-2.5 py-1 text-xs rounded-lg transition-colors"
                                :class="link.active ? 'bg-slate-900 dark:bg-teal-500 text-white dark:text-slate-950 font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D]'"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#21262D]">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                        Add New {{ type === 'complaints' ? 'Chief Complaint' : type === 'diagnoses' ? 'Diagnosis' : type === 'investigations' ? 'Investigation' : 'Clinical Advice' }}
                    </h3>
                    <button
                        type="button"
                        @click="showAddModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitAdd" class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Name / Title <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="e.g. High fever, Acute Bronchitis..."
                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        />
                    </div>

                    <!-- ICD-10 Code (Diagnoses only) -->
                    <div v-if="type === 'diagnoses'">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            ICD-10 Code (Optional)
                        </label>
                        <input
                            v-model="form.icd10_code"
                            type="text"
                            placeholder="e.g. J20.9, E11.9"
                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        />
                    </div>

                    <!-- Category (Investigations & Advices) -->
                    <div v-if="type === 'investigations' || type === 'advices'">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Category (Optional)
                        </label>
                        <input
                            v-model="form.category"
                            type="text"
                            :placeholder="type === 'investigations' ? 'e.g. Hematology, Radiology, Biochemistry' : 'e.g. Diet, Lifestyle, Medication'"
                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Description / Remarks (Optional)
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="2"
                            placeholder="Additional details..."
                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-[#21262D]">
                        <button
                            type="button"
                            @click="showAddModal = false"
                            class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 text-xs font-bold text-white bg-[#0A0D36] hover:bg-[#12164A] rounded-xl transition-colors cursor-pointer shadow-xs disabled:opacity-50"
                        >
                            <span>{{ form.processing ? 'Saving...' : 'Save Item' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
