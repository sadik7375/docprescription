<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    Edit3,
    User,
    Phone,
    Mail,
    MapPin,
    Droplet,
    Shield,
    ShieldCheck,
    AlertCircle,
    Activity,
    FileText,
    Plus,
    Key,
    Lock,
    Save,
    Calendar,
    Users,
    Building2,
    Sparkles
} from 'lucide-vue-next';
import AiPatientHistoryTimeline from '@/Components/AiPatientHistoryTimeline.vue';

const props = defineProps({
    patient: Object,
});

const showCreateAccountModal = ref(false);

const accountForm = useForm({
    account_email: props.patient.email || '',
    account_password: '',
    account_password_confirmation: '',
});

const submitAccountCreation = () => {
    accountForm.post(route('patients.create-account', props.patient.id), {
        onSuccess: () => {
            showCreateAccountModal.value = false;
            accountForm.reset('account_password', 'account_password_confirmation');
        }
    });
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
    <Head :title="patient.name + ' - Patient Profile'" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            <!-- Top Bar Actions -->
            <div class="flex items-center justify-between">
                <Link
                    :href="route('patients.index')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors cursor-pointer shadow-xs"
                >
                    <ChevronLeft class="w-3.5 h-3.5" /> Back to Patients
                </Link>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('prescriptions.create', { patient_id: patient.id })"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] text-white dark:text-slate-950 text-xs font-bold rounded-xl transition-colors cursor-pointer shadow-xs"
                    >
                        <Plus class="w-3.5 h-3.5" /> Write Prescription
                    </Link>

                    <Link
                        :href="route('patients.edit', patient.id)"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] hover:bg-slate-50 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-colors cursor-pointer shadow-xs"
                    >
                        <Edit3 class="w-3.5 h-3.5" /> Edit Profile
                    </Link>
                </div>
            </div>

            <!-- Patient Hero Profile Card -->
            <div class="bg-white dark:bg-[#161B22] p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-400 font-bold text-xl flex items-center justify-center shrink-0">
                            {{ patient.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h1 class="text-lg font-bold text-slate-900 dark:text-white">{{ patient.name }}</h1>
                                <span class="px-2.5 py-0.5 rounded-md font-mono text-xs font-bold bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-[#30363D]">
                                    {{ patient.patient_code }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1 flex-wrap">
                                <span class="capitalize">{{ patient.gender }}</span>
                                <span>•</span>
                                <span>{{ patient.age ? patient.age + ' Years Old' : 'Age not recorded' }}</span>
                                <span v-if="patient.blood_group">•</span>
                                <span
                                    v-if="patient.blood_group"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold border"
                                    :class="bloodGroupColors[patient.blood_group]"
                                >
                                    <Droplet class="w-2.5 h-2.5" /> {{ patient.blood_group }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Status Pill -->
                    <div class="flex items-center gap-2">
                        <div v-if="patient.user_id" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                            <ShieldCheck class="w-3.5 h-3.5" />
                            <span>Portal Account Active</span>
                        </div>
                        <div v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-[#21262D] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-[#30363D]">
                            <span>Direct Patient Record</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Details Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column (Contact & Portal Account) -->
                <div class="space-y-6">
                    <!-- Contact Details Card -->
                    <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-[#21262D]">
                            Contact & Personal
                        </h3>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-start gap-3">
                                <Phone class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-[11px] text-slate-400">Phone</p>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ patient.phone || '—' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <Mail class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-[11px] text-slate-400">Email</p>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ patient.email || '—' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <MapPin class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-[11px] text-slate-400">Address</p>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ patient.address || '—' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <Users class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" />
                                <div>
                                    <p class="text-[11px] text-slate-400">Emergency Contact</p>
                                    <p class="font-medium text-slate-900 dark:text-white">
                                        {{ patient.emergency_contact_name ? patient.emergency_contact_name + ' (' + (patient.emergency_contact_phone || 'No phone') + ')' : '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chambers Visited Card (Multi-Chamber Tracking) -->
                    <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 pb-2 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <Building2 class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                                Chambers Visited
                            </span>
                            <span class="text-[10px] font-semibold text-slate-400">
                                {{ patient.chambers?.length || 0 }} Locations
                            </span>
                        </h3>

                        <div v-if="patient.chambers && patient.chambers.length > 0" class="space-y-2">
                            <div
                                v-for="ch in patient.chambers"
                                :key="ch.id"
                                class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200/70 dark:border-[#30363D] text-xs flex items-center justify-between"
                            >
                                <div class="overflow-hidden pr-2">
                                    <p class="font-bold text-slate-800 dark:text-slate-200 truncate">{{ ch.name }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">{{ ch.branch || ch.address }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-800 shrink-0">
                                    {{ ch.pivot?.visit_count || 1 }} Visits
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-xs text-slate-400 italic">No chamber visit history recorded yet.</p>
                    </div>

                    <!-- Portal Account Card (Hybrid Action) -->
                    <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#21262D]">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <Shield class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                                Portal Account
                            </h3>
                        </div>

                        <!-- Has Account View -->
                        <div v-if="patient.user" class="space-y-3 text-xs">
                            <div class="p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900">
                                <p class="text-[11px] font-semibold text-emerald-800 dark:text-emerald-300">Account Credentials Active</p>
                                <p class="text-xs text-slate-700 dark:text-slate-300 mt-1">
                                    <strong>Login ID:</strong> {{ patient.user.email }}
                                </p>
                            </div>
                        </div>

                        <!-- No Account - 1-Click Generate Account -->
                        <div v-else class="space-y-3">
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                This patient does not have a web portal login account yet.
                            </p>
                            <button
                                type="button"
                                @click="showCreateAccountModal = true"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-teal-50 dark:bg-teal-950/40 hover:bg-teal-100 dark:hover:bg-teal-900/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800 text-xs font-bold rounded-xl transition-colors cursor-pointer"
                            >
                                <Key class="w-3.5 h-3.5" />
                                <span>Create Login Account Now</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Medical Details & Consultations) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Medical Details Card -->
                    <div class="bg-white dark:bg-[#161B22] p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5 pb-2 border-b border-slate-100 dark:border-[#21262D]">
                            <Activity class="w-4 h-4 text-rose-500" />
                            Medical Background
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <!-- Allergies -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200/70 dark:border-[#30363D]">
                                <div class="flex items-center gap-1.5 font-bold text-rose-600 dark:text-rose-400 mb-1">
                                    <AlertCircle class="w-3.5 h-3.5" />
                                    <span>Known Allergies</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300">
                                    {{ patient.allergies || 'No known allergies reported.' }}
                                </p>
                            </div>

                            <!-- Chronic Illness / History -->
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200/70 dark:border-[#30363D]">
                                <div class="flex items-center gap-1.5 font-bold text-teal-600 dark:text-teal-400 mb-1">
                                    <Activity class="w-3.5 h-3.5" />
                                    <span>Chronic Conditions / History</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300">
                                    {{ patient.medical_history || 'No chronic history recorded.' }}
                                </p>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2 p-3.5 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200/70 dark:border-[#30363D]">
                                <div class="flex items-center gap-1.5 font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    <FileText class="w-3.5 h-3.5 text-slate-400" />
                                    <span>Doctor Notes</span>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300">
                                    {{ patient.note || 'No additional notes.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- AI Medical History Summarizer & Interactive Timeline -->
                    <AiPatientHistoryTimeline
                        :patient="patient"
                        :prescriptions="patient.prescriptions || []"
                        :appointments="patient.appointments || []"
                    />
                </div>
            </div>
        </div>

        <!-- Create Account Modal -->
        <div v-if="showCreateAccountModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-[#21262D]">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400">
                            <Key class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Create Portal Account</h3>
                    </div>
                    <button
                        type="button"
                        @click="showCreateAccountModal = false"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitAccountCreation" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Patient Login Email <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="accountForm.account_email"
                            type="email"
                            required
                            placeholder="patient@gmail.com"
                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        />
                        <p v-if="accountForm.errors.account_email" class="text-rose-500 text-[11px] mt-1">{{ accountForm.errors.account_email }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Password <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="accountForm.account_password"
                            type="password"
                            required
                            placeholder="Min 6 characters"
                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        />
                        <p v-if="accountForm.errors.account_password" class="text-rose-500 text-[11px] mt-1">{{ accountForm.errors.account_password }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Confirm Password <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="accountForm.account_password_confirmation"
                            type="password"
                            required
                            placeholder="Retype password"
                            class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-[#21262D]">
                        <button
                            type="button"
                            @click="showCreateAccountModal = false"
                            class="px-3.5 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-lg transition-colors cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="accountForm.processing"
                            class="px-4 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-colors cursor-pointer shadow-xs disabled:opacity-50"
                        >
                            <span>{{ accountForm.processing ? 'Creating...' : 'Create Account' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
