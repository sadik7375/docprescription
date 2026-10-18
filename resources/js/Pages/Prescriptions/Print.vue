<script setup>
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Printer, ArrowLeft, Download } from 'lucide-vue-next';

const props = defineProps({
    prescription: Object,
});

const printPrescription = () => {
    window.print();
};

const formatDegrees = (degrees) => {
    if (!degrees) return '';
    if (Array.isArray(degrees)) return degrees.join(', ');
    if (typeof degrees === 'string') {
        try {
            const parsed = JSON.parse(degrees);
            if (Array.isArray(parsed)) return parsed.join(', ');
        } catch (e) {}
        return degrees;
    }
    return String(degrees);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

onMounted(() => {
    // Optionally auto open print dialog if desired
});
</script>

<template>
    <Head :title="`Prescription ${prescription.prescription_code} - Doc Helper AI`" />

    <div class="min-h-screen bg-slate-100 dark:bg-slate-900 py-6 px-4 print:p-0 print:bg-white text-slate-900">
        <!-- Floating Print Action Bar (Hidden on Print) -->
        <div class="max-w-4xl mx-auto mb-4 flex items-center justify-between gap-3 bg-white dark:bg-[#161B22] p-3 rounded-2xl shadow-sm border border-slate-200 dark:border-[#21262D] print:hidden">
            <div class="flex items-center gap-2">
                <Link
                    :href="route('prescriptions.show', prescription.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-[#30363D] text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors"
                >
                    <ArrowLeft class="w-4 h-4" />
                    <span>Back</span>
                </Link>
                <span class="text-xs font-mono font-bold text-slate-500 dark:text-slate-400">
                    {{ prescription.prescription_code }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="printPrescription"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer transition-all"
                >
                    <Printer class="w-4 h-4" />
                    <span>Print Prescription (A4)</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- THE A4 PRESCRIPTION PAD (Standard Medical Layout)                         -->
        <!-- ========================================================================= -->
        <div class="max-w-4xl mx-auto bg-white rounded-none shadow-md print:shadow-none print:max-w-none print:w-full border border-slate-200 print:border-none p-8 sm:p-12 print:p-6 min-h-[1050px] flex flex-col justify-between text-slate-900 font-sans">
            
            <div>
                <!-- 1. Header: Doctor Credentials & Chamber Info -->
                <div class="border-b-2 border-teal-700 pb-4 mb-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <!-- Doctor Info -->
                    <div>
                        <h1 class="text-xl sm:text-2xl font-black tracking-tight text-teal-800">
                            {{ prescription.doctor?.name || 'Dr. Practitioner' }}
                        </h1>
                        <p v-if="formatDegrees(prescription.doctor?.doctor_profile?.degrees)" class="text-xs font-bold text-slate-700 mt-0.5">
                            {{ formatDegrees(prescription.doctor?.doctor_profile?.degrees) }}
                        </p>
                        <p v-if="prescription.doctor?.doctor_profile?.specialty || prescription.doctor?.doctor_profile?.specialization" class="text-[11px] text-slate-600">
                            {{ prescription.doctor?.doctor_profile?.specialty || prescription.doctor?.doctor_profile?.specialization }}
                        </p>
                        <p v-if="prescription.doctor?.doctor_profile?.bmdc_reg_no || prescription.doctor?.doctor_profile?.bmdc_reg_number" class="text-[10px] font-mono text-slate-500 mt-0.5">
                            Reg. No: {{ prescription.doctor?.doctor_profile?.bmdc_reg_no || prescription.doctor?.doctor_profile?.bmdc_reg_number }}
                        </p>
                    </div>

                    <!-- Chamber Info -->
                    <div class="text-left sm:text-right">
                        <h2 class="text-sm font-bold text-slate-900">
                            {{ prescription.chamber?.name || 'Medical Centre' }}
                        </h2>
                        <p v-if="prescription.chamber?.branch" class="text-xs font-medium text-slate-700">
                            {{ prescription.chamber.branch }}
                        </p>
                        <p class="text-[11px] text-slate-600 max-w-xs sm:ml-auto">
                            {{ prescription.chamber?.address || '' }}
                        </p>
                        <p class="text-[10px] text-slate-500 mt-0.5">
                            <span v-if="prescription.chamber?.phone">Phone: {{ prescription.chamber.phone }} | </span>
                            <span>{{ prescription.chamber?.visiting_time || 'Daily Chamber' }}</span>
                        </p>
                    </div>
                </div>

                <!-- 2. Patient Demographics & Vitals Strip -->
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-2.5 mb-5 text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold block">Patient Name:</span>
                            <span class="font-bold text-slate-900">{{ prescription.patient?.name }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold block">Age / Gender:</span>
                            <span class="font-bold">{{ prescription.patient?.age ? prescription.patient.age + ' Years' : 'N/A' }}, {{ prescription.patient?.gender }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold block">Date:</span>
                            <span class="font-bold">{{ formatDate(prescription.prescription_date) }}</span>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="text-[10px] text-slate-500 uppercase font-semibold block">Patient ID / RX:</span>
                            <span class="font-mono font-bold text-teal-800">{{ prescription.prescription_code }}</span>
                        </div>
                    </div>

                    <!-- Vitals Line (If present) -->
                    <div v-if="prescription.vitals && (prescription.vitals.bp || prescription.vitals.pulse || prescription.vitals.weight || prescription.vitals.temperature)" class="mt-2 pt-2 border-t border-slate-200 flex items-center gap-4 flex-wrap text-[11px] text-slate-700">
                        <span v-if="prescription.vitals.bp"><strong class="text-slate-900">BP:</strong> {{ prescription.vitals.bp }} mmHg</span>
                        <span v-if="prescription.vitals.pulse"><strong class="text-slate-900">Pulse:</strong> {{ prescription.vitals.pulse }} bpm</span>
                        <span v-if="prescription.vitals.temperature"><strong class="text-slate-900">Temp:</strong> {{ prescription.vitals.temperature }} °F</span>
                        <span v-if="prescription.vitals.weight"><strong class="text-slate-900">Weight:</strong> {{ prescription.vitals.weight }} kg</span>
                        <span v-if="prescription.vitals.height"><strong class="text-slate-900">Height:</strong> {{ prescription.vitals.height }}</span>
                        <span v-if="prescription.vitals.bmi"><strong class="text-slate-900">BMI:</strong> {{ prescription.vitals.bmi }}</span>
                    </div>
                </div>

                <!-- 3. Two-Column Clinical & Rx Layout -->
                <div class="grid grid-cols-12 gap-6 min-h-[550px]">
                    
                    <!-- LEFT COLUMN: C/C, O/E, Dx, Ix (~35% width) -->
                    <div class="col-span-4 border-r-2 border-slate-200 pr-4 space-y-4 text-xs">
                        
                        <!-- Chief Complaints (C/C) -->
                        <div v-if="prescription.chief_complaints?.length">
                            <h3 class="font-bold text-slate-900 uppercase tracking-wide border-b border-slate-300 pb-0.5 mb-1.5 text-[11px] text-teal-900">
                                Chief Complaints (C/C)
                            </h3>
                            <ul class="space-y-1">
                                <li v-for="(cc, idx) in prescription.chief_complaints" :key="idx" class="leading-snug">
                                    <span class="font-semibold text-slate-800">• {{ cc.complaint }}</span>
                                    <span v-if="cc.duration" class="text-slate-500 text-[10px] ml-1">({{ cc.duration }})</span>
                                </li>
                            </ul>
                        </div>

                        <!-- On Examination (O/E) -->
                        <div v-if="prescription.on_examinations?.length">
                            <h3 class="font-bold text-slate-900 uppercase tracking-wide border-b border-slate-300 pb-0.5 mb-1.5 text-[11px] text-teal-900">
                                On Examination (O/E)
                            </h3>
                            <ul class="space-y-1">
                                <li v-for="(oe, idx) in prescription.on_examinations" :key="idx" class="leading-snug text-slate-700">
                                    • {{ oe.finding }}
                                </li>
                            </ul>
                        </div>

                        <!-- Diagnosis (Dx) -->
                        <div v-if="prescription.diagnoses?.length">
                            <h3 class="font-bold text-slate-900 uppercase tracking-wide border-b border-slate-300 pb-0.5 mb-1.5 text-[11px] text-teal-900">
                                Diagnosis (Dx)
                            </h3>
                            <ul class="space-y-1">
                                <li v-for="(dx, idx) in prescription.diagnoses" :key="idx" class="leading-snug">
                                    <span class="font-bold text-slate-900">• {{ dx.diagnosis }}</span>
                                    <span v-if="dx.type" class="text-[9px] font-semibold text-teal-700 ml-1">({{ dx.type }})</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Investigations (Ix) -->
                        <div v-if="prescription.investigations?.length">
                            <h3 class="font-bold text-slate-900 uppercase tracking-wide border-b border-slate-300 pb-0.5 mb-1.5 text-[11px] text-teal-900">
                                Investigations (Ix)
                            </h3>
                            <ul class="space-y-1">
                                <li v-for="(ix, idx) in prescription.investigations" :key="idx" class="leading-snug">
                                    <span class="font-medium text-slate-800">• {{ ix.test_name }}</span>
                                    <p v-if="ix.instruction" class="text-[10px] text-slate-500 ml-2 italic">{{ ix.instruction }}</p>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Rx Medicines (~65% width) -->
                    <div class="col-span-8 pl-2 space-y-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-3xl font-serif font-black text-teal-800">℞</span>
                        </div>

                        <!-- Medicines List -->
                        <div class="space-y-3.5">
                            <div
                                v-for="(item, idx) in prescription.items"
                                :key="idx"
                                class="border-b border-slate-100 pb-2.5 last:border-none"
                            >
                                <div class="flex items-start gap-2">
                                    <span class="font-mono font-bold text-slate-400 text-xs w-5 shrink-0">
                                        {{ idx + 1 }}.
                                    </span>
                                    <div class="flex-1">
                                        <!-- Medicine Name & Strength -->
                                        <p class="font-bold text-sm text-slate-900 leading-tight">
                                            {{ item.medicine_name }}
                                        </p>
                                        
                                        <!-- Generic Name -->
                                        <p v-if="item.generic_name" class="text-[11px] text-slate-500 italic">
                                            {{ item.generic_name }}
                                        </p>

                                        <!-- Dosage, Duration, Instruction -->
                                        <div class="mt-1 flex items-center gap-3 text-xs text-slate-800">
                                            <span class="font-mono font-bold text-teal-800 tracking-wider">
                                                {{ item.dosage }}
                                            </span>
                                            <span class="text-slate-600 font-medium">
                                                — {{ item.duration }}
                                            </span>
                                            <span class="text-slate-700 font-medium">
                                                ({{ item.instruction }})
                                            </span>
                                        </div>

                                        <p v-if="item.extra_notes" class="text-[11px] text-amber-700 font-medium mt-0.5 italic">
                                            * {{ item.extra_notes }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Clinical Advice Section -->
                <div v-if="prescription.clinical_advices?.length" class="mt-6 pt-3 border-t border-slate-200 text-xs">
                    <h4 class="font-bold text-slate-900 uppercase text-[11px] mb-1.5 text-teal-900">
                        Clinical Advice:
                    </h4>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1">
                        <li v-for="(adv, idx) in prescription.clinical_advices" :key="idx" class="text-slate-700 leading-snug">
                            ✓ {{ adv }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- 5. Footer: Next Visit & Doctor Signature -->
            <div class="pt-6 border-t-2 border-slate-200 mt-8">
                <div class="flex items-end justify-between">
                    <!-- Next Visit -->
                    <div class="text-xs">
                        <p v-if="prescription.follow_up_date" class="font-bold text-slate-900">
                            Next Visit / Follow-up: <span class="text-teal-800">{{ prescription.follow_up_date }}</span>
                        </p>
                        <p v-if="prescription.follow_up_notes" class="text-[11px] text-slate-500 italic mt-0.5">
                            {{ prescription.follow_up_notes }}
                        </p>
                    </div>

                    <!-- Doctor Signature Block -->
                    <div class="text-center w-48">
                        <div class="border-b border-slate-400 pb-1 mb-1"></div>
                        <p class="font-bold text-xs text-slate-900">{{ prescription.doctor?.name }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">Specialist Signature & Seal</p>
                    </div>
                </div>

                <div class="mt-4 pt-2 border-t border-slate-100 text-center text-[9px] text-slate-400 flex items-center justify-between">
                    <span>Generated by Doc Helper AI - Clinical Practice OS</span>
                    <span>Prescription Code: {{ prescription.prescription_code }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    @page {
        size: A4;
        margin: 10mm 12mm 10mm 12mm;
    }
    body {
        background: white !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>
