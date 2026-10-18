<script setup>
import { ref, computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import {
    Stethoscope,
    ShieldCheck,
    Award,
    Building2,
    Sparkles,
    Check,
    User,
    Mail,
    Phone,
    FileText,
    Eye
} from 'lucide-vue-next';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    doctorProfile: Object,
});

const user = usePage().props.auth.user;
const profile = props.doctorProfile || user.doctor_profile || {};

// Format initial degrees (if array, join with comma)
const initialDegrees = computed(() => {
    if (!profile.degrees) return '';
    if (Array.isArray(profile.degrees)) return profile.degrees.join(', ');
    return String(profile.degrees);
});

// Format initial experiences (if array of objects or strings, format cleanly)
const initialExperiences = computed(() => {
    if (!profile.experiences) return '';
    if (Array.isArray(profile.experiences)) {
        return profile.experiences
            .map(exp => {
                if (typeof exp === 'object' && exp !== null) {
                    return [exp.designation, exp.institute].filter(Boolean).join(' - ');
                }
                return String(exp);
            })
            .filter(Boolean)
            .join('\n');
    }
    return String(profile.experiences);
});

const form = useForm({
    name: user.name || '',
    email: user.email || '',
    phone: user.phone || '',
    title: profile.title || 'Dr.',
    specialty: profile.specialty || '',
    bmdc_reg_no: profile.bmdc_reg_no || '',
    degrees: initialDegrees.value,
    experiences: initialExperiences.value,
    signature: profile.signature || '',
});

// Clean doctor display name for preview avoiding duplicate 'Dr.' / 'Prof.'
const formattedDoctorDisplayName = computed(() => {
    if (!form.name) return 'Dr. Practitioner Name';
    const name = form.name.trim();
    const title = form.title?.trim() || '';
    if (!title) return name;
    
    // Check if name already starts with title
    if (name.toLowerCase().startsWith(title.toLowerCase())) {
        return name;
    }
    // Strip redundant leading prefixes from name if title will provide it
    const cleanName = name.replace(/^(Dr\.|Prof\.|Prof\. Dr\.|Assoc\.|Asst\.|Consultant|Dr)\s+/i, '');
    return `${title} ${cleanName}`;
});

// Degree preset quick chips
const commonDegrees = [
    'MBBS', 'FCPS', 'MD', 'MRCP (UK)', 'MS', 'FRCS', 'DGO', 'DCH', 'FRCP', 'MPH', 'PhD'
];

const addDegreeTag = (deg) => {
    const current = form.degrees ? form.degrees.split(',').map(s => s.trim()).filter(Boolean) : [];
    if (!current.includes(deg)) {
        current.push(deg);
        form.degrees = current.join(', ');
    }
};

const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section class="space-y-6">
        <header class="border-b border-slate-100 dark:border-[#21262D] pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 flex items-center justify-center font-bold">
                    <Stethoscope class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">
                        Doctor Profile & Medical Credentials
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Manage your professional clinical credentials, medical license registration, degrees, and prescription header seal.
                    </p>
                </div>
            </div>
        </header>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- SECTION 1: Personal & Account Identity -->
            <div class="space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                    <User class="w-3.5 h-3.5 text-teal-600" />
                    <span>Account Identity</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Title / Honorific -->
                    <div>
                        <InputLabel for="title" value="Honorific / Title" />
                        <select
                            id="title"
                            v-model="form.title"
                            class="mt-1 block w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-semibold"
                        >
                            <option value="Dr.">Dr.</option>
                            <option value="Prof. Dr.">Prof. Dr.</option>
                            <option value="Assoc. Prof. Dr.">Assoc. Prof. Dr.</option>
                            <option value="Asst. Prof. Dr.">Asst. Prof. Dr.</option>
                            <option value="Consultant Dr.">Consultant Dr.</option>
                            <option value="Dr. (Mrs.)">Dr. (Mrs.)</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.title" />
                    </div>

                    <!-- Full Name -->
                    <div>
                        <InputLabel for="name" value="Doctor Full Name *" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full text-xs"
                            v-model="form.name"
                            required
                            placeholder="e.g. John Doe"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <!-- Mobile / Phone -->
                    <div>
                        <InputLabel for="phone" value="Phone / Mobile Number" />
                        <TextInput
                            id="phone"
                            type="text"
                            class="mt-1 block w-full text-xs"
                            v-model="form.phone"
                            placeholder="+1 234 567 890"
                        />
                        <InputError class="mt-1" :message="form.errors.phone" />
                    </div>
                </div>

                <!-- Email Address -->
                <div class="max-w-md">
                    <InputLabel for="email" value="Email Address *" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full text-xs"
                        v-model="form.email"
                        required
                        placeholder="doctor@clinic.com"
                    />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>
            </div>

            <!-- SECTION 2: Professional Medical Credentials -->
            <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-[#21262D]">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                    <Award class="w-3.5 h-3.5 text-teal-600" />
                    <span>Clinical Credentials & License</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Medical Registration / License Number -->
                    <div>
                        <InputLabel for="bmdc_reg_no" value="Medical Registration / License No." />
                        <div class="relative mt-1">
                            <ShieldCheck class="w-4 h-4 text-teal-600 absolute left-3 top-1/2 -translate-y-1/2" />
                            <TextInput
                                id="bmdc_reg_no"
                                type="text"
                                class="block w-full pl-9 text-xs font-mono font-bold"
                                v-model="form.bmdc_reg_no"
                                placeholder="e.g. A-12345 or MED-98472"
                            />
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Printed on prescriptions as "Reg. No: A-12345".
                        </p>
                        <InputError class="mt-1" :message="form.errors.bmdc_reg_no" />
                    </div>

                    <!-- Specialty / Department -->
                    <div>
                        <InputLabel for="specialty" value="Specialization / Designation" />
                        <TextInput
                            id="specialty"
                            type="text"
                            class="mt-1 block w-full text-xs"
                            v-model="form.specialty"
                            placeholder="e.g. Consultant Cardiologist & Specialist Physician"
                        />
                        <p class="text-[11px] text-slate-400 mt-1">
                            Displayed under doctor name on prescription pads.
                        </p>
                        <InputError class="mt-1" :message="form.errors.specialty" />
                    </div>
                </div>

                <!-- Degrees & Qualifications -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <InputLabel for="degrees" value="Degrees & Qualifications (Comma Separated)" />
                        <span class="text-[11px] text-slate-400">Click presets below to append</span>
                    </div>
                    <TextInput
                        id="degrees"
                        type="text"
                        class="block w-full text-xs font-medium"
                        v-model="form.degrees"
                        placeholder="e.g. MBBS, FCPS (Medicine), MD (Cardiology), MRCP (UK)"
                    />
                    <InputError class="mt-1" :message="form.errors.degrees" />

                    <!-- Quick Degree Badges -->
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        <button
                            v-for="deg in commonDegrees"
                            :key="deg"
                            type="button"
                            @click="addDegreeTag(deg)"
                            class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-[#21262D] hover:bg-teal-50 dark:hover:bg-teal-950/40 text-slate-700 dark:text-slate-300 hover:text-teal-700 dark:hover:text-teal-300 text-[11px] font-semibold transition-colors cursor-pointer"
                        >
                            + {{ deg }}
                        </button>
                    </div>
                </div>

                <!-- Hospital / Workplace / Experience -->
                <div>
                    <InputLabel for="experiences" value="Hospital Affiliation / Workplace Experience" />
                    <textarea
                        id="experiences"
                        v-model="form.experiences"
                        rows="2"
                        placeholder="e.g. Senior Consultant at Central Hospital&#10;Ex-Assistant Professor, Department of Medicine"
                        class="mt-1 block w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-teal-500"
                    ></textarea>
                    <InputError class="mt-1" :message="form.errors.experiences" />
                </div>

                <!-- Prescription Seal Text -->
                <div>
                    <InputLabel for="signature" value="Prescription Signature & Seal Note" />
                    <TextInput
                        id="signature"
                        type="text"
                        class="mt-1 block w-full text-xs"
                        v-model="form.signature"
                        placeholder="e.g. Specialist Signature & Official Seal"
                    />
                    <InputError class="mt-1" :message="form.errors.signature" />
                </div>
            </div>

            <!-- SECTION 3: Live Prescription Header Preview -->
            <div class="p-4 rounded-2xl bg-teal-50/60 dark:bg-teal-950/20 border border-teal-200/80 dark:border-teal-900/60 space-y-2.5">
                <div class="flex items-center gap-2 text-teal-800 dark:text-teal-300">
                    <Eye class="w-4 h-4" />
                    <span class="text-xs font-bold uppercase tracking-wider">Live Prescription Header Preview</span>
                </div>

                <div class="p-4 bg-white dark:bg-[#161B22] rounded-xl border border-slate-200 dark:border-[#30363D] shadow-xs">
                    <h4 class="text-lg font-black text-teal-800 dark:text-teal-400">
                        {{ formattedDoctorDisplayName }}
                    </h4>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200 mt-0.5">
                        {{ form.degrees || 'MBBS, FCPS (Medicine)' }}
                    </p>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400">
                        {{ form.specialty || 'Consultant Specialist Physician' }}
                    </p>
                    <p class="text-[10px] font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                        Reg. No: {{ form.bmdc_reg_no || 'MED-12345' }}
                    </p>
                </div>
            </div>

            <!-- Submit Action -->
            <div class="flex items-center gap-4 pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                >
                    <span>{{ form.processing ? 'Saving Credentials...' : 'Save Doctor Profile' }}</span>
                </button>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5"
                    >
                        <Check class="w-4 h-4" />
                        <span>Doctor profile & credentials saved successfully.</span>
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
