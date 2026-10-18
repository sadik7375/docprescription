<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import {
    Users,
    UserPlus,
    ShieldCheck,
    Building2,
    Mail,
    Phone,
    Key,
    Edit2,
    Trash2,
    CheckCircle2,
    XCircle,
    Search,
    Filter,
    Check,
    X,
    Calendar,
    FileText,
    Activity,
    SlidersHorizontal,
    Plus,
    UserCheck,
    Layers,
    Lock,
    Ban,
    Sparkles,
    CheckSquare,
    Square
} from 'lucide-vue-next';

const props = defineProps({
    staffList: {
        type: Array,
        default: () => [],
    },
    chambers: {
        type: Array,
        default: () => [],
    },
    availablePermissions: {
        type: Array,
        default: () => [],
    },
    limitCheck: {
        type: Object,
        default: () => ({ allowed: true })
    },
    filters: {
        type: Object,
        default: () => ({ search: '', chamber_id: '' }),
    },
});

const searchQuery = ref(props.filters.search || '');
const chamberFilter = ref(props.filters.chamber_id || '');

const handleFilter = () => {
    router.get(route('staff.index'), {
        search: searchQuery.value || undefined,
        chamber_id: chamberFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Modal State
const isModalOpen = ref(false);
const editingStaff = ref(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    designation: 'Receptionist',
    chamber_ids: [],
    permissions: ['appointments.manage', 'patients.manage'],
    is_active: true,
});

// Chamber selection helpers
const isAllChambers = computed(() => {
    return form.chamber_ids.length === 0;
});

const toggleChamber = (chamberId) => {
    const id = Number(chamberId);
    const idx = form.chamber_ids.indexOf(id);
    if (idx > -1) {
        form.chamber_ids.splice(idx, 1);
    } else {
        form.chamber_ids.push(id);
    }
};

const setAllChambersAccess = () => {
    form.chamber_ids = [];
};

const selectAllIndividualChambers = () => {
    form.chamber_ids = props.chambers.map(c => Number(c.id));
};

const isChamberSelected = (chamberId) => {
    return form.chamber_ids.includes(Number(chamberId));
};

const openCreateModal = () => {
    editingStaff.value = null;
    form.reset();
    form.clearErrors();
    form.designation = 'Receptionist';
    form.chamber_ids = []; // Default: All chambers
    form.permissions = ['appointments.manage', 'patients.manage'];
    form.is_active = true;
    isModalOpen.value = true;
};

const openEditModal = (staff) => {
    editingStaff.value = staff;
    form.clearErrors();
    form.name = staff.name || '';
    form.email = staff.email || '';
    form.phone = staff.phone || '';
    form.password = '';
    form.designation = staff.designation || 'Receptionist';
    form.chamber_ids = staff.chamber_ids && staff.chamber_ids.length > 0 
        ? staff.chamber_ids.map(Number) 
        : [];
    form.permissions = Array.isArray(staff.permissions) ? [...staff.permissions] : [];
    form.is_active = Boolean(staff.is_active);
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingStaff.value = null;
    form.reset();
};

const togglePermission = (key) => {
    const idx = form.permissions.indexOf(key);
    if (idx > -1) {
        form.permissions.splice(idx, 1);
    } else {
        form.permissions.push(key);
    }
};

// Quick Presets
const applyReceptionistPreset = () => {
    form.permissions = ['appointments.manage', 'patients.manage', 'chambers.view'];
    form.designation = 'Receptionist';
};

const applyAssistantPreset = () => {
    form.permissions = [
        'appointments.manage',
        'patients.manage',
        'prescriptions.view',
        'prescriptions.create',
        'drugs.manage',
    ];
    form.designation = 'Clinical Assistant';
};

const applyFullAccess = () => {
    form.permissions = props.availablePermissions.map(p => p.key);
};

const clearAllPermissions = () => {
    form.permissions = [];
};

const submit = () => {
    if (editingStaff.value) {
        form.put(route('staff.update', editingStaff.value.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('staff.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const toggleStatus = (staff) => {
    const action = staff.is_active ? 'temporarily block' : 'unblock';
    if (confirm(`Are you sure you want to ${action} ${staff.name}? ${staff.is_active ? 'They will be immediately blocked from logging in.' : 'They will be allowed to log in again.'}`)) {
        router.patch(route('staff.toggle-status', staff.id), {}, {
            preserveScroll: true,
        });
    }
};

const deleteStaff = (staff) => {
    if (confirm(`Are you sure you want to remove ${staff.name}? This will delete their login account.`)) {
        router.delete(route('staff.destroy', staff.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Staff & Permissions - Doctor Console" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6 pb-16">
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
                    <span>Upgrade Plan</span>
                </Link>
            </div>

            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                            <UserCheck class="w-4 h-4" />
                        </div>
                        <h1 class="text-base font-bold text-slate-900 dark:text-white">
                            Staff Accounts & Multi-Chamber Permissions
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Create login accounts for receptionists & clinical assistants, assign multiple chambers, and configure granular permissions.
                    </p>
                </div>

                <button
                    v-if="!limitCheck || limitCheck.allowed"
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
                >
                    <UserPlus class="w-4 h-4" />
                    <span>+ Add New Staff</span>
                </button>
                <Link
                    v-else
                    :href="route('subscription.index')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
                >
                    <Sparkles class="w-4 h-4" />
                    <span>Upgrade to Add Staff</span>
                </Link>
            </div>

            <!-- Flash Success Message -->
            <div v-if="$page.props.flash?.success" class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>{{ $page.props.flash.success }}</span>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        v-model="searchQuery"
                        @input="handleFilter"
                        placeholder="Search by staff name, email, phone, or designation..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                    />
                </div>

                <div class="sm:w-64">
                    <select
                        v-model="chamberFilter"
                        @change="handleFilter"
                        class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 cursor-pointer"
                    >
                        <option value="">All Chambers Filter</option>
                        <option v-for="ch in chambers" :key="ch.id" :value="ch.id">
                            {{ ch.name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Staff Members Grid -->
            <div v-if="staffList.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="staff in staffList"
                    :key="staff.id"
                    class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs p-5 flex flex-col justify-between space-y-4 hover:border-teal-300 dark:hover:border-teal-800 transition-all"
                >
                    <!-- Top Info Card -->
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-700 text-white font-black text-sm flex items-center justify-center shrink-0">
                                    {{ staff.name ? staff.name.charAt(0).toUpperCase() : 'S' }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-bold text-sm text-slate-900 dark:text-white truncate">
                                        {{ staff.name }}
                                    </h3>
                                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 border border-teal-200 dark:border-teal-800 mt-0.5">
                                        {{ staff.designation }}
                                    </span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <span
                                class="text-[10px] font-bold px-2 py-0.5 rounded-full transition-all shrink-0 border"
                                :class="[
                                    staff.is_active
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'
                                        : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border-rose-200 dark:border-rose-800'
                                ]"
                            >
                                {{ staff.is_active ? '● Active' : '⛔ Blocked' }}
                            </span>
                        </div>

                        <!-- Contact Details -->
                        <div class="space-y-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <div class="flex items-center gap-2">
                                <Mail class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                <span class="truncate">{{ staff.email }}</span>
                            </div>
                            <div v-if="staff.phone" class="flex items-center gap-2">
                                <Phone class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                <span>{{ staff.phone }}</span>
                            </div>
                        </div>

                        <!-- Assigned Chambers Badges -->
                        <div class="pt-2 border-t border-slate-100 dark:border-[#21262D] space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1">
                                    <Building2 class="w-3 h-3" />
                                    <span>Assigned Chambers</span>
                                </span>
                                <span class="text-[10px] text-teal-600 dark:text-teal-400 font-bold">
                                    {{ staff.is_all_chambers ? 'All Chambers' : `${staff.chamber_names.length} Chamber(s)` }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-if="staff.is_all_chambers"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-lg bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800"
                                >
                                    <Building2 class="w-3 h-3 text-teal-600" />
                                    <span>All Chambers (Global Access)</span>
                                </span>
                                <span
                                    v-else
                                    v-for="(cName, cIdx) in staff.chamber_names"
                                    :key="cIdx"
                                    class="inline-flex items-center gap-1 text-[11px] font-medium px-2.5 py-0.5 rounded-lg bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-[#30363D]"
                                >
                                    <Building2 class="w-3 h-3 text-slate-400" />
                                    <span>{{ cName }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Active Permission Badges -->
                        <div class="pt-2 border-t border-slate-100 dark:border-[#21262D]">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
                                Granted Permissions ({{ staff.permissions.length }})
                            </p>
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="perm in staff.permissions.slice(0, 4)"
                                    :key="perm"
                                    class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300"
                                >
                                    {{ perm.replace('.', ' • ') }}
                                </span>
                                <span
                                    v-if="staff.permissions.length > 4"
                                    class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300"
                                >
                                    +{{ staff.permissions.length - 4 }} more
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-3 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between gap-2">
                        <button
                            type="button"
                            @click="toggleStatus(staff)"
                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg transition-colors cursor-pointer"
                            :class="[
                                staff.is_active
                                    ? 'text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/30'
                                    : 'text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30'
                            ]"
                        >
                            {{ staff.is_active ? 'Block Account' : 'Activate' }}
                        </button>

                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                @click="openEditModal(staff)"
                                class="p-1.5 text-slate-600 dark:text-slate-300 hover:text-teal-600 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition cursor-pointer"
                                title="Edit Staff"
                            >
                                <Edit2 class="w-3.5 h-3.5" />
                            </button>
                            <button
                                type="button"
                                @click="deleteStaff(staff)"
                                class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition cursor-pointer"
                                title="Delete Staff"
                            >
                                <Trash2 class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] p-8">
                <div class="w-12 h-12 rounded-2xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center mx-auto mb-3">
                    <Users class="w-6 h-6" />
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">No Staff Accounts Found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mt-1">
                    You have not added any staff members yet. Add receptionists or clinical assistants to help manage your patient queues across multiple chambers.
                </p>
                <button
                    v-if="!limitCheck || limitCheck.allowed"
                    @click="openCreateModal"
                    class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition"
                >
                    <UserPlus class="w-4 h-4" />
                    <span>Add First Staff Member</span>
                </button>
            </div>
        </div>

        <!-- Create / Edit Staff Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto">
            <div class="relative w-full max-w-2xl bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-2xl overflow-hidden p-6 space-y-5 my-8 max-h-[90vh] overflow-y-auto custom-scrollbar">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-[#21262D]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                            <ShieldCheck class="w-5 h-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                                {{ editingStaff ? `Edit Staff: ${editingStaff.name}` : 'Add New Staff Member' }}
                            </h2>
                            <p class="text-[11px] text-slate-400">
                                Configure credentials, assigned chamber(s), and granular permissions.
                            </p>
                        </div>
                    </div>
                    <button @click="closeModal" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white rounded-xl hover:bg-slate-100 dark:hover:bg-[#21262D]">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Full Name *
                            </label>
                            <input
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="e.g. Emily Davis"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                            />
                            <p v-if="form.errors.name" class="text-[10px] text-rose-500 font-medium">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Designation / Role Title *
                            </label>
                            <input
                                type="text"
                                v-model="form.designation"
                                required
                                placeholder="e.g. Receptionist, Clinical Assistant, Nurse"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                            />
                            <p v-if="form.errors.designation" class="text-[10px] text-rose-500 font-medium">{{ form.errors.designation }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Email Address (Login ID) *
                            </label>
                            <input
                                type="email"
                                v-model="form.email"
                                required
                                placeholder="staff@example.com"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                            />
                            <p v-if="form.errors.email" class="text-[10px] text-rose-500 font-medium">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Phone Number
                            </label>
                            <input
                                type="text"
                                v-model="form.phone"
                                placeholder="e.g. +1 (555) 234-5678"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                            />
                            <p v-if="form.errors.phone" class="text-[10px] text-rose-500 font-medium">{{ form.errors.phone }}</p>
                        </div>

                        <div class="space-y-1 sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Password {{ editingStaff ? '(Leave blank to keep unchanged)' : '*' }}
                            </label>
                            <input
                                type="password"
                                v-model="form.password"
                                :required="!editingStaff"
                                placeholder="••••••••"
                                class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500"
                            />
                            <p v-if="form.errors.password" class="text-[10px] text-rose-500 font-medium">{{ form.errors.password }}</p>
                        </div>
                    </div>

                    <!-- Multiple Chambers Assignment Section -->
                    <div class="pt-3 border-t border-slate-100 dark:border-[#21262D] space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <Building2 class="w-3.5 h-3.5 text-teal-600" />
                                    <span>Assigned Chambers (Multi-Chamber Access)</span>
                                </h3>
                                <p class="text-[11px] text-slate-400">
                                    Select which chamber(s) this staff member is allowed to access and manage.
                                </p>
                            </div>

                            <!-- Fast Chamber Quick Buttons -->
                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    @click="setAllChambersAccess"
                                    class="px-2.5 py-1 text-[10px] font-bold rounded-lg transition cursor-pointer"
                                    :class="isAllChambers ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
                                >
                                    🌐 All Chambers (Global)
                                </button>
                                <button
                                    type="button"
                                    @click="selectAllIndividualChambers"
                                    class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition cursor-pointer"
                                >
                                    Select All
                                </button>
                            </div>
                        </div>

                        <!-- Chamber Selection Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div
                                v-for="ch in chambers"
                                :key="ch.id"
                                @click="toggleChamber(ch.id)"
                                class="p-3 rounded-xl border-2 transition-all cursor-pointer flex items-center justify-between gap-2"
                                :class="[
                                    isChamberSelected(ch.id)
                                        ? 'border-teal-500 bg-teal-50/40 dark:bg-teal-950/20 shadow-xs'
                                        : (isAllChambers 
                                            ? 'border-teal-500/40 bg-teal-50/20 dark:bg-teal-950/10' 
                                            : 'border-slate-200 dark:border-[#30363D] bg-slate-50 dark:bg-[#21262D]/40 opacity-70 hover:opacity-100')
                                ]"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center shrink-0">
                                        <Building2 class="w-3.5 h-3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                            {{ ch.name }}
                                        </h4>
                                        <span class="text-[10px] text-slate-400">
                                            {{ isChamberSelected(ch.id) ? 'Assigned' : (isAllChambers ? 'Included (Global)' : 'Click to assign') }}
                                        </span>
                                    </div>
                                </div>

                                <div
                                    class="w-4 h-4 rounded-md border flex items-center justify-center shrink-0"
                                    :class="[
                                        isChamberSelected(ch.id)
                                            ? 'border-teal-600 bg-teal-600 text-white'
                                            : (isAllChambers ? 'border-teal-400 bg-teal-100 text-teal-700 dark:bg-teal-950/80 dark:text-teal-300' : 'border-slate-300 dark:border-slate-700')
                                    ]"
                                >
                                    <Check v-if="isChamberSelected(ch.id) || isAllChambers" class="w-3 h-3 stroke-[3]" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 px-1">
                            <span>
                                Status: 
                                <strong class="text-teal-600 dark:text-teal-400">
                                    {{ isAllChambers ? 'Global Access (All Current & Future Chambers)' : `Assigned to ${form.chamber_ids.length} of ${chambers.length} chambers` }}
                                </strong>
                            </span>
                        </div>
                    </div>

                    <!-- Role Permissions Section -->
                    <div class="pt-3 border-t border-slate-100 dark:border-[#21262D] space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                    <Lock class="w-3.5 h-3.5 text-teal-600" />
                                    <span>Feature & Module Permissions</span>
                                </h3>
                                <p class="text-[11px] text-slate-400">
                                    Select which specific screens and features this staff member can see.
                                </p>
                            </div>

                            <!-- Quick Preset Buttons -->
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button
                                    type="button"
                                    @click="applyReceptionistPreset"
                                    class="px-2 py-1 text-[10px] font-bold rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 transition cursor-pointer"
                                >
                                    Receptionist
                                </button>
                                <button
                                    type="button"
                                    @click="applyAssistantPreset"
                                    class="px-2 py-1 text-[10px] font-bold rounded-lg bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 hover:bg-teal-100 transition cursor-pointer"
                                >
                                    Assistant
                                </button>
                                <button
                                    type="button"
                                    @click="applyFullAccess"
                                    class="px-2 py-1 text-[10px] font-bold rounded-lg bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 hover:bg-slate-200 transition cursor-pointer"
                                >
                                    All
                                </button>
                                <button
                                    type="button"
                                    @click="clearAllPermissions"
                                    class="px-2 py-1 text-[10px] font-bold rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100 transition cursor-pointer"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <!-- Permissions Grid Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 max-h-60 overflow-y-auto custom-scrollbar pr-1">
                            <div
                                v-for="perm in availablePermissions"
                                :key="perm.key"
                                @click="togglePermission(perm.key)"
                                class="p-3 rounded-xl border-2 transition-all cursor-pointer flex items-start justify-between gap-2"
                                :class="[
                                    form.permissions.includes(perm.key)
                                        ? 'border-teal-500 bg-teal-50/40 dark:bg-teal-950/20'
                                        : 'border-slate-100 dark:border-[#21262D] bg-slate-50/50 dark:bg-[#21262D]/30 opacity-70 hover:opacity-100'
                                ]"
                            >
                                <div class="space-y-0.5 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-slate-900 dark:text-white">
                                            {{ perm.label }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-2">
                                        {{ perm.description }}
                                    </p>
                                </div>

                                <div
                                    class="w-4 h-4 rounded-md border flex items-center justify-center shrink-0 mt-0.5"
                                    :class="form.permissions.includes(perm.key) ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300 dark:border-slate-700'"
                                >
                                    <Check v-if="form.permissions.includes(perm.key)" class="w-3 h-3 stroke-[3]" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="pt-4 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white rounded-xl shadow-xs transition cursor-pointer disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : (editingStaff ? 'Update Staff Member' : 'Create Staff Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>