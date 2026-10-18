<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    FileText,
    Printer,
    Edit3,
    Trash2,
    ArrowLeft,
    User,
    Calendar,
    Building2,
    Activity,
    Pill,
    Stethoscope,
    Sparkles,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    prescription: Object,
});

const deletePrescription = () => {
    if (confirm(`Are you sure you want to delete prescription ${props.prescription.prescription_code}?`)) {
        router.delete(route('prescriptions.destroy', props.prescription.id));
    }
};
</script>

<template>
    <Head :title="`Prescription ${prescription.prescription_code} - Doc Helper AI`" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-5xl mx-auto">
            <!-- Top Control Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('prescriptions.index')"
                        class="p-2 rounded-xl bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-[#30363D] transition-colors text-slate-700 dark:text-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-bold text-slate-900 dark:text-white font-mono">
                                {{ prescription.prescription_code }}
                            </h1>
                            <span
                                class="text-[10px] font-bold px-2 py-0.5 rounded-md uppercase border"
                                :class="prescription.status === 'finalized' ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 border-amber-200'"
                            >
                                {{ prescription.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Created on {{ prescription.prescription_date }} • Chamber: {{ prescription.chamber?.name }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('prescriptions.print', prescription.id)"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer"
                    >
                        <Printer class="w-4 h-4" />
                        <span>Print Prescription Pad</span>
                    </Link>

                    <Link
                        :href="route('prescriptions.edit', prescription.id)"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-[#30363D] text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-colors cursor-pointer"
                    >
                        <Edit3 class="w-4 h-4" />
                        <span>Edit</span>
                    </Link>

                    <button
                        type="button"
                        @click="deletePrescription"
                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-pointer"
                    >
                        <Trash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <!-- Patient Profile Card -->
            <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-950/40 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 font-bold text-lg flex items-center justify-center">
                        {{ prescription.patient?.name?.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                        <Link
                            v-if="prescription.patient"
                            :href="route('patients.show', prescription.patient.id)"
                            class="text-base font-bold text-slate-900 dark:text-white hover:text-teal-600 transition-colors"
                        >
                            {{ prescription.patient?.name }}
                        </Link>
                        <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            <span class="font-mono font-medium">{{ prescription.patient?.patient_code }}</span>
                            <span>•</span>
                            <span>{{ prescription.patient?.age ? prescription.patient.age + ' Years' : 'N/A' }} ({{ prescription.patient?.gender }})</span>
                            <span v-if="prescription.patient?.phone">• {{ prescription.patient.phone }}</span>
                        </div>
                    </div>
                </div>

                <!-- Vitals Badges -->
                <div v-if="prescription.vitals" class="flex items-center gap-2 flex-wrap">
                    <div v-if="prescription.vitals.bp" class="px-3 py-1 rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-xs">
                        <span class="text-[10px] text-slate-400 block font-medium">BP</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ prescription.vitals.bp }} mmHg</span>
                    </div>
                    <div v-if="prescription.vitals.pulse" class="px-3 py-1 rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-xs">
                        <span class="text-[10px] text-slate-400 block font-medium">Pulse</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ prescription.vitals.pulse }} bpm</span>
                    </div>
                    <div v-if="prescription.vitals.temperature" class="px-3 py-1 rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-xs">
                        <span class="text-[10px] text-slate-400 block font-medium">Temp</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ prescription.vitals.temperature }} °F</span>
                    </div>
                    <div v-if="prescription.vitals.weight" class="px-3 py-1 rounded-xl bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] text-xs">
                        <span class="text-[10px] text-slate-400 block font-medium">Weight</span>
                        <span class="font-bold text-slate-900 dark:text-white">{{ prescription.vitals.weight }} kg</span>
                    </div>
                </div>
            </div>

            <!-- 2-Column Content Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Column: Findings -->
                <div class="lg:col-span-4 space-y-4">
                    <!-- Chief Complaints -->
                    <div v-if="prescription.chief_complaints?.length" class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-2">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Stethoscope class="w-3.5 h-3.5 text-blue-600" />
                            <span>Chief Complaints (C/C)</span>
                        </h3>
                        <div class="space-y-1">
                            <div
                                v-for="(cc, idx) in prescription.chief_complaints"
                                :key="idx"
                                class="text-xs text-slate-700 dark:text-slate-300 flex items-center justify-between"
                            >
                                <span class="font-medium">• {{ cc.complaint }}</span>
                                <span class="text-[10px] text-slate-400">({{ cc.duration }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnosis -->
                    <div v-if="prescription.diagnoses?.length" class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-2">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Sparkles class="w-3.5 h-3.5 text-purple-600" />
                            <span>Diagnosis (Dx)</span>
                        </h3>
                        <div class="space-y-1.5">
                            <div
                                v-for="(dx, idx) in prescription.diagnoses"
                                :key="idx"
                                class="text-xs font-bold text-slate-900 dark:text-white flex items-center justify-between"
                            >
                                <span>• {{ dx.diagnosis }}</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase bg-purple-50 text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                                    {{ dx.type }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Investigations -->
                    <div v-if="prescription.investigations?.length" class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-2">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <FileText class="w-3.5 h-3.5 text-amber-600" />
                            <span>Investigations (Ix)</span>
                        </h3>
                        <div class="space-y-1">
                            <div
                                v-for="(ix, idx) in prescription.investigations"
                                :key="idx"
                                class="text-xs text-slate-700 dark:text-slate-300"
                            >
                                • {{ ix.test_name }}
                            </div>
                        </div>
                    </div>

                    <!-- Advices -->
                    <div v-if="prescription.clinical_advices?.length" class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-2">
                        <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600" />
                            <span>Clinical Advices</span>
                        </h3>
                        <ul class="space-y-1 text-xs text-slate-700 dark:text-slate-300">
                            <li v-for="(adv, idx) in prescription.clinical_advices" :key="idx">
                                ✓ {{ adv }}
                            </li>
                        </ul>
                    </div>

                    <!-- Follow-up -->
                    <div v-if="prescription.follow_up_date" class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-1 text-xs">
                        <span class="font-bold text-slate-900 dark:text-white block">Next Visit / Follow-up:</span>
                        <p class="text-teal-600 dark:text-teal-400 font-bold">{{ prescription.follow_up_date }}</p>
                        <p v-if="prescription.follow_up_notes" class="text-slate-400 text-[11px]">{{ prescription.follow_up_notes }}</p>
                    </div>
                </div>

                <!-- Right Column: Medications -->
                <div class="lg:col-span-8 space-y-4">
                    <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="text-2xl font-serif font-black text-teal-600 dark:text-teal-400">℞</span>
                            <span>Prescribed Medications</span>
                        </h3>

                        <div class="space-y-3">
                            <div
                                v-for="(item, idx) in prescription.items"
                                :key="idx"
                                class="p-4 bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200/80 dark:border-[#30363D] rounded-xl flex items-start gap-3"
                            >
                                <span class="w-6 h-6 rounded-lg bg-[#0A0D36] dark:bg-teal-500 text-white dark:text-slate-950 font-mono font-bold text-xs flex items-center justify-center shrink-0">
                                    {{ idx + 1 }}
                                </span>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-bold text-sm text-slate-900 dark:text-white">
                                            {{ item.medicine_name }}
                                        </p>
                                        <span v-if="item.generic_name" class="text-xs text-slate-400 italic">
                                            ({{ item.generic_name }})
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-slate-700 dark:text-slate-300">
                                        <span class="font-mono font-bold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/60 px-2 py-0.5 rounded border border-teal-200 dark:border-teal-800">
                                            {{ item.dosage }}
                                        </span>
                                        <span>— {{ item.duration }}</span>
                                        <span class="text-slate-500">({{ item.instruction }})</span>
                                    </div>
                                    <p v-if="item.extra_notes" class="text-xs text-amber-600 dark:text-amber-400 italic">
                                        * {{ item.extra_notes }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Private Doctor Notes -->
                    <div v-if="prescription.doctor_notes" class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-1 text-xs">
                        <span class="font-bold text-slate-900 dark:text-white block">Private Doctor Notes:</span>
                        <p class="text-slate-600 dark:text-slate-400">{{ prescription.doctor_notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
