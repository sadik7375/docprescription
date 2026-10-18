<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Users,
    UserPlus,
    Search,
    Filter,
    Eye,
    Edit3,
    Trash2,
    CheckCircle2,
    UserCheck,
    Droplet,
    Phone,
    Mail,
    X,
    Sparkles,
    ShieldCheck,
    ChevronLeft,
    ChevronRight
} from 'lucide-vue-next';

const props = defineProps({
    patients: Object,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters.search || '');
const gender = ref(props.filters.gender || '');
const bloodGroup = ref(props.filters.blood_group || '');

const deleteModalOpen = ref(false);
const patientToDelete = ref(null);

const applyFilters = () => {
    router.get(
        route('patients.index'),
        {
            search: search.value,
            gender: gender.value,
            blood_group: bloodGroup.value,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilters = () => {
    search.value = '';
    gender.value = '';
    bloodGroup.value = '';
    applyFilters();
};

const confirmDelete = (patient) => {
    patientToDelete.value = patient;
    deleteModalOpen.value = true;
};

const deletePatient = () => {
    if (patientToDelete.value) {
        router.delete(route('patients.destroy', patientToDelete.value.id), {
            onSuccess: () => {
                deleteModalOpen.value = false;
                patientToDelete.value = null;
            }
        });
    }
};

const bloodGroupColors = {
    'A+': 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
    'A-': 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
    'B+': 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
    'B-': 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
    'O+': 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
    'O-': 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
    'AB+': 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
    'AB-': 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
};
</script>

<template>
    <Head title="Patient Management - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Header & Action -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <Users class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        Patient Management
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage all registered clinic patients, medical records, and patient portal accounts.
                    </p>
                </div>

                <Link
                    :href="route('patients.create')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl transition-all shadow-sm cursor-pointer"
                >
                    <UserPlus class="w-4 h-4" />
                    <span>Register New Patient</span>
                </Link>
            </div>

            <!-- Stats Overview Cards (3 Clean Cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total Patients</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.total }}</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Male Patients</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.male }}</p>
                </div>
                <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Female Patients</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ stats.female }}</p>
                </div>
            </div>

            <!-- Search & Filters Bar -->
            <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Search Input -->
                    <div class="lg:col-span-2 relative">
                        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search name, phone, code (PID)..."
                            class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 focus:bg-white"
                        />
                    </div>

                    <!-- Gender Filter -->
                    <div>
                        <select
                            v-model="gender"
                            @change="applyFilters"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        >
                            <option value="">All Genders</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Blood Group Filter -->
                    <div>
                        <select
                            v-model="bloodGroup"
                            @change="applyFilters"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        >
                            <option value="">All Blood Groups</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Patient Table -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                        <thead class="bg-slate-50/75 dark:bg-[#21262D]/60 text-[11px] font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider border-b border-slate-200/80 dark:border-[#30363D]">
                            <tr>
                                <th class="py-3 px-4">Patient Code / Name</th>
                                <th class="py-3 px-4">Contact</th>
                                <th class="py-3 px-4">Age / Gender</th>
                                <th class="py-3 px-4">Blood Group</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr
                                v-for="p in patients.data"
                                :key="p.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-[#21262D]/40 transition-colors"
                            >
                                <!-- Patient Code & Name -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] flex items-center justify-center font-bold text-slate-700 dark:text-teal-400 shrink-0">
                                            {{ p.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <Link :href="route('patients.show', p.id)" class="font-bold text-slate-900 dark:text-white hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
                                                {{ p.name }}
                                            </Link>
                                            <div class="text-[11px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">
                                                {{ p.patient_code }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Info -->
                                <td class="py-3 px-4">
                                    <div class="space-y-0.5">
                                        <div v-if="p.phone" class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                            <Phone class="w-3 h-3 text-slate-400" />
                                            <span>{{ p.phone }}</span>
                                        </div>
                                        <div v-if="p.email" class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500">
                                            <Mail class="w-3 h-3 text-slate-400" />
                                            <span>{{ p.email }}</span>
                                        </div>
                                        <span v-if="!p.phone && !p.email" class="text-slate-400 italic">No contact</span>
                                    </div>
                                </td>

                                <!-- Age / Gender -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-medium text-slate-900 dark:text-white">
                                            {{ p.age ? p.age + ' yrs' : 'N/A' }}
                                        </span>
                                        <span class="text-slate-300 dark:text-slate-600">•</span>
                                        <span class="capitalize text-slate-500 dark:text-slate-400">{{ p.gender }}</span>
                                    </div>
                                </td>

                                <!-- Blood Group -->
                                <td class="py-3 px-4">
                                    <span
                                        v-if="p.blood_group"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold border"
                                        :class="bloodGroupColors[p.blood_group] || 'bg-slate-100 text-slate-700 border-slate-200'"
                                    >
                                        <Droplet class="w-2.5 h-2.5" />
                                        {{ p.blood_group }}
                                    </span>
                                    <span v-else class="text-slate-400 text-[11px]">—</span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View Profile -->
                                        <Link
                                            :href="route('patients.show', p.id)"
                                            class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                            title="View Patient Profile"
                                        >
                                            <Eye class="w-4 h-4" />
                                        </Link>

                                        <!-- Edit -->
                                        <Link
                                            :href="route('patients.edit', p.id)"
                                            class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                            title="Edit Patient"
                                        >
                                            <Edit3 class="w-4 h-4" />
                                        </Link>

                                        <!-- Delete -->
                                        <button
                                            type="button"
                                            @click="confirmDelete(p)"
                                            class="p-1.5 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                                            title="Delete Patient"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="!patients.data || patients.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <Users class="w-8 h-8 mx-auto mb-2 opacity-30" />
                                    <p class="font-medium">No patients found</p>
                                    <p class="text-[11px] mt-1">Try changing your search or filter parameters.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="patients.links && patients.links.length > 3" class="p-4 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Showing <span class="font-medium">{{ patients.from || 0 }}</span> to <span class="font-medium">{{ patients.to || 0 }}</span> of <span class="font-medium">{{ patients.total }}</span> patients
                    </p>

                    <div class="flex items-center gap-1">
                        <template v-for="(link, i) in patients.links" :key="i">
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

        <!-- Delete Confirmation Modal -->
        <div v-if="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] rounded-2xl max-w-sm w-full p-5 shadow-xl space-y-4">
                <div class="w-10 h-10 rounded-full bg-rose-50 dark:bg-rose-950/40 text-rose-600 flex items-center justify-center">
                    <Trash2 class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Delete Patient Record?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Are you sure you want to delete patient <strong class="text-slate-800 dark:text-slate-200">{{ patientToDelete?.name }}</strong> ({{ patientToDelete?.patient_code }})? This action cannot be undone.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="deleteModalOpen = false"
                        class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="deletePatient"
                        class="px-4 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors cursor-pointer shadow-xs"
                    >
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
