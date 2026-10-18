<script setup>
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AiDrugInteractionChecker from '@/Components/AiDrugInteractionChecker.vue';
import AiClinicalChatDrawer from '@/Components/AiClinicalChatDrawer.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    FileText,
    Plus,
    Trash2,
    Save,
    Printer,
    Search,
    User,
    Calendar,
    Building2,
    Activity,
    Pill,
    Stethoscope,
    ChevronDown,
    ChevronUp,
    CheckCircle2,
    AlertCircle,
    Clock,
    X,
    Sparkles,
    ArrowLeft
} from 'lucide-vue-next';

const props = defineProps({
    prescription: Object,
    chambers: Array,
    patients: Array,
    lookups: Object,
    drugs: Array,
});

// Prescription Form State
const form = useForm({
    chamber_id: props.prescription.chamber_id,
    prescription_date: props.prescription.prescription_date,
    status: props.prescription.status || 'finalized',

    // Vitals (O/E)
    vitals: {
        bp: props.prescription.vitals?.bp || '',
        pulse: props.prescription.vitals?.pulse || '',
        weight: props.prescription.vitals?.weight || '',
        height: props.prescription.vitals?.height || '',
        temperature: props.prescription.vitals?.temperature || '',
        spo2: props.prescription.vitals?.spo2 || '',
        bmi: props.prescription.vitals?.bmi || '',
    },

    // Clinical Sections
    chief_complaints: props.prescription.chief_complaints || [],
    on_examinations: props.prescription.on_examinations || [],
    diagnoses: props.prescription.diagnoses || [],
    investigations: props.prescription.investigations || [],
    clinical_advices: props.prescription.clinical_advices || [],
    follow_up_date: props.prescription.follow_up_date || 'After 7 days',
    follow_up_notes: props.prescription.follow_up_notes || '',
    doctor_notes: props.prescription.doctor_notes || '',

    // Medications
    items: (props.prescription.items || []).map((item, idx) => ({
        drug_id: item.drug_id || null,
        medicine_name: item.medicine_name || '',
        generic_name: item.generic_name || '',
        dosage_form: item.dosage_form || 'Tab',
        strength: item.strength || '',
        dosage: item.dosage || '1+0+1',
        duration: item.duration || '7 Days',
        instruction: item.instruction || 'After meal',
        extra_notes: item.extra_notes || '',
        item_order: item.item_order || (idx + 1),
    })),
});

// Auto-calculate BMI
const calculateBmi = () => {
    const wt = parseFloat(form.vitals.weight);
    const ht = form.vitals.height;
    if (!wt || !ht) return;

    let heightInMeters = null;
    if (ht.includes("'")) {
        const parts = ht.split("'");
        const feet = parseFloat(parts[0]) || 0;
        const inches = parseFloat(parts[1]) || 0;
        const totalInches = (feet * 12) + inches;
        heightInMeters = totalInches * 0.0254;
    } else if (parseFloat(ht) > 50) {
        heightInMeters = parseFloat(ht) / 100;
    }

    if (heightInMeters && heightInMeters > 0) {
        const bmi = wt / (heightInMeters * heightInMeters);
        form.vitals.bmi = bmi.toFixed(1);
    }
};

// ==========================================
// 1. Chief Complaints (C/C)
// ==========================================
const ccInput = ref('');
const ccDuration = ref('3 Days');
const ccSeverity = ref('Moderate');
const showCcDropdown = ref(false);

const filteredCcList = computed(() => {
    const list = props.lookups?.chiefComplaints || [];
    if (!ccInput.value) return list.slice(0, 10);
    const q = ccInput.value.toLowerCase();
    return list.filter(item => item.toLowerCase().includes(q)).slice(0, 10);
});

const addCc = (complaintName = null) => {
    const name = (typeof complaintName === 'string' ? complaintName : null) || ccInput.value.trim();
    if (!name) {
        showCcDropdown.value = !showCcDropdown.value;
        return;
    }

    form.chief_complaints.push({
        complaint: name,
        duration: ccDuration.value,
        severity: ccSeverity.value,
        notes: '',
    });

    ccInput.value = '';
    showCcDropdown.value = false;
};

const removeCc = (index) => {
    form.chief_complaints.splice(index, 1);
};

// ==========================================
// 2. On Examination (O/E)
// ==========================================
const oeFinding = ref('');
const oeExamType = ref('General Examination');
const showOeDropdown = ref(false);

const commonOeFindings = [
    'Chest: Clear, Bilateral vesicular breath sounds',
    'Anemia: Absent, Jaundice: Absent, Cyanosis: Absent',
    'Heart: S1 + S2 Normal, No murmur',
    'Abdomen: Soft, Non-tender, No organomegaly',
    'Throat: Mild congestion, Tonsils normal',
    'Skin: Normal, No rash or lesions',
    'CNS: Intact, Conscious, Oriented',
    'Edema: Absent, Dehydration: None'
];

const filteredOeList = computed(() => {
    if (!oeFinding.value) return commonOeFindings;
    const q = oeFinding.value.toLowerCase();
    return commonOeFindings.filter(item => item.toLowerCase().includes(q));
});

const addOe = (findingText = null) => {
    const text = (typeof findingText === 'string' ? findingText : null) || oeFinding.value.trim();
    if (!text) {
        showOeDropdown.value = !showOeDropdown.value;
        return;
    }
    form.on_examinations.push({
        examination: oeExamType.value,
        finding: text,
    });
    oeFinding.value = '';
    showOeDropdown.value = false;
};

const removeOe = (index) => {
    form.on_examinations.splice(index, 1);
};

// ==========================================
// 3. Diagnosis (Dx)
// ==========================================
const dxInput = ref('');
const dxType = ref('Final');
const showDxDropdown = ref(false);

const defaultDiagnoses = [
    { id: 1, name: 'Viral Fever', icd10_code: 'A99' },
    { id: 2, name: 'Acute Bronchitis', icd10_code: 'J20.9' },
    { id: 3, name: 'Type 2 Diabetes Mellitus', icd10_code: 'E11' },
    { id: 4, name: 'Essential (Primary) Hypertension', icd10_code: 'I10' },
    { id: 5, name: 'Upper Respiratory Tract Infection (URTI)', icd10_code: 'J06.9' },
    { id: 6, name: 'Acute Gastritis', icd10_code: 'K29.0' },
    { id: 7, name: 'Urinary Tract Infection (UTI)', icd10_code: 'N39.0' },
    { id: 8, name: 'Bronchial Asthma', icd10_code: 'J45' },
    { id: 9, name: 'Allergic Rhinitis', icd10_code: 'J30.9' },
    { id: 10, name: 'Generalized Anxiety Disorder', icd10_code: 'F41.1' },
];

const filteredDxList = computed(() => {
    const list = (props.lookups?.diagnoses && props.lookups.diagnoses.length) ? props.lookups.diagnoses : defaultDiagnoses;
    if (!dxInput.value) return list.slice(0, 15);
    const q = dxInput.value.toLowerCase();
    return list.filter(item => {
        const name = typeof item === 'string' ? item : (item.name || '');
        const code = item.icd10_code || '';
        return name.toLowerCase().includes(q) || code.toLowerCase().includes(q);
    }).slice(0, 15);
});

const addDx = (dxObj = null) => {
    let name = '';
    let icd10 = '';
    if (dxObj && typeof dxObj === 'object') {
        name = dxObj.name || '';
        icd10 = dxObj.icd10_code || '';
    } else if (typeof dxObj === 'string') {
        name = dxObj;
    } else {
        name = dxInput.value.trim();
    }
    if (!name) {
        showDxDropdown.value = !showDxDropdown.value;
        return;
    }

    form.diagnoses.push({
        diagnosis: name,
        icd10: icd10,
        type: dxType.value,
        notes: '',
    });

    dxInput.value = '';
    showDxDropdown.value = false;
};

const removeDx = (index) => {
    form.diagnoses.splice(index, 1);
};

// ==========================================
// 4. Investigations (Ix)
// ==========================================
const ixInput = ref('');
const ixInstruction = ref('');
const showIxDropdown = ref(false);

const defaultInvestigations = [
    { id: 1, name: 'CBC with ESR', category: 'Hematology' },
    { id: 2, name: 'Serum Creatinine', category: 'Biochemistry' },
    { id: 3, name: 'Random Blood Sugar (RBS)', category: 'Biochemistry' },
    { id: 4, name: 'HbA1c', category: 'Biochemistry' },
    { id: 5, name: 'Lipid Profile', category: 'Biochemistry' },
    { id: 6, name: 'Urine Routine Examination (R/M/E)', category: 'Clinical Pathology' },
    { id: 7, name: 'Chest X-Ray P/A View', category: 'Radiology' },
    { id: 8, name: 'ECG (12 Leads)', category: 'Cardiology' },
    { id: 9, name: 'USG of Whole Abdomen', category: 'Ultrasonography' },
    { id: 10, name: 'Serum Electrolytes', category: 'Biochemistry' },
    { id: 11, name: 'Serum SGPT / ALT', category: 'Hepatic' },
    { id: 12, name: 'Serum Bilirubin', category: 'Hepatic' },
];

const filteredIxList = computed(() => {
    const list = (props.lookups?.investigations && props.lookups.investigations.length) ? props.lookups.investigations : defaultInvestigations;
    if (!ixInput.value) return list.slice(0, 15);
    const q = ixInput.value.toLowerCase();
    return list.filter(item => {
        const name = typeof item === 'string' ? item : (item.name || '');
        const cat = item.category || '';
        return name.toLowerCase().includes(q) || cat.toLowerCase().includes(q);
    }).slice(0, 15);
});

const addIx = (ixObj = null) => {
    let name = '';
    if (ixObj && typeof ixObj === 'object') {
        name = ixObj.name || '';
    } else if (typeof ixObj === 'string') {
        name = ixObj;
    } else {
        name = ixInput.value.trim();
    }
    if (!name) {
        showIxDropdown.value = !showIxDropdown.value;
        return;
    }

    form.investigations.push({
        test_name: name,
        instruction: ixInstruction.value.trim(),
    });

    ixInput.value = '';
    ixInstruction.value = '';
    showIxDropdown.value = false;
};

const removeIx = (index) => {
    form.investigations.splice(index, 1);
};

// ==========================================
// 5. Clinical Advices
// ==========================================
const adviceInput = ref('');
const showAdviceDropdown = ref(false);

const filteredAdviceList = computed(() => {
    const list = props.lookups?.advices || [];
    if (!adviceInput.value) return list.slice(0, 10);
    const q = adviceInput.value.toLowerCase();
    return list.filter(item => item.toLowerCase().includes(q)).slice(0, 10);
});

const addAdvice = (adviceText = null) => {
    const text = (typeof adviceText === 'string' ? adviceText : null) || adviceInput.value.trim();
    if (!text || form.clinical_advices.includes(text)) return;

    form.clinical_advices.push(text);
    adviceInput.value = '';
    showAdviceDropdown.value = false;
};

const removeAdvice = (index) => {
    form.clinical_advices.splice(index, 1);
};

// ==========================================
// 6. Medication Builder & Management
// ==========================================
const currentDrug = ref({
    drug_id: null,
    medicine_name: '',
    generic_name: '',
    dosage_form: 'Tab',
    strength: '',
    dosage: '1+0+1',
    duration: '7 Days',
    instruction: 'After meal',
    extra_notes: '',
});

const drugSearchQuery = ref('');
const showDrugSearchDropdown = ref(false);

const filteredDrugs = computed(() => {
    if (!drugSearchQuery.value || drugSearchQuery.value.length < 1) return (props.drugs || []).slice(0, 10);
    const q = drugSearchQuery.value.toLowerCase();
    return (props.drugs || []).filter(d =>
        (d.brand_name && d.brand_name.toLowerCase().includes(q)) ||
        (d.generic_name && d.generic_name.toLowerCase().includes(q))
    ).slice(0, 15);
});

const selectDrugFromSearch = (drug) => {
    currentDrug.value.drug_id = drug.id;
    currentDrug.value.medicine_name = drug.brand_name;
    currentDrug.value.generic_name = drug.generic_name || '';
    currentDrug.value.dosage_form = drug.type || 'Tab';
    currentDrug.value.strength = drug.strength || '';

    drugSearchQuery.value = drug.brand_name;
    showDrugSearchDropdown.value = false;
};

const onDrugSearchInput = () => {
    currentDrug.value.medicine_name = drugSearchQuery.value;
    showDrugSearchDropdown.value = true;
};

const addMedication = () => {
    const name = currentDrug.value.medicine_name || drugSearchQuery.value;
    if (!name || !name.trim()) return;

    form.items.push({
        drug_id: currentDrug.value.drug_id,
        medicine_name: name.trim(),
        generic_name: currentDrug.value.generic_name,
        dosage_form: currentDrug.value.dosage_form || 'Tab',
        strength: currentDrug.value.strength || '',
        dosage: currentDrug.value.dosage || '1+0+1',
        duration: currentDrug.value.duration || '7 Days',
        instruction: currentDrug.value.instruction || 'After meal',
        extra_notes: currentDrug.value.extra_notes || '',
        item_order: form.items.length + 1,
    });

    // Reset current drug builder
    currentDrug.value = {
        drug_id: null,
        medicine_name: '',
        generic_name: '',
        dosage_form: 'Tab',
        strength: '',
        dosage: '1+0+1',
        duration: '7 Days',
        instruction: 'After meal',
        extra_notes: '',
    };
    drugSearchQuery.value = '';
    showDrugSearchDropdown.value = false;
};

const removeMedication = (index) => {
    form.items.splice(index, 1);
};

const moveMedication = (index, direction) => {
    const targetIndex = index + direction;
    if (targetIndex < 0 || targetIndex >= form.items.length) return;
    const item = form.items.splice(index, 1)[0];
    form.items.splice(targetIndex, 0, item);
};

const currentPatient = computed(() => {
    return props.prescription?.patient || (props.patients ? props.patients.find(p => p.id === form.patient_id) : null) || {};
});

// AI Copilot 1-Click Handlers
const handleAiAddDrug = (drugData) => {
    if (!drugData) return;
    form.items.push({
        medicine_id: null,
        medicine_name: drugData.trade_name || drugData.name || 'Medicine',
        dosage_form: drugData.type || 'Tab.',
        strength: drugData.strength || '',
        dosage: drugData.dosage || '1+0+1',
        instruction: drugData.instruction || 'After meal',
        duration: drugData.duration || '7 Days',
        notes: drugData.notes || '',
    });
};

const handleAiAddInvestigation = (testName) => {
    if (!testName) return;
    const name = typeof testName === 'string' ? testName : (testName.test_name || testName.name);
    if (!form.investigations.some(i => i.test_name === name)) {
        form.investigations.push({
            test_name: name,
            instruction: 'Please review on next visit',
        });
    }
};

const handleAiAddAdvice = (adviceText) => {
    if (!adviceText) return;
    if (!form.clinical_advices.includes(adviceText)) {
        form.clinical_advices.push(adviceText);
    }
};

// Form submission
const submit = () => {
    if (!form.items.length) {
        alert('Please add at least one medication to update the prescription.');
        return;
    }

    form.put(route('prescriptions.update', props.prescription.id), {
        onSuccess: () => {
            // Success handled by controller redirect
        },
    });
};
</script>

<template>
    <Head :title="`Edit Prescription ${prescription.prescription_code} - Doc Helper AI`" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-7xl mx-auto pb-16">
            <!-- Header Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('prescriptions.show', prescription.id)"
                        class="p-2 rounded-xl bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-[#30363D] transition-colors text-slate-700 dark:text-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Edit Prescription</span>
                            <span class="font-mono text-sm text-teal-600 dark:text-teal-400 font-bold">
                                {{ prescription.prescription_code }}
                            </span>
                        </h1>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Patient: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ prescription.patient?.name }}</span> ({{ prescription.patient?.patient_code }}) • Chamber: {{ prescription.chamber?.name }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('prescriptions.show', prescription.id)"
                        class="px-3.5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-xl transition-colors"
                    >
                        Cancel
                    </Link>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Updating...' : 'Update Prescription' }}</span>
                    </button>
                </div>
            </div>

            <!-- Main Split Clinical Workspace Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">
                <!-- ========================================== -->
                <!-- LEFT COLUMN: Clinical Assessment (5 cols) -->
                <!-- ========================================== -->
                <div class="lg:col-span-5 space-y-4">
                    <!-- Patient & Chamber Meta Card -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                <User class="w-3.5 h-3.5 text-teal-600" />
                                <span>Patient & Visit Info</span>
                            </h2>
                            <span class="text-[11px] font-mono font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/30 px-2 py-0.5 rounded-lg border border-teal-200 dark:border-teal-800">
                                {{ prescription.patient?.patient_code }}
                            </span>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-[#21262D]/60 rounded-xl border border-slate-200/70 dark:border-[#30363D] text-xs space-y-1">
                            <p class="font-bold text-slate-900 dark:text-white">{{ prescription.patient?.name }}</p>
                            <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                                {{ prescription.patient?.age ? `${prescription.patient.age} Yrs` : '' }} • {{ prescription.patient?.gender }} • Phone: {{ prescription.patient?.phone || 'N/A' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Chamber</label>
                                <select
                                    v-model="form.chamber_id"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-medium"
                                >
                                    <option v-for="c in chambers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Date</label>
                                <input
                                    type="date"
                                    v-model="form.prescription_date"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-medium"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- 1. Vitals & O/E Strip -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Activity class="w-3.5 h-3.5 text-rose-500" />
                            <span>Vitals & Physical Examination (O/E)</span>
                        </h2>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">BP (mmHg)</label>
                                <input
                                    v-model="form.vitals.bp"
                                    type="text"
                                    placeholder="120/80"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-mono text-center"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">Pulse (bpm)</label>
                                <input
                                    v-model="form.vitals.pulse"
                                    type="text"
                                    placeholder="78"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-mono text-center"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">Temp (°F)</label>
                                <input
                                    v-model="form.vitals.temperature"
                                    type="text"
                                    placeholder="98.6"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-mono text-center"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">Weight (kg)</label>
                                <input
                                    v-model="form.vitals.weight"
                                    @input="calculateBmi"
                                    type="number"
                                    placeholder="65"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-mono text-center"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">Height</label>
                                <input
                                    v-model="form.vitals.height"
                                    @input="calculateBmi"
                                    type="text"
                                    placeholder="5'7&quot;"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-mono text-center"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">BMI / SpO2</label>
                                <input
                                    v-model="form.vitals.bmi"
                                    type="text"
                                    placeholder="22.5"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-teal-600 dark:text-teal-400 font-mono text-center font-bold"
                                />
                            </div>
                        </div>

                        <!-- O/E Findings List & Input -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-[#21262D]">
                            <div class="relative">
                                <input
                                    v-model="oeFinding"
                                    @focus="showOeDropdown = true"
                                    @keyup.enter="addOe()"
                                    type="text"
                                    placeholder="Type finding & press Enter (e.g. Chest: Clear)..."
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                                <!-- Autocomplete Dropdown -->
                                <div
                                    v-if="showOeDropdown && filteredOeList.length"
                                    class="absolute z-40 left-0 right-0 mt-1 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl shadow-lg max-h-36 overflow-y-auto custom-scrollbar"
                                >
                                    <button
                                        v-for="oe in filteredOeList"
                                        :key="oe"
                                        type="button"
                                        @mousedown.prevent="addOe(oe)"
                                        class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 dark:hover:bg-[#21262D] text-slate-700 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 last:border-none cursor-pointer"
                                    >
                                        {{ oe }}
                                    </button>
                                </div>
                            </div>

                            <div v-if="form.on_examinations.length" class="space-y-1">
                                <div
                                    v-for="(oe, idx) in form.on_examinations"
                                    :key="idx"
                                    class="flex items-center justify-between px-2.5 py-1 bg-slate-50 dark:bg-[#21262D] rounded-lg text-xs"
                                >
                                    <span class="text-slate-700 dark:text-slate-300">• {{ oe.finding }}</span>
                                    <button type="button" @click="removeOe(idx)" class="text-slate-400 hover:text-rose-500 cursor-pointer">
                                        <X class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Chief Complaints (C/C) Card -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                <Stethoscope class="w-3.5 h-3.5 text-blue-500" />
                                <span>Chief Complaints (C/C)</span>
                            </h2>
                            <span class="text-[10px] text-slate-400">{{ form.chief_complaints.length }} added</span>
                        </div>

                        <!-- C/C Input & Autocomplete -->
                        <div class="relative space-y-2">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input
                                        v-model="ccInput"
                                        @focus="showCcDropdown = true"
                                        @keyup.enter="addCc()"
                                        type="text"
                                        placeholder="Type complaint & press Enter..."
                                        class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                    />
                                    <!-- Autocomplete Dropdown -->
                                    <div
                                        v-if="showCcDropdown && filteredCcList.length"
                                        class="absolute z-40 left-0 right-0 mt-1 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl shadow-lg max-h-36 overflow-y-auto custom-scrollbar"
                                    >
                                        <button
                                            v-for="cc in filteredCcList"
                                            :key="cc"
                                            type="button"
                                            @mousedown.prevent="addCc(cc)"
                                            class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 dark:hover:bg-[#21262D] text-slate-700 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 last:border-none cursor-pointer"
                                        >
                                            {{ cc }}
                                        </button>
                                    </div>
                                </div>

                                <input
                                    v-model="ccDuration"
                                    @keyup.enter="addCc()"
                                    type="text"
                                    placeholder="3 Days"
                                    class="w-24 px-2 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white text-center"
                                />
                            </div>
                        </div>

                        <!-- C/C List -->
                        <div v-if="form.chief_complaints.length" class="space-y-1.5 pt-1">
                            <div
                                v-for="(cc, idx) in form.chief_complaints"
                                :key="idx"
                                class="flex items-center justify-between px-3 py-1.5 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-900/60 rounded-xl text-xs"
                            >
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ cc.complaint }}</span>
                                    <span class="text-[11px] text-blue-600 dark:text-blue-400 font-medium ml-2">({{ cc.duration }})</span>
                                </div>
                                <button
                                    type="button"
                                    @click="removeCc(idx)"
                                    class="text-slate-400 hover:text-rose-500 transition-colors p-1 cursor-pointer"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Diagnosis (Dx) Card -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                <Sparkles class="w-3.5 h-3.5 text-purple-500" />
                                <span>Diagnosis (Dx)</span>
                            </h2>
                            <span class="text-[10px] text-slate-400">{{ form.diagnoses.length }} added</span>
                        </div>

                        <div class="relative space-y-2">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input
                                        v-model="dxInput"
                                        @focus="showDxDropdown = true"
                                        @keyup.enter="addDx()"
                                        type="text"
                                        placeholder="Type diagnosis & press Enter..."
                                        class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                    />
                                    <div
                                        v-if="showDxDropdown && filteredDxList.length"
                                        class="absolute z-40 left-0 right-0 mt-1 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl shadow-lg max-h-36 overflow-y-auto custom-scrollbar"
                                    >
                                        <button
                                            v-for="dx in filteredDxList"
                                            :key="dx.id || dx"
                                            type="button"
                                            @mousedown.prevent="addDx(dx)"
                                            class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 dark:hover:bg-[#21262D] text-slate-700 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 last:border-none flex items-center justify-between cursor-pointer"
                                        >
                                            <span>{{ typeof dx === 'string' ? dx : dx.name }}</span>
                                            <span v-if="dx.icd10_code" class="text-[10px] font-mono text-slate-400">{{ dx.icd10_code }}</span>
                                        </button>
                                    </div>
                                </div>

                                <select
                                    v-model="dxType"
                                    class="w-28 px-2 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                >
                                    <option value="Final">Final</option>
                                    <option value="Provisional">Provisional</option>
                                </select>
                            </div>
                        </div>

                        <!-- Dx List -->
                        <div v-if="form.diagnoses.length" class="space-y-1.5 pt-1">
                            <div
                                v-for="(dx, idx) in form.diagnoses"
                                :key="idx"
                                class="flex items-center justify-between px-3 py-1.5 bg-purple-50/50 dark:bg-purple-950/20 border border-purple-200/60 dark:border-purple-900/60 rounded-xl text-xs"
                            >
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ dx.diagnosis }}</span>
                                    <span
                                        class="text-[9px] px-1.5 py-0.5 rounded font-bold uppercase ml-2"
                                        :class="dx.type === 'Final' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300'"
                                    >
                                        {{ dx.type }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="removeDx(idx)"
                                    class="text-slate-400 hover:text-rose-500 transition-colors p-1 cursor-pointer"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Investigations (Ix) Card -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                <FileText class="w-3.5 h-3.5 text-amber-500" />
                                <span>Investigations & Tests (Ix)</span>
                            </h2>
                            <span class="text-[10px] text-slate-400">{{ form.investigations.length }} advised</span>
                        </div>

                        <div class="relative space-y-2">
                            <div class="relative">
                                <input
                                    v-model="ixInput"
                                    @focus="showIxDropdown = true"
                                    @keyup.enter="addIx()"
                                    type="text"
                                    placeholder="Type test & press Enter (e.g. CBC, RBS, X-Ray)..."
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                                <div
                                    v-if="showIxDropdown && filteredIxList.length"
                                    class="absolute z-40 left-0 right-0 mt-1 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl shadow-lg max-h-36 overflow-y-auto custom-scrollbar"
                                >
                                    <button
                                        v-for="ix in filteredIxList"
                                        :key="ix.id || ix"
                                        type="button"
                                        @mousedown.prevent="addIx(ix)"
                                        class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 dark:hover:bg-[#21262D] text-slate-700 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 last:border-none flex items-center justify-between cursor-pointer"
                                    >
                                        <span>{{ typeof ix === 'string' ? ix : ix.name }}</span>
                                        <span v-if="ix.category" class="text-[10px] text-slate-400">{{ ix.category }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Ix List -->
                        <div v-if="form.investigations.length" class="space-y-1.5 pt-1">
                            <div
                                v-for="(ix, idx) in form.investigations"
                                :key="idx"
                                class="flex items-center justify-between px-3 py-1.5 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/60 rounded-xl text-xs"
                            >
                                <span class="font-bold text-slate-900 dark:text-white">{{ ix.test_name }}</span>
                                <button
                                    type="button"
                                    @click="removeIx(idx)"
                                    class="text-slate-400 hover:text-rose-500 transition-colors p-1 cursor-pointer"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Advices & Follow-up Card -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                <CheckCircle2 class="w-3.5 h-3.5 text-emerald-500" />
                                <span>Advices & Follow Up</span>
                            </h2>
                            <span class="text-[10px] text-slate-400">{{ form.clinical_advices.length }} selected</span>
                        </div>

                        <!-- Advice Input & Dropdown -->
                        <div class="space-y-2">
                            <div class="relative">
                                <input
                                    v-model="adviceInput"
                                    @focus="showAdviceDropdown = true"
                                    @keyup.enter="addAdvice()"
                                    type="text"
                                    placeholder="Type advice & press Enter..."
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                                <!-- Autocomplete Dropdown -->
                                <div
                                    v-if="showAdviceDropdown && filteredAdviceList.length"
                                    class="absolute z-40 left-0 right-0 mt-1 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl shadow-lg max-h-36 overflow-y-auto custom-scrollbar"
                                >
                                    <button
                                        v-for="adv in filteredAdviceList"
                                        :key="adv"
                                        type="button"
                                        @mousedown.prevent="addAdvice(adv)"
                                        class="w-full text-left px-3 py-1.5 text-xs hover:bg-slate-50 dark:hover:bg-[#21262D] text-slate-700 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 last:border-none cursor-pointer"
                                    >
                                        {{ adv }}
                                    </button>
                                </div>
                            </div>

                            <!-- Selected Advices List -->
                            <div v-if="form.clinical_advices.length" class="space-y-1 pt-1">
                                <div
                                    v-for="(adv, idx) in form.clinical_advices"
                                    :key="idx"
                                    class="flex items-start justify-between gap-2 px-3 py-1.5 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-900/60 rounded-xl text-xs"
                                >
                                    <span class="text-slate-700 dark:text-slate-300">✓ {{ adv }}</span>
                                    <button type="button" @click="removeAdvice(idx)" class="text-slate-400 hover:text-rose-500 cursor-pointer shrink-0 mt-0.5">
                                        <X class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>

                            <!-- Preset Advice Chips -->
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <button
                                    v-for="adv in (lookups.advices || [
                                        'Drink plenty of warm water',
                                        'Take complete physical rest',
                                        'Avoid oily and spicy food',
                                        'Walk 30 minutes daily',
                                        'Check blood pressure regularly',
                                        'Low salt & low sugar diet',
                                        'Consult immediately if emergency symptoms appear'
                                    ])"
                                    :key="adv"
                                    type="button"
                                    @click="addAdvice(adv)"
                                    :class="[
                                        'px-2 py-0.5 text-[10px] font-semibold rounded-lg transition-colors text-left cursor-pointer',
                                        form.clinical_advices.includes(adv)
                                            ? 'bg-emerald-600 text-white'
                                            : 'bg-slate-100 dark:bg-[#21262D] text-slate-600 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 hover:text-emerald-600'
                                    ]"
                                >
                                    {{ form.clinical_advices.includes(adv) ? '✓ ' : '+ ' }}{{ adv }}
                                </button>
                            </div>
                        </div>

                        <!-- Follow-up Field -->
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-[#21262D]">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Follow-up Date / Timeline</label>
                                <select
                                    v-model="form.follow_up_date"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-medium"
                                >
                                    <option value="After 3 days">After 3 days</option>
                                    <option value="After 5 days">After 5 days</option>
                                    <option value="After 7 days">After 7 days</option>
                                    <option value="After 10 days">After 10 days</option>
                                    <option value="After 14 days">After 14 days</option>
                                    <option value="After 1 month">After 1 month</option>
                                    <option value="After 3 months">After 3 months</option>
                                    <option value="As needed (SOS)">As needed (SOS)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase mb-1">Follow-up Instruction</label>
                                <input
                                    v-model="form.follow_up_notes"
                                    placeholder="e.g. Review with lab reports"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-medium"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- RIGHT COLUMN: Medication Rx Pad (7 cols)  -->
                <!-- ========================================== -->
                <div class="lg:col-span-7 space-y-4">
                    <!-- Real-Time AI Drug-to-Drug Interaction & Allergy Checker -->
                    <AiDrugInteractionChecker
                        v-if="$page.props.auth?.can_use_ai"
                        :drugs="form.items"
                        :patient="currentPatient"
                        :diagnoses="form.diagnoses"
                    />

                    <!-- Medicine Builder Card -->
                    <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-serif font-black text-teal-600 dark:text-teal-400">℞</span>
                                <div>
                                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Rx Medicine Builder</h2>
                                    <p class="text-[11px] text-slate-400">Search drugs from catalog or enter custom brand name</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-slate-100 dark:bg-[#21262D] text-slate-600 dark:text-slate-300">
                                {{ form.items.length }} Prescribed
                            </span>
                        </div>

                        <!-- 1. Medicine Name & Type Search Field -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 tracking-wider">
                                    Medicine Name / Brand / Generic
                                </label>
                                <span class="text-[10px] text-teal-600 dark:text-teal-400 font-medium">Auto-suggest enabled</span>
                            </div>

                            <div class="relative">
                                <div class="relative flex items-center">
                                    <Search class="absolute left-3 w-4 h-4 text-slate-400" />
                                    <input
                                        v-model="drugSearchQuery"
                                        @input="onDrugSearchInput"
                                        @focus="showDrugSearchDropdown = true"
                                        @keyup.enter="addMedication"
                                        placeholder="Search brand (Napa, Seclo, Monas, Nexum) or generic..."
                                        class="w-full pl-9 pr-4 py-2.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-bold text-slate-900 dark:text-white placeholder:font-normal focus:ring-2 focus:ring-teal-500"
                                    />
                                    <button
                                        v-if="drugSearchQuery"
                                        type="button"
                                        @click="drugSearchQuery = ''; currentDrug.medicine_name = ''; showDrugSearchDropdown = false"
                                        class="absolute right-3 text-slate-400 hover:text-slate-600"
                                    >
                                        <X class="w-3.5 h-3.5" />
                                    </button>
                                </div>

                                <!-- Drug Search Autocomplete Dropdown -->
                                <div
                                    v-if="showDrugSearchDropdown && filteredDrugs.length"
                                    class="absolute z-30 left-0 right-0 mt-1 bg-white dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-[#21262D]"
                                >
                                    <div
                                        v-for="drug in filteredDrugs"
                                        :key="drug.id"
                                        @click="selectDrugFromSearch(drug)"
                                        class="p-2.5 text-xs hover:bg-teal-50 dark:hover:bg-teal-950/40 cursor-pointer flex items-center justify-between transition-colors"
                                    >
                                        <div>
                                            <div class="flex items-center gap-1.5 font-bold text-slate-900 dark:text-white">
                                                <span class="text-[10px] px-1.5 py-0.2 bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 rounded uppercase font-mono">
                                                    {{ drug.type || 'Tab' }}
                                                </span>
                                                <span>{{ drug.brand_name }}</span>
                                                <span v-if="drug.strength" class="text-slate-400 font-normal">({{ drug.strength }})</span>
                                            </div>
                                            <p v-if="drug.generic_name" class="text-[10px] text-slate-400 mt-0.5">
                                                Generic: {{ drug.generic_name }} • {{ drug.company || 'Standard' }}
                                            </p>
                                        </div>
                                        <span class="text-[11px] font-bold text-teal-600 dark:text-teal-400 shrink-0">+ Select</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Dosage Form & Strength Selectors -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    Dosage Form (Type)
                                </label>
                                <select
                                    v-model="currentDrug.dosage_form"
                                    class="w-full px-2.5 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-semibold"
                                >
                                    <option v-for="type in (lookups.types || ['Tab', 'Cap', 'Syp', 'Inj', 'Drop', 'Oint', 'Cream', 'Inhaler', 'Supp', 'Gel', 'Suspension', 'Powder'])" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    Strength
                                </label>
                                <div class="flex gap-1.5">
                                    <select
                                        v-model="currentDrug.strength"
                                        class="w-full px-2.5 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-semibold"
                                    >
                                        <option value="">-- Choose / Custom --</option>
                                        <option v-for="st in (lookups.strengths || ['500mg', '250mg', '20mg', '40mg', '10mg', '5mg', '100mg', '650mg', '1gm', '5ml', '10ml', '0.05%'])" :key="st" :value="st">
                                            {{ st }}
                                        </option>
                                    </select>
                                    <input
                                        v-model="currentDrug.strength"
                                        placeholder="or type"
                                        class="w-24 px-2 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-center font-semibold"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- 3. Dosage, Duration & Timing Selection Options -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <!-- Dosage Selector -->
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    Dosage Schedule
                                </label>
                                <select
                                    v-model="currentDrug.dosage"
                                    class="w-full px-2.5 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono font-bold text-teal-600 dark:text-teal-400"
                                >
                                    <option v-for="dose in (lookups.doses || ['1+0+1', '1+1+1', '1+0+0', '0+0+1', '1+1+1+1', '1/2+0+1/2', '1 tsp 3 times daily', '2 tsp twice daily', '1 puff as needed'])" :key="dose" :value="dose">
                                        {{ dose }}
                                    </option>
                                </select>
                            </div>

                            <!-- Duration Selector -->
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    Duration
                                </label>
                                <select
                                    v-model="currentDrug.duration"
                                    class="w-full px-2.5 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-semibold"
                                >
                                    <option v-for="dur in (lookups.durations || ['3 Days', '5 Days', '7 Days', '10 Days', '14 Days', '1 Month', '2 Months', '3 Months', 'Continue'])" :key="dur" :value="dur">
                                        {{ dur }}
                                    </option>
                                </select>
                            </div>

                            <!-- Instruction / Meal Timing Selector -->
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                                    Instruction / Timing
                                </label>
                                <select
                                    v-model="currentDrug.instruction"
                                    class="w-full px-2.5 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 font-semibold"
                                >
                                    <option v-for="inst in (lookups.instructions || ['After meal', 'Before meal', 'Empty stomach', 'At bedtime', 'With plenty of water', 'Between meals'])" :key="inst" :value="inst">
                                        {{ inst }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Special Instructions / Notes -->
                        <div>
                            <input
                                v-model="currentDrug.extra_notes"
                                placeholder="Special instruction (optional, e.g. Take with 1 glass of lukewarm water)"
                                class="w-full px-3 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 placeholder:text-slate-400"
                            />
                        </div>

                        <!-- Add Button -->
                        <div class="flex items-center justify-end pt-2">
                            <button
                                type="button"
                                @click="addMedication"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Medication to Prescription</span>
                            </button>
                        </div>
                    </div>

                    <!-- Prescribed Medications List (Interactive Rx Pad) -->
                    <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white flex items-center gap-2">
                                <Pill class="w-4 h-4 text-teal-600" />
                                <span>Prescribed Medications ({{ form.items.length }})</span>
                            </h3>
                            <span v-if="form.items.length" class="text-[10px] text-slate-400">
                                Reorder with arrows or delete
                            </span>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!form.items.length" class="py-12 text-center text-slate-400 space-y-2">
                            <Pill class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600" />
                            <p class="text-xs font-medium">No medications added yet.</p>
                            <p class="text-[11px]">Use the Rx builder above to search or add medications.</p>
                        </div>

                        <!-- Medication Items List -->
                        <div v-else class="space-y-2.5">
                            <div
                                v-for="(item, idx) in form.items"
                                :key="idx"
                                class="group relative p-3.5 bg-slate-50/80 dark:bg-[#21262D]/60 hover:bg-slate-100/80 dark:hover:bg-[#21262D] border border-slate-200/80 dark:border-[#30363D] rounded-2xl flex items-start justify-between gap-3 transition-colors"
                            >
                                <div class="flex items-start gap-3">
                                    <!-- Index Badge -->
                                    <span class="w-6 h-6 rounded-lg bg-[#0A0D36] dark:bg-teal-500 text-white dark:text-slate-950 font-mono font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ idx + 1 }}
                                    </span>

                                    <!-- Drug Details -->
                                    <div class="space-y-1">
                                        <div class="flex items-baseline gap-2 flex-wrap">
                                            <span class="text-[11px] font-mono font-bold px-1.5 py-0.2 bg-teal-100 dark:bg-teal-900/40 text-teal-700 dark:text-teal-300 rounded uppercase">
                                                {{ item.dosage_form }}
                                            </span>
                                            <span class="font-bold text-sm text-slate-900 dark:text-white">
                                                {{ item.medicine_name }}
                                            </span>
                                            <span v-if="item.strength" class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                                {{ item.strength }}
                                            </span>
                                        </div>

                                        <p v-if="item.generic_name" class="text-[11px] text-slate-400 italic">
                                            ({{ item.generic_name }})
                                        </p>

                                        <!-- Dosage / Duration / Instruction Tag -->
                                        <div class="flex items-center gap-2 flex-wrap pt-0.5 text-xs text-slate-700 dark:text-slate-300">
                                            <span class="font-mono font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/30 px-2 py-0.5 rounded-lg border border-teal-200 dark:border-teal-900">
                                                {{ item.dosage }}
                                            </span>
                                            <span class="text-slate-400">•</span>
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">
                                                {{ item.duration }}
                                            </span>
                                            <span class="text-slate-400">•</span>
                                            <span class="italic text-slate-600 dark:text-slate-300">
                                                {{ item.instruction }}
                                            </span>
                                        </div>

                                        <p v-if="item.extra_notes" class="text-[11px] text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-2 py-0.5 rounded-lg inline-block mt-1">
                                            Note: {{ item.extra_notes }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions: Reorder & Remove -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <button
                                        type="button"
                                        @click="moveMedication(idx, -1)"
                                        :disabled="idx === 0"
                                        class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-[#30363D] rounded-lg disabled:opacity-20 transition-colors cursor-pointer"
                                        title="Move Up"
                                    >
                                        <ChevronUp class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="moveMedication(idx, 1)"
                                        :disabled="idx === form.items.length - 1"
                                        class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-[#30363D] rounded-lg disabled:opacity-20 transition-colors cursor-pointer"
                                        title="Move Down"
                                    >
                                        <ChevronDown class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="removeMedication(idx)"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition-colors cursor-pointer"
                                        title="Remove Medication"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Notes / Confidential Card -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-2">
                        <label class="block text-xs font-bold uppercase text-slate-700 dark:text-slate-300">
                            Doctor's Private Notes / Case History
                        </label>
                        <textarea
                            v-model="form.doctor_notes"
                            rows="2"
                            placeholder="Optional private notes for this patient's case history (not printed on prescription pad)..."
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 placeholder:text-slate-400"
                        ></textarea>
                    </div>

                    <!-- Bottom Submit Bar -->
                    <div class="flex items-center justify-between p-4 bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                        <div class="text-xs text-slate-500">
                            Status: <span class="font-bold text-teal-600 uppercase">{{ form.status }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Link
                                :href="route('prescriptions.show', prescription.id)"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-xl transition-colors"
                            >
                                Cancel
                            </Link>
                            <button
                                type="button"
                                @click="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-1.5 px-6 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                            >
                                <Save class="w-4 h-4" />
                                <span>{{ form.processing ? 'Updating...' : 'Update Prescription' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Clinical Copilot Slide-out Drawer -->
        <AiClinicalChatDrawer
            v-if="$page.props.auth?.can_use_ai"
            :patient="currentPatient"
            :diagnoses="form.diagnoses"
            :drugs="form.items"
            :complaints="form.chief_complaints"
            @add-drug="handleAiAddDrug"
            @add-investigation="handleAiAddInvestigation"
            @add-advice="handleAiAddAdvice"
        />
    </AuthenticatedLayout>
</template>
