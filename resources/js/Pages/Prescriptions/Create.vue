<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AiDrugInteractionChecker from '@/Components/AiDrugInteractionChecker.vue';
import AiClinicalChatDrawer from '@/Components/AiClinicalChatDrawer.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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
    chambers: Array,
    activeChamberId: Number,
    patients: Array,
    selectedPatient: Object,
    selectedAppointment: Object,
    lookups: Object,
    drugs: Array,
});

// Prescription Form State
const form = useForm({
    chamber_id: props.activeChamberId || (props.chambers[0]?.id ?? ''),
    patient_id: props.selectedPatient?.id || (props.selectedAppointment?.patient_id ?? ''),
    appointment_id: props.selectedAppointment?.id || null,
    prescription_date: new Date().toISOString().split('T')[0],
    status: 'finalized',

    // Vitals (O/E)
    vitals: {
        bp: props.selectedAppointment?.vitals?.bp || '',
        pulse: props.selectedAppointment?.vitals?.pulse || '',
        weight: props.selectedAppointment?.vitals?.weight || '',
        height: '',
        temperature: props.selectedAppointment?.vitals?.temperature || '',
        spo2: props.selectedAppointment?.vitals?.spo2 || '',
        bmi: '',
    },

    // Clinical Sections
    chief_complaints: [], // [{ complaint, duration, severity, notes }]
    on_examinations: [], // [{ examination, finding }]
    diagnoses: [], // [{ diagnosis, icd10, type, notes }]
    investigations: [], // [{ test_name, instruction }]
    clinical_advices: [], // ['Drink plenty of warm water', ...]
    follow_up_date: 'After 7 days',
    follow_up_notes: 'Please review with investigation reports.',
    doctor_notes: '',

    // Medications
    items: [],
});

// Patient Search & Selector State
const patientQuery = ref('');
const showPatientDropdown = ref(false);

const filteredPatients = computed(() => {
    if (!patientQuery.value) return props.patients.slice(0, 10);
    const q = patientQuery.value.toLowerCase();
    return props.patients.filter(p =>
        p.name.toLowerCase().includes(q) ||
        (p.phone && p.phone.includes(q)) ||
        p.patient_code.toLowerCase().includes(q)
    ).slice(0, 10);
});

const currentPatient = computed(() => {
    return props.patients.find(p => p.id === form.patient_id) || props.selectedPatient || null;
});

const selectPatient = (patient) => {
    form.patient_id = patient.id;
    patientQuery.value = `${patient.name} (${patient.patient_code})`;
    showPatientDropdown.value = false;
};

// AI Longitudinal Medical History Modal for Selected Patient
const isPatientHistoryModalOpen = ref(false);
const patientHistoryLoading = ref(false);
const patientHistoryData = ref(null);
const patientHistoryError = ref('');

const openPatientHistoryModal = async () => {
    if (!currentPatient.value?.id) return;
    isPatientHistoryModalOpen.value = true;
    patientHistoryLoading.value = true;
    patientHistoryError.value = '';

    try {
        const response = await axios.post(route('ai.patient-history-summary'), {
            patient_id: currentPatient.value.id,
        });
        if (response.data?.success) {
            patientHistoryData.value = response.data.data;
        } else {
            patientHistoryError.value = response.data?.message || 'Unable to load AI summary.';
        }
    } catch (err) {
        patientHistoryError.value = err.response?.data?.message || 'AI service temporarily unavailable.';
    } finally {
        patientHistoryLoading.value = false;
    }
};

// Initialize patient query if already selected
onMounted(() => {
    if (currentPatient.value) {
        patientQuery.value = `${currentPatient.value.name} (${currentPatient.value.patient_code})`;
    }
});

// Auto-calculate BMI
const calculateBmi = () => {
    const wt = parseFloat(form.vitals.weight);
    const ht = form.vitals.height; // e.g. "5'7\"" or cm
    if (!wt || !ht) return;

    let heightInMeters = null;
    if (ht.includes("'")) {
        const parts = ht.split("'");
        const feet = parseFloat(parts[0]) || 0;
        const inches = parseFloat(parts[1]) || 0;
        const totalInches = (feet * 12) + inches;
        heightInMeters = totalInches * 0.0254;
    } else if (parseFloat(ht) > 50) {
        heightInMeters = parseFloat(ht) / 100; // cm to m
    }

    if (heightInMeters && heightInMeters > 0) {
        const bmi = wt / (heightInMeters * heightInMeters);
        form.vitals.bmi = bmi.toFixed(1);
    }
};

// ==========================================
// 1. Chief Complaints (C/C) Management
// ==========================================
const ccInput = ref('');
const ccDuration = ref('3 Days');
const ccSeverity = ref('Moderate');
const showCcDropdown = ref(false);

const filteredCcList = computed(() => {
    const list = props.lookups?.chiefComplaints || [
        'Fever', 'Dry Cough', 'Productive Cough', 'Chest Pain', 'Shortness of Breath',
        'Headache', 'Abdominal Pain', 'Nausea / Vomiting', 'Burning sensation during urination',
        'Generalized Weakness', 'Joint Pain', 'Dizziness', 'Sore Throat', 'Loose Motion'
    ];
    if (!ccInput.value) return list.slice(0, 15);
    const q = ccInput.value.toLowerCase();
    return list.filter(item => item.toLowerCase().includes(q)).slice(0, 15);
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
// 2. On Examination (O/E) Management
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
// 3. Diagnosis (Dx) Management
// ==========================================
const dxInput = ref('');
const dxType = ref('Final'); // Final or Provisional
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
// 4. Investigations (Ix) Management
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
// 5. Clinical Advice Management
// ==========================================
const adviceInput = ref('');
const showAdviceDropdown = ref(false);

const presetAdvices = [
    'Drink plenty of warm water & fluids.',
    'Avoid cold air, dust, and direct exposure to fans/AC.',
    'Avoid smoking, tobacco, and alcohol.',
    'Avoid oily, spicy, and deep-fried foods.',
    'Take medications at regular times as prescribed.',
    'Take adequate physical rest and avoid heavy lifting.',
    'Maintain a low sodium (salt) and low sugar diet.',
    'Contact immediately if symptoms worsen or emergency signs occur.',
    'Walk 30 minutes daily in the morning.',
    'Measure blood pressure and blood glucose regularly.'
];

const filteredAdviceList = computed(() => {
    const list = (props.lookups?.advices && props.lookups.advices.length) ? props.lookups.advices : presetAdvices;
    if (!adviceInput.value) return list.slice(0, 15);
    const q = adviceInput.value.toLowerCase();
    return list.filter(item => item.toLowerCase().includes(q)).slice(0, 15);
});

const addAdvice = (text = null) => {
    const adv = (typeof text === 'string' ? text : null) || adviceInput.value.trim();
    if (!adv) {
        showAdviceDropdown.value = !showAdviceDropdown.value;
        return;
    }
    if (form.clinical_advices.includes(adv)) {
        adviceInput.value = '';
        showAdviceDropdown.value = false;
        return;
    }

    form.clinical_advices.push(adv);
    adviceInput.value = '';
    showAdviceDropdown.value = false;
};

const removeAdvice = (index) => {
    form.clinical_advices.splice(index, 1);
};

// ==========================================
// 6. Medication / Rx Management
// ==========================================
const currentDrug = ref({
    drug_id: null,
    medicine_name: '',
    generic_name: '',
    dosage_form: 'Tablet',
    strength: '500mg',
    dosage: '1+0+1',
    duration: '7 Days',
    instruction: 'After meal',
    extra_notes: '',
});

const drugSearchQuery = ref('');
const showDrugSearchDropdown = ref(false);

const filteredDrugs = computed(() => {
    if (!drugSearchQuery.value) return props.drugs.slice(0, 10);
    const q = drugSearchQuery.value.toLowerCase();
    return props.drugs.filter(d =>
        d.trade_name.toLowerCase().includes(q) ||
        (d.generic_name && d.generic_name.toLowerCase().includes(q))
    ).slice(0, 15);
});

const selectDrugFromCatalog = (drug) => {
    const firstVariation = drug.variations?.[0] || {};
    currentDrug.value.drug_id = drug.id;
    currentDrug.value.medicine_name = drug.trade_name;
    currentDrug.value.generic_name = drug.generic_name || '';
    currentDrug.value.dosage_form = firstVariation.type || currentDrug.value.dosage_form || 'Tablet';
    currentDrug.value.strength = firstVariation.strength || currentDrug.value.strength || '500mg';
    currentDrug.value.dosage = firstVariation.dose || currentDrug.value.dosage || '1+0+1';
    currentDrug.value.duration = firstVariation.duration || currentDrug.value.duration || '7 Days';
    currentDrug.value.instruction = firstVariation.advice || currentDrug.value.instruction || 'After meal';

    drugSearchQuery.value = drug.trade_name;
    showDrugSearchDropdown.value = false;
};

const addMedication = () => {
    if (!currentDrug.value.medicine_name.trim()) return;

    let formattedName = currentDrug.value.medicine_name.trim();
    const formPrefix = currentDrug.value.dosage_form ? currentDrug.value.dosage_form : 'Tab';
    const strengthSuffix = currentDrug.value.strength ? currentDrug.value.strength : '';

    if (!formattedName.toLowerCase().includes(formPrefix.toLowerCase()) && !formattedName.toLowerCase().includes('tab') && !formattedName.toLowerCase().includes('cap')) {
        formattedName = `${formPrefix}. ${formattedName}`;
    }

    if (strengthSuffix && !formattedName.includes(strengthSuffix)) {
        formattedName = `${formattedName} ${strengthSuffix}`;
    }

    form.items.push({
        drug_id: currentDrug.value.drug_id,
        medicine_name: formattedName,
        generic_name: currentDrug.value.generic_name,
        dosage_form: currentDrug.value.dosage_form,
        strength: currentDrug.value.strength,
        dosage: currentDrug.value.dosage,
        duration: currentDrug.value.duration,
        instruction: currentDrug.value.instruction,
        extra_notes: currentDrug.value.extra_notes,
        item_order: form.items.length + 1,
    });

    // Reset current drug bar
    currentDrug.value = {
        drug_id: null,
        medicine_name: '',
        generic_name: '',
        dosage_form: 'Tablet',
        strength: '500mg',
        dosage: '1+0+1',
        duration: '7 Days',
        instruction: 'After meal',
        extra_notes: '',
    };
    drugSearchQuery.value = '';
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

// Available Lookups from props or defaults
const availableDrugTypes = computed(() => {
    const list = props.lookups?.drugTypes;
    return (list && list.length) ? list : ['Tablet', 'Capsule', 'Syrup', 'Suspension', 'Injection', 'Eye/Ear Drop', 'Ointment', 'Inhaler', 'Suppository', 'Oral Gel'];
});

const availableDrugStrengths = computed(() => {
    const list = props.lookups?.drugStrengths;
    return (list && list.length) ? list : ['500mg', '650mg', '250mg', '125mg', '20mg', '40mg', '10mg', '5mg', '2.5mg', '1mg', '100ml', '200ml', '100IU', '500IU'];
});

const availableDrugDoses = computed(() => {
    const list = props.lookups?.drugDoses;
    if (list && list.length) {
        return list.map(d => typeof d === 'string' ? d : d.name);
    }
    return ['1+0+1', '1+1+1', '1+0+0', '0+0+1', '0+1+0', '1+1+1+1', '1/2+0+1/2', '1 tsp 3 times daily', '2 tsp 3 times daily', '1 Puff 2 times daily'];
});

const availableDrugDurations = computed(() => {
    const list = props.lookups?.drugDurations;
    return (list && list.length) ? list : ['3 Days', '5 Days', '7 Days', '10 Days', '14 Days', '1 Month', '2 Months', '3 Months', 'Continue', 'As needed'];
});

const availableDrugAdvices = computed(() => {
    const list = props.lookups?.drugAdvices;
    if (list && list.length) {
        return list.map(a => typeof a === 'string' ? a : a.name);
    }
    return ['Before meal', 'After meal', 'Empty stomach', 'At bedtime', 'With lukewarm water'];
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

// Submit Handler
const submitPrescription = (shouldPrint = false) => {
    if (!form.patient_id) {
        alert('Please select a patient before saving the prescription.');
        return;
    }
    if (!form.items.length) {
        alert('Please add at least one medication (Rx) to the prescription.');
        return;
    }

    form.post(route('prescriptions.store'), {
        onSuccess: (page) => {
            if (shouldPrint && page.props.prescription?.id) {
                window.open(route('prescriptions.print', page.props.prescription.id), '_blank');
            }
        }
    });
};
</script>

<template>
    <Head title="Write Prescription - Doc Helper AI" />

    <AuthenticatedLayout>
        <div class="space-y-4 max-w-7xl mx-auto pb-16">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('prescriptions.index')"
                        class="p-2 rounded-xl bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-[#30363D] transition-colors text-slate-700 dark:text-slate-200"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-teal-600 text-white font-bold text-xs flex items-center justify-center">Rx</span>
                            Interactive Prescription Pad
                        </h1>
                        <p class="text-[11px] text-slate-400">
                            Fast 2-column clinical canvas with auto-lookups, drug presets, and instant print.
                        </p>
                    </div>
                </div>

                <!-- Top Right Control Buttons -->
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="submitPrescription(false)"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl shadow-xs cursor-pointer disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Saving...' : 'Save Prescription' }}</span>
                    </button>

                    <button
                        type="button"
                        @click="submitPrescription(true)"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer disabled:opacity-50"
                    >
                        <Printer class="w-4 h-4" />
                        <span>Save & Print</span>
                    </button>
                </div>
            </div>

            <!-- Patient & Chamber Banner -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <!-- Patient Selector / Autocomplete -->
                <div class="relative space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Patient <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <User class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="patientQuery"
                            @focus="showPatientDropdown = true"
                            type="text"
                            placeholder="Type patient name, phone, PID..."
                            class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500"
                        />
                    </div>

                    <!-- Autocomplete dropdown -->
                    <div
                        v-if="showPatientDropdown && filteredPatients.length"
                        class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl shadow-lg max-h-48 overflow-y-auto custom-scrollbar"
                    >
                        <button
                            v-for="p in filteredPatients"
                            :key="p.id"
                            type="button"
                            @click="selectPatient(p)"
                            class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-[#21262D] border-b border-slate-100 dark:border-slate-800 last:border-none flex items-center justify-between"
                        >
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ p.name }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ p.patient_code }} • {{ p.phone || 'No phone' }}</p>
                            </div>
                            <span class="text-[10px] font-semibold text-teal-600 dark:text-teal-400">{{ p.age ? p.age + 'y' : '' }} {{ p.gender }}</span>
                        </button>
                    </div>
                </div>

                <!-- Chamber Selector -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                        Chamber <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <Building2 class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <select
                            v-model="form.chamber_id"
                            required
                            class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-medium"
                        >
                            <option v-for="ch in chambers" :key="ch.id" :value="ch.id">
                                {{ ch.name }} {{ ch.branch ? '(' + ch.branch + ')' : '' }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Date & Status -->
                <div class="grid grid-cols-2 gap-2">
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">Date</label>
                        <input
                            v-model="form.prescription_date"
                            type="date"
                            required
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-medium"
                        />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">Status</label>
                        <select
                            v-model="form.status"
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-medium capitalize"
                        >
                            <option value="finalized">Finalized</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Patient Quick Info Strip -->
            <div v-if="currentPatient" class="bg-teal-50/60 dark:bg-teal-950/20 border border-teal-200/80 dark:border-teal-900/60 p-3 rounded-2xl flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ currentPatient.name.charAt(0).toUpperCase() }}
                    </span>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">{{ currentPatient.name }}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">PID: {{ currentPatient.patient_code }} | Phone: {{ currentPatient.phone || 'N/A' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-slate-700 dark:text-slate-300">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold">Age / Gender</span>
                        <span class="font-bold">{{ currentPatient.age ? currentPatient.age + ' Years' : 'N/A' }}, {{ currentPatient.gender }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold">Blood Group</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400">{{ currentPatient.blood_group || 'N/A' }}</span>
                    </div>
                    <div v-if="currentPatient.address">
                        <span class="text-[10px] text-slate-400 uppercase block font-semibold">Address</span>
                        <span class="font-medium truncate max-w-xs">{{ currentPatient.address }}</span>
                    </div>

                    <!-- 1-Click AI Longitudinal History Trigger Button -->
                    <button
                        v-if="$page.props.auth?.can_use_ai"
                        type="button"
                        @click="openPatientHistoryModal"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-700 hover:to-cyan-700 text-white rounded-xl font-bold text-xs shadow-md shadow-teal-600/20 transition-all cursor-pointer"
                        title="View AI Timeline and Longitudinal History"
                    >
                        <Sparkles class="w-3.5 h-3.5" />
                        <span>AI History Summary</span>
                    </button>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- MAIN 2-COLUMN PRESCRIPTION CANVAS                                         -->
            <!-- ========================================================================= -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

                <!-- ===================================================================== -->
                <!-- LEFT COLUMN: Clinical Findings (Vitals, C/C, O/E, Dx, Ix, Advices)   -->
                <!-- ===================================================================== -->
                <div class="lg:col-span-5 space-y-4">

                    <!-- 1. Vitals & O/E Strip -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Activity class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" />
                            <span>Vitals & Physical Examination (O/E)</span>
                        </h2>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">BP (mmHg)</label>
                                <input
                                    v-model="form.vitals.bp"
                                    type="text"
                                    placeholder="120/80"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">Pulse (bpm)</label>
                                <input
                                    v-model="form.vitals.pulse"
                                    type="text"
                                    placeholder="78"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">Temp (°F)</label>
                                <input
                                    v-model="form.vitals.temperature"
                                    type="text"
                                    placeholder="98.6"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">Weight (kg)</label>
                                <input
                                    v-model="form.vitals.weight"
                                    @input="calculateBmi"
                                    type="number"
                                    placeholder="65"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">Height</label>
                                <input
                                    v-model="form.vitals.height"
                                    @input="calculateBmi"
                                    type="text"
                                    placeholder="5'7&quot;"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-0.5">BMI / SpO2</label>
                                <input
                                    v-model="form.vitals.bmi"
                                    type="text"
                                    placeholder="23.5"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
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

                    <!-- 2. Chief Complaints (C/C) -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Stethoscope class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" />
                            <span>Chief Complaints (C/C)</span>
                        </h2>

                        <!-- Add C/C Input & Quick Autocomplete -->
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
                                    <!-- Autocomplete -->
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

                        <!-- C/C Added List -->
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
                                <button type="button" @click="removeCc(idx)" class="text-slate-400 hover:text-rose-500 cursor-pointer">
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Diagnosis (Dx) -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Sparkles class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" />
                            <span>Diagnosis (Dx)</span>
                        </h2>

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

                        <!-- Dx Added List -->
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
                                <button type="button" @click="removeDx(idx)" class="text-slate-400 hover:text-rose-500 cursor-pointer">
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Investigations / Tests (Ix) -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <FileText class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                            <span>Investigations & Tests (Ix)</span>
                        </h2>

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

                        <!-- Ix Added List -->
                        <div v-if="form.investigations.length" class="space-y-1.5 pt-1">
                            <div
                                v-for="(ix, idx) in form.investigations"
                                :key="idx"
                                class="flex items-center justify-between px-3 py-1.5 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/60 rounded-xl text-xs"
                            >
                                <span class="font-bold text-slate-900 dark:text-white">{{ ix.test_name }}</span>
                                <button type="button" @click="removeIx(idx)" class="text-slate-400 hover:text-rose-500 cursor-pointer">
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Clinical Advices & Follow-up -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <CheckCircle2 class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" />
                            <span>Clinical Advices & Next Visit</span>
                        </h2>

                        <!-- Custom Advice Input & Dropdown -->
                        <div class="relative">
                            <input
                                v-model="adviceInput"
                                @focus="showAdviceDropdown = true"
                                @keyup.enter="addAdvice()"
                                type="text"
                                placeholder="Type advice & press Enter..."
                                class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                            />
                            <!-- Autocomplete -->
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

                        <!-- Selected Advices -->
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

                        <!-- Next Visit / Follow-up -->
                        <div class="pt-2 border-t border-slate-100 dark:border-[#21262D] space-y-2">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">Next Visit / Follow-up</label>
                            <div class="flex items-center gap-1.5 flex-wrap text-[10px]">
                                <button
                                    v-for="fup in ['After 3 days', 'After 7 days', 'After 14 days', 'After 1 month', 'As needed']"
                                    :key="fup"
                                    type="button"
                                    @click="form.follow_up_date = fup"
                                    class="px-2.5 py-1 rounded-lg border transition-colors cursor-pointer font-medium"
                                    :class="form.follow_up_date === fup ? 'bg-teal-600 text-white border-teal-600 font-bold' : 'bg-slate-50 dark:bg-[#21262D] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-[#30363D]'"
                                >
                                    {{ fup }}
                                </button>
                            </div>
                            <input
                                v-model="form.follow_up_notes"
                                type="text"
                                placeholder="Follow-up instructions (e.g. Please review with reports)"
                                class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- ===================================================================== -->
                <!-- RIGHT COLUMN: Rx Medication Canvas                                    -->
                <!-- ===================================================================== -->
                <div class="lg:col-span-7 space-y-4">

                    <!-- AI Drug-to-Drug Interaction & Allergy Checker -->
                    <AiDrugInteractionChecker
                        v-if="$page.props.auth?.can_use_ai"
                        :drugs="form.items"
                        :patient="currentPatient"
                        :diagnoses="form.diagnoses"
                    />

                    <!-- Rx Medication Builder Box -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span class="text-xl font-serif font-black text-teal-600 dark:text-teal-400">℞</span>
                                <span>Prescribed Medications (Rx)</span>
                            </h2>
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                {{ form.items.length }} Added
                            </span>
                        </div>

                        <!-- 1. Search Brand / Generic Medicine -->
                        <div class="relative space-y-1">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                Medicine Name (Brand or Generic) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <Pill class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                                <input
                                    v-model="drugSearchQuery"
                                    @focus="showDrugSearchDropdown = true"
                                    @input="currentDrug.medicine_name = drugSearchQuery"
                                    type="text"
                                    placeholder="Type medicine name (e.g. Napa Extra, Maxpro, Seclo, Azithrocin, Tusca)..."
                                    class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-teal-500 font-semibold"
                                />
                                <button
                                    v-if="drugSearchQuery"
                                    type="button"
                                    @click="drugSearchQuery = ''; currentDrug.medicine_name = ''; showDrugSearchDropdown = false"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>

                            <!-- Autocomplete Dropdown List -->
                            <div
                                v-if="showDrugSearchDropdown && filteredDrugs.length"
                                class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl shadow-xl max-h-60 overflow-y-auto custom-scrollbar"
                            >
                                <button
                                    v-for="d in filteredDrugs"
                                    :key="d.id"
                                    type="button"
                                    @mousedown.prevent="selectDrugFromCatalog(d)"
                                    class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-[#21262D] border-b border-slate-100 dark:border-slate-800 last:border-none flex items-center justify-between cursor-pointer"
                                >
                                    <div>
                                        <p class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium">
                                                {{ d.variations?.[0]?.type || 'Tab' }}
                                            </span>
                                            {{ d.trade_name }}
                                            <span v-if="d.variations?.[0]?.strength" class="text-[11px] text-teal-600 font-semibold font-mono">{{ d.variations[0].strength }}</span>
                                        </p>
                                        <p class="text-[10px] text-slate-400 italic mt-0.5">{{ d.generic_name || 'Generic' }}</p>
                                    </div>
                                    <span class="text-[10px] text-teal-600 dark:text-teal-400 font-bold bg-teal-50 dark:bg-teal-950/40 px-2 py-0.5 rounded border border-teal-200 dark:border-teal-800">+ Select</span>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Drug Selectors Row: Type, Strength, Dosage, Duration -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <!-- Drug Type Selector -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Form / Type</label>
                                <select
                                    v-model="currentDrug.dosage_form"
                                    class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-medium"
                                >
                                    <option v-for="t in availableDrugTypes" :key="t" :value="t">{{ t }}</option>
                                </select>
                            </div>

                            <!-- Drug Strength Selector / Datalist -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Strength</label>
                                <div class="relative">
                                    <select
                                        v-model="currentDrug.strength"
                                        class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-medium"
                                    >
                                        <option v-for="s in availableDrugStrengths" :key="s" :value="s">{{ s }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Dosage Selector / Input -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Dosage</label>
                                <select
                                    v-model="currentDrug.dosage"
                                    class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-mono font-bold text-center"
                                >
                                    <option v-for="dose in availableDrugDoses" :key="dose" :value="dose">{{ dose }}</option>
                                </select>
                            </div>

                            <!-- Duration Selector -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Duration</label>
                                <select
                                    v-model="currentDrug.duration"
                                    class="w-full px-2 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-medium text-center"
                                >
                                    <option v-for="dur in availableDrugDurations" :key="dur" :value="dur">{{ dur }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- 3. Instruction & Presets -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <!-- Instruction / Advice Selector -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Instruction / Timing</label>
                                <select
                                    v-model="currentDrug.instruction"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white font-medium"
                                >
                                    <option v-for="inst in availableDrugAdvices" :key="inst" :value="inst">{{ inst }}</option>
                                </select>
                            </div>

                            <!-- Special Notes -->
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 mb-0.5">Special Instruction (Optional)</label>
                                <input
                                    v-model="currentDrug.extra_notes"
                                    @keyup.enter="addMedication"
                                    type="text"
                                    placeholder="e.g. In case of fever/severe pain"
                                    class="w-full px-2.5 py-1.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-lg text-slate-900 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Add Medicine Button -->
                        <div class="flex justify-end pt-2">
                            <button
                                type="button"
                                @click="addMedication"
                                class="inline-flex items-center justify-center gap-1.5 px-6 py-2.5 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl cursor-pointer shadow-xs transition-all"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Medication to Prescription</span>
                            </button>
                        </div>
                    </div>

                    <!-- Added Prescription Items (Medications List) -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-3">
                        <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-2">
                            <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                Prescribed Medicines ({{ form.items.length }})
                            </h3>
                            <span class="text-[11px] text-slate-400">Order on printed prescription</span>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!form.items.length" class="py-12 text-center text-slate-400">
                            <Pill class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                            <p class="font-bold text-slate-600 dark:text-slate-300 text-xs">No medications added yet</p>
                            <p class="text-[11px] mt-0.5">Use the search & selection bar above to add medications.</p>
                        </div>

                        <!-- Items List -->
                        <div v-else class="space-y-2">
                            <div
                                v-for="(item, idx) in form.items"
                                :key="idx"
                                class="p-3 bg-slate-50/75 dark:bg-[#21262D]/60 border border-slate-200/80 dark:border-[#30363D] rounded-xl flex items-start justify-between gap-3 hover:border-teal-400 transition-colors"
                            >
                                <div class="flex items-start gap-2.5">
                                    <span class="w-6 h-6 rounded-lg bg-[#0A0D36] dark:bg-teal-500 text-white dark:text-slate-950 font-mono font-bold text-xs flex items-center justify-center shrink-0 mt-0.5">
                                        {{ idx + 1 }}
                                    </span>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-bold text-xs text-slate-900 dark:text-white">
                                                {{ item.medicine_name }}
                                            </p>
                                            <span v-if="item.generic_name" class="text-[10px] text-slate-400 italic">
                                                ({{ item.generic_name }})
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-2 text-xs flex-wrap">
                                            <span class="font-mono font-bold text-teal-700 dark:text-teal-300 bg-teal-50 dark:bg-teal-950/60 px-2 py-0.5 rounded border border-teal-200 dark:border-teal-800">
                                                {{ item.dosage }}
                                            </span>
                                            <span class="text-slate-600 dark:text-slate-300 font-medium">
                                                — {{ item.duration }}
                                            </span>
                                            <span class="text-slate-500 dark:text-slate-400 text-[11px]">
                                                ({{ item.instruction }})
                                            </span>
                                            <span v-if="item.extra_notes" class="text-[11px] text-amber-600 dark:text-amber-400 italic font-medium">
                                                * {{ item.extra_notes }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action buttons (Reorder / Remove) -->
                                <div class="flex items-center gap-1 shrink-0">
                                    <button
                                        type="button"
                                        @click="moveMedication(idx, -1)"
                                        :disabled="idx === 0"
                                        class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 disabled:opacity-20 cursor-pointer"
                                        title="Move Up"
                                    >
                                        <ChevronUp class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="moveMedication(idx, 1)"
                                        :disabled="idx === form.items.length - 1"
                                        class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 disabled:opacity-20 cursor-pointer"
                                        title="Move Down"
                                    >
                                        <ChevronDown class="w-4 h-4" />
                                    </button>
                                    <button
                                        type="button"
                                        @click="removeMedication(idx)"
                                        class="p-1 text-slate-400 hover:text-rose-600 cursor-pointer"
                                        title="Remove Medicine"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Private Doctor Notes -->
                    <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs space-y-2">
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                            Private Doctor Notes (Internal Use Only - Not Printed on Rx Pad)
                        </label>
                        <textarea
                            v-model="form.doctor_notes"
                            rows="2"
                            placeholder="Add any private clinical observations, patient compliance remarks, or follow-up plan..."
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                        ></textarea>
                    </div>

                </div>
            </div>

            <!-- Sticky Bottom Action Bar -->
            <div class="sticky bottom-4 z-40 bg-white/95 dark:bg-[#161B22]/95 backdrop-blur-md p-3 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-lg flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                        Total Medications: <span class="text-teal-600 font-mono text-sm">{{ form.items.length }}</span>
                    </span>
                    <span v-if="form.diagnoses.length" class="text-xs text-slate-500 dark:text-slate-400 hidden sm:inline">
                        • Dx: {{ form.diagnoses.map(d => d.diagnosis).join(', ') }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('prescriptions.index')"
                        class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-pointer"
                    >
                        Cancel
                    </Link>

                    <button
                        type="button"
                        @click="submitPrescription(false)"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0A0D36] dark:bg-teal-500 hover:bg-[#12164A] dark:hover:bg-teal-600 text-white dark:text-slate-950 text-xs font-bold rounded-xl shadow-xs cursor-pointer disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Saving...' : 'Save Prescription' }}</span>
                    </button>

                    <button
                        type="button"
                        @click="submitPrescription(true)"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs cursor-pointer disabled:opacity-50"
                    >
                        <Printer class="w-4 h-4" />
                        <span>Save & Print Pad</span>
                    </button>
                </div>
            </div>

            <!-- AI Patient Longitudinal Medical History Summary Modal -->
            <div
                v-if="isPatientHistoryModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs"
                @click.self="isPatientHistoryModalOpen = false"
            >
                <div class="bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-2xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                    <!-- Modal Header -->
                    <div class="p-4 border-b border-slate-100 dark:border-[#21262D] bg-gradient-to-r from-teal-50/60 to-cyan-50/20 dark:from-teal-950/30 dark:to-cyan-950/10 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-teal-600 to-cyan-500 text-white flex items-center justify-center shadow-md shadow-teal-600/20">
                                <Sparkles class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    AI Longitudinal History: {{ currentPatient?.name }}
                                </h3>
                                <p class="text-[11px] text-slate-400">
                                    PID: {{ currentPatient?.patient_code }} • Rapid Clinical Brief
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="isPatientHistoryModalOpen = false"
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D] transition cursor-pointer"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-5 overflow-y-auto space-y-4 custom-scrollbar text-xs">
                        <!-- Loading -->
                        <div v-if="patientHistoryLoading" class="py-12 flex flex-col items-center justify-center gap-3 text-center">
                            <div class="w-8 h-8 rounded-full border-2 border-teal-500 border-t-transparent animate-spin"></div>
                            <p class="text-xs text-slate-500">AI is consolidating historical visits, diagnoses, and regimens...</p>
                        </div>

                        <!-- Error -->
                        <div v-else-if="patientHistoryError" class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200">
                            {{ patientHistoryError }}
                        </div>

                        <!-- Content -->
                        <div v-else-if="patientHistoryData" class="space-y-4">
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#11141A] border border-slate-200 dark:border-[#21262D] space-y-2">
                                <div class="flex items-center justify-between font-bold text-teal-700 dark:text-teal-400 text-[11px] uppercase tracking-wider">
                                    <span>Clinical Overview</span>
                                    <span class="text-slate-400 font-normal">Visits: {{ patientHistoryData.total_visits }} ({{ patientHistoryData.first_visit }} - {{ patientHistoryData.latest_visit }})</span>
                                </div>
                                <div class="text-xs text-slate-700 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                                    {{ patientHistoryData.summary }}
                                </div>
                            </div>

                            <!-- Highlights -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#1E2633]/60 border border-slate-200/80 dark:border-[#30363D] space-y-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 block">Chronic Conditions</span>
                                    <div v-if="patientHistoryData.chronic_conditions?.length" class="flex flex-wrap gap-1">
                                        <span v-for="c in patientHistoryData.chronic_conditions" :key="c" class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950 text-[10px] font-semibold text-indigo-700 dark:text-indigo-300">
                                            {{ c }}
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400 italic text-[11px]">None recorded</span>
                                </div>

                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-[#1E2633]/60 border border-slate-200/80 dark:border-[#30363D] space-y-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400 block">Allergies & Contraindications</span>
                                    <p class="text-slate-700 dark:text-slate-300 text-[11px] font-medium">
                                        {{ patientHistoryData.allergies || 'No known allergies' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Active Medications -->
                            <div v-if="patientHistoryData.active_medications?.length" class="p-3 rounded-xl bg-slate-50 dark:bg-[#1E2633]/60 border border-slate-200/80 dark:border-[#30363D] space-y-1.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400 block">Current / Active Regimens (Rx)</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <div v-for="drug in patientHistoryData.active_medications" :key="drug.name" class="p-1.5 rounded bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] text-[11px] flex justify-between">
                                        <span class="font-medium truncate">{{ drug.name }} {{ drug.strength }}</span>
                                        <span class="text-slate-400 font-mono text-[10px]">{{ drug.dose }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Recommendations -->
                            <div v-if="patientHistoryData.key_recommendations?.length" class="p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800 text-[11px] space-y-1">
                                <span class="font-bold text-emerald-800 dark:text-emerald-400 block">AI Focus Recommendations:</span>
                                <ul class="list-disc list-inside text-emerald-900 dark:text-emerald-200 space-y-0.5">
                                    <li v-for="(rec, rIdx) in patientHistoryData.key_recommendations" :key="rIdx">
                                        {{ rec }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-3.5 border-t border-slate-100 dark:border-[#21262D] bg-slate-50 dark:bg-[#161B22] flex items-center justify-between">
                        <Link
                            :href="route('patients.show', currentPatient?.id)"
                            target="_blank"
                            class="text-xs font-bold text-teal-600 dark:text-teal-400 hover:underline"
                        >
                            Open Full Patient Timeline View →
                        </Link>
                        <button
                            type="button"
                            @click="isPatientHistoryModalOpen = false"
                            class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition cursor-pointer"
                        >
                            Close Brief
                        </button>
                    </div>
                </div>
            </div>

            <!-- AI Clinical Copilot Slide-out Chat Drawer -->
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
        </div>
    </AuthenticatedLayout>
</template>
