<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    UserPlus,
    User,
    Phone,
    Mail,
    Heart,
    Shield,
    Key,
    Lock,
    AlertCircle,
    Save,
    FileText,
    Activity,
    Droplet
} from 'lucide-vue-next';

const showMedicalSection = ref(false);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    gender: 'male',
    age: '',
    dob: '',
    blood_group: '',
    marital_status: '',
    address: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    allergies: '',
    medical_history: '',
    note: '',

    // Portal Account toggle
    create_account: false,
    account_email: '',
    account_password: '',
    account_password_confirmation: '',
});

// Auto sync email if entered
watch(() => form.email, (newVal) => {
    if (form.create_account && !form.account_email) {
        form.account_email = newVal;
    }
});

watch(() => form.create_account, (isEnabled) => {
    if (isEnabled && form.email && !form.account_email) {
        form.account_email = form.email;
    }
});

const submit = () => {
    form.post(route('patients.store'));
};
</script>

<template>
    <Head title="Register Patient - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Form Container Card -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <!-- Card Header -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <UserPlus class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                            Register New Patient
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Enter patient personal details, medical info, and optionally create a login account.
                        </p>
                    </div>

                    <Link
                        :href="route('patients.index')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0A0D36] dark:bg-slate-800 hover:bg-[#12164A] text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer"
                    >
                        <ChevronLeft class="w-3.5 h-3.5" /> Back
                    </Link>
                </div>

                <form @submit.prevent="submit" class="p-5 sm:p-6 space-y-8">
                    <!-- SECTION 1: Personal Information -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-[#21262D]">
                            <User class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                1. Personal & Contact Information
                            </h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Full Name -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Full Name <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="e.g. Mohammad Rahman"
                                    required
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                />
                                <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                            </div>

                            <!-- Phone Number -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Phone Number
                                </label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="e.g. +1 (555) 234-5678"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                />
                                <p v-if="form.errors.phone" class="text-rose-500 text-[11px] mt-1">{{ form.errors.phone }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Contact Email
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="e.g. patient@gmail.com"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                />
                                <p v-if="form.errors.email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.email }}</p>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Gender <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    v-model="form.gender"
                                    required
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                                >
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <!-- Age -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Age (Years)
                                </label>
                                <input
                                    v-model="form.age"
                                    type="number"
                                    min="0"
                                    max="150"
                                    placeholder="e.g. 35"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                />
                            </div>

                            <!-- Blood Group -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Blood Group
                                </label>
                                <select
                                    v-model="form.blood_group"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                                >
                                    <option value="">Select Blood Group</option>
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

                            <!-- Marital Status -->
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Marital Status
                                </label>
                                <select
                                    v-model="form.marital_status"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                                >
                                    <option value="">Select Marital Status</option>
                                    <option value="Single">Single</option>
                                    <option value="Married">Married</option>
                                    <option value="Divorced">Divorced</option>
                                    <option value="Widowed">Widowed</option>
                                </select>
                            </div>

                            <!-- Address (2 Cols) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Address
                                </label>
                                <input
                                    v-model="form.address"
                                    type="text"
                                    placeholder="e.g. 123 Main St, Apartment 4B, Boston, MA"
                                    class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Medical & Emergency Details (Optional Toggle) -->
                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-[#F7F9FB] dark:bg-[#21262D]/60 border border-slate-200/80 dark:border-[#30363D] space-y-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5">
                                        <Activity class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <label for="add_medical_toggle" class="text-xs font-bold text-slate-900 dark:text-white cursor-pointer select-none">
                                            2. Medical Background & Emergency Contact (Optional)
                                        </label>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Add known drug allergies, past chronic conditions, emergency contact person, or doctor notes.
                                        </p>
                                    </div>
                                </div>

                                <!-- Toggle switch -->
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input
                                        id="add_medical_toggle"
                                        v-model="showMedicalSection"
                                        type="checkbox"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:bg-slate-700 peer-checked:bg-rose-600"></div>
                                </label>
                            </div>

                            <!-- Inputs (Shown when toggle is ON) -->
                            <div v-if="showMedicalSection" class="pt-3 border-t border-slate-200 dark:border-[#30363D] grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Emergency Contact Name -->
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Emergency Contact Person
                                    </label>
                                    <input
                                        v-model="form.emergency_contact_name"
                                        type="text"
                                        placeholder="e.g. Spouse / Brother / Parent"
                                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500"
                                    />
                                </div>

                                <!-- Emergency Contact Phone -->
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Emergency Contact Phone
                                    </label>
                                    <input
                                        v-model="form.emergency_contact_phone"
                                        type="text"
                                        placeholder="e.g. +1 (555) 987-6543"
                                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500"
                                    />
                                </div>

                                <!-- Known Allergies -->
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Known Allergies (if any)
                                    </label>
                                    <textarea
                                        v-model="form.allergies"
                                        rows="2"
                                        placeholder="e.g. Penicillin, Dust, Sulfa, Peanuts..."
                                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500"
                                    ></textarea>
                                </div>

                                <!-- Medical History / Chronic Illness -->
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Chronic Diseases / Past Medical History
                                    </label>
                                    <textarea
                                        v-model="form.medical_history"
                                        rows="2"
                                        placeholder="e.g. Diabetes Type 2, Hypertension, Asthma..."
                                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500"
                                    ></textarea>
                                </div>

                                <!-- General Note -->
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        General Doctor Note / Remarks
                                    </label>
                                    <input
                                        v-model="form.note"
                                        type="text"
                                        placeholder="e.g. Referred by Dr. Alexander, VIP patient"
                                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Patient Portal Account (Hybrid Toggle) -->
                    <div class="space-y-4 pt-2">
                        <div class="p-4 rounded-2xl bg-[#F7F9FB] dark:bg-[#21262D]/60 border border-slate-200/80 dark:border-[#30363D] space-y-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 shrink-0 mt-0.5">
                                        <Shield class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <label for="create_account_toggle" class="text-xs font-bold text-slate-900 dark:text-white cursor-pointer select-none">
                                            Create Patient Portal Login Account
                                        </label>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                            Allow this patient to log into their web portal to view live prescriptions, history, and appointment tickets.
                                        </p>
                                    </div>
                                </div>

                                <!-- Toggle switch -->
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input
                                        id="create_account_toggle"
                                        v-model="form.create_account"
                                        type="checkbox"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:bg-slate-700 peer-checked:bg-teal-600"></div>
                                </label>
                            </div>

                            <!-- Account Credentials Inputs (Shown when toggle is ON) -->
                            <div v-if="form.create_account" class="pt-3 border-t border-slate-200 dark:border-[#30363D] grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Login Email <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.account_email"
                                        type="email"
                                        required
                                        placeholder="patient@email.com"
                                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                    />
                                    <p v-if="form.errors.account_email" class="text-rose-500 text-[11px] mt-1">{{ form.errors.account_email }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Password <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.account_password"
                                        type="password"
                                        required
                                        placeholder="Min 6 characters"
                                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                    />
                                    <p v-if="form.errors.account_password" class="text-rose-500 text-[11px] mt-1">{{ form.errors.account_password }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                        Confirm Password <span class="text-rose-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.account_password_confirmation"
                                        type="password"
                                        required
                                        placeholder="Retype password"
                                        class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action: Save Button -->
                    <div class="pt-4 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-end gap-3">
                        <Link
                            :href="route('patients.index')"
                            class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-pointer"
                        >
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#0A0D36] hover:bg-[#12164A] text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm disabled:opacity-50"
                        >
                            <Save class="w-4 h-4" />
                            <span>{{ form.processing ? 'Registering...' : 'Save & Register Patient' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
