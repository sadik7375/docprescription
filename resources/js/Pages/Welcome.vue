<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    Mail,
    Phone,
    MessageCircle,
    Send,
    Stethoscope,
    Sparkles,
    FileText,
    Users,
    Building2,
    Calendar,
    Pill,
    Printer,
    ShieldCheck,
    CheckCircle2,
    ArrowRight,
    Zap,
    Clock,
    HeartPulse,
    Activity,
    Star,
    ChevronDown,
    Search,
    Plus,
    Check,
    FolderKanban,
    UserCheck,
    Lock,
    CheckCircle,
    SlidersHorizontal,
    Globe,
    MapPin,
    ExternalLink,
    Shield,
    ArrowUp,
    HelpCircle,
    X
} from 'lucide-vue-next';

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: true
    },
    canRegister: {
        type: Boolean,
        default: true
    },
    pricingPlans: {
        type: Array,
        default: () => []
    },
    landingContent: {
        type: Object,
        default: () => ({})
    },
    annualDiscountBadge: {
        type: String,
        default: 'SAVE 25%'
    },
    supportContacts: {
        type: Object,
        default: () => ({
            email: 'contact@cannyapps.com',
            phone: '+1 (800) 555-0199',
            whatsapp: '+18005550199',
            hours: 'Monday - Friday: 9:00 AM - 8:00 PM EST',
            address: '100 Medical Plaza, Suite 400, San Francisco, CA 94143',
        })
    },
    laravelVersion: String,
    phpVersion: String,
});

// Safe defaults for dynamic landing content
const lc = {
    brand_name: props.landingContent?.brand_name || 'Doc Helper',
    brand_name_highlight: props.landingContent?.brand_name_highlight || 'AI',
    brand_tagline: props.landingContent?.brand_tagline || 'Clinical Practice OS',
    brand_logo_url: props.landingContent?.brand_logo_url || null,
    brand_favicon_url: props.landingContent?.brand_favicon_url || null,
    hero_image_url: props.landingContent?.hero_image_url || null,
    meta_title: props.landingContent?.meta_title || 'Doc Helper AI - Intelligent Medical Prescription & Clinic Practice OS',
    meta_description: props.landingContent?.meta_description || 'Generate comprehensive, flawless digital prescriptions in under 45 seconds.',
    hero_badge: props.landingContent?.hero_badge || 'Next-Generation Medical Practice & AI Prescription Platform',
    hero_version_text: props.landingContent?.hero_version_text || 'v2.4 Live',
    hero_title_line1: props.landingContent?.hero_title_line1 || 'The Smart AI-Powered',
    hero_title_highlight: props.landingContent?.hero_title_highlight || 'Prescription & Practice OS',
    hero_title_line2: props.landingContent?.hero_title_line2 || 'for Modern Doctors',
    hero_subtitle: props.landingContent?.hero_subtitle || 'Generate comprehensive, flawless digital prescriptions in under 45 seconds. Manage multi-chamber queues, patient vitals, diagnosis lookups, and instant branded printouts effortlessly.',
    hero_cta_primary_text: props.landingContent?.hero_cta_primary_text || 'Start 14-Day Free Trial',
    hero_cta_primary_link: props.landingContent?.hero_cta_primary_link || '/register',
    hero_cta_secondary_text: props.landingContent?.hero_cta_secondary_text || 'Try Interactive Demo Rx',
    hero_cta_secondary_link: props.landingContent?.hero_cta_secondary_link || '#live-demo',
    trust_point_1: props.landingContent?.trust_point_1 || 'No Credit Card Required',
    trust_point_2: props.landingContent?.trust_point_2 || 'HIPAA-Ready Architecture',
    trust_point_3: props.landingContent?.trust_point_3 || '4.9/5 Rating (2,500+ Doctors)',
    features_badge: props.landingContent?.features_badge || 'Engineered for High-Volume Clinical Precision',
    features_title: props.landingContent?.features_title || 'Everything a Doctor Needs in One Seamless Platform',
    features_subtitle: props.landingContent?.features_subtitle || 'Replace messy paper pads, slow generic ERPs, and disjointed systems with a purpose-built medical practice operating system.',
    specialties_title: props.landingContent?.specialties_title || 'Tailored for Every Medical Specialty',
    specialties_subtitle: props.landingContent?.specialties_subtitle || 'Customizable clinical templates and database lookups optimized for individual clinical workflows.',
    testimonials_title: props.landingContent?.testimonials_title || 'Trusted by Thousands of Doctors Worldwide',
    testimonials_subtitle: props.landingContent?.testimonials_subtitle || 'See how clinical specialists streamline their daily patient consultations.',
    faqs_title: props.landingContent?.faqs_title || 'Frequently Asked Questions',
    faqs_subtitle: props.landingContent?.faqs_subtitle || 'Everything you need to know about Doc Helper AI and clinical deployment.',
    bottom_cta_title: props.landingContent?.bottom_cta_title || 'Transform Your Medical Practice Today',
    bottom_cta_subtitle: props.landingContent?.bottom_cta_subtitle || 'Join thousands of doctors who generate accurate digital prescriptions and manage their clinical chambers with Doc Helper AI.',
    bottom_cta_btn_text: props.landingContent?.bottom_cta_btn_text || 'Start 14-Day Free Trial',
    bottom_cta_note: props.landingContent?.bottom_cta_note || 'Instant Setup • No Installation Required • Cloud & Dedicated Host Ready',
    footer_about: props.landingContent?.footer_about || 'The comprehensive medical prescription generator, clinical lookup management, and multi-chamber practice operating system for physicians, clinics, and health systems.',
    footer_copyright: props.landingContent?.footer_copyright || '© Doc Helper AI. All rights reserved. International Medical Software Standard.',
    privacy_policy: props.landingContent?.privacy_policy || null,
    terms_of_service: props.landingContent?.terms_of_service || null,
    medical_disclaimer: props.landingContent?.medical_disclaimer || null,
};

// Contact Form State
const contactForm = useForm({
    name: '',
    email: '',
    phone: '',
    subject: '',
    inquiry_type: 'sales_demo',
    message: '',
});

const isSubmitted = ref(false);

const submitContact = () => {
    contactForm.post(route('contact.submit'), {
        preserveScroll: true,
        onSuccess: () => {
            isSubmitted.value = true;
            contactForm.reset();
            setTimeout(() => {
                isSubmitted.value = false;
            }, 6000);
        },
    });
};

// Legal Modals & Navigation
const activeLegalModal = ref(null); // 'privacy' | 'terms' | 'security' | 'disclaimer' | null

const openLegalModal = (type) => {
    activeLegalModal.value = type;
};

const closeLegalModal = () => {
    activeLegalModal.value = null;
};

const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Interactive state for Pricing
const billingPeriod = ref('yearly'); // 'monthly' | 'yearly'

const getPlanButtonLink = (plan) => {
    // If plan button link is an anchor (e.g. #contact or #live-demo)
    if (plan.button_link && plan.button_link.startsWith('#')) {
        return plan.button_link;
    }

    const planSlug = plan.slug || (
        plan.name?.toLowerCase().includes('solo') ? 'starter' :
        (plan.name?.toLowerCase().includes('pro') ? 'pro' : 'enterprise')
    );

    // If user is already authenticated (Doctor/Staff), go directly to doctor dashboard subscription manager
    // If guest user, redirect to subscription index which passes through auth/login and retains intent
    return route('subscription.index', {
        plan: planSlug,
        billing: billingPeriod.value,
    });
};

// Interactive FAQ Accordion
const activeFaq = ref(0);
const toggleFaq = (index) => {
    activeFaq.value = activeFaq.value === index ? null : index;
};

// Interactive Demo Rx Preview state
const demoDrugs = ref([
    {
        type: 'Tab.',
        name: 'Azithromycin 500mg',
        dosage: '1+0+1',
        advice: 'After Meal',
        duration: '5 Days',
    },
    {
        type: 'Cap.',
        name: 'Esomeprazole 40mg',
        dosage: '1+0+1',
        advice: 'Before Meal',
        duration: '14 Days',
    },
    {
        type: 'Syp.',
        name: 'Paracetamol 120mg/5ml',
        dosage: '1+1+1',
        advice: 'When fever > 100°F',
        duration: '3 Days',
    }
]);

const newDrugName = ref('');
const addDemoDrug = () => {
    if (!newDrugName.value.trim()) return;
    demoDrugs.value.push({
        type: 'Tab.',
        name: newDrugName.value.trim(),
        dosage: '1+0+1',
        advice: 'After Meal',
        duration: '7 Days',
    });
    newDrugName.value = '';
};

const removeDemoDrug = (index) => {
    demoDrugs.value.splice(index, 1);
};

const faqs = props.landingContent?.faqs && props.landingContent.faqs.length ? props.landingContent.faqs : [
    {
        q: 'Can I customize the prescription header with my medical degree and registration number?',
        a: 'Yes, absolutely. Doc Helper AI includes a dedicated Doctor Profile & Credentials Manager where you can add all your medical degrees (MBBS, FCPS, MD, MRCP, FRCS, etc.), medical registration/license number, specialty, workplace affiliations, and custom signature text with a live prescription header preview.'
    },
    {
        q: 'Does Doc Helper AI support multiple chambers and different visiting hours?',
        a: 'Yes! You can add and manage unlimited chambers or hospital visiting rooms. Each chamber has its own consultation fees, follow-up fees, visiting schedule, room numbers, and patient queues. You can switch between active chambers with a single click.'
    },
    {
        q: 'Is this software ready for international deployment and SaaS licensing?',
        a: 'Yes. Doc Helper AI is built according to 100% international medical practice standards in clean English, making it perfect for private clinics, multi-doctor hospitals, and CodeCanyon SaaS distribution.'
    },
    {
        q: 'How fast is prescription generation compared to traditional writing?',
        a: 'With intelligent type-ahead auto-complete, pre-saved clinical lookups (Chief Complaints, Diagnoses, Investigations, General Advice) and drug dosages, doctors routinely draft complete, error-free prescriptions in under 45 seconds.'
    },
    {
        q: 'Does Doc Helper AI support high-resolution printing on custom letterheads or thermal printers?',
        a: 'Yes. You can print directly to standard A4/A5 prescription pads, plain white paper with customizable headers, pre-printed clinic letterheads (with margins adjusted), or digital PDF downloads for patient sharing.'
    },
    {
        q: 'Can patients access their medical history and previous prescriptions?',
        a: 'Yes. Doc Helper AI features an integrated Patient Portal module where doctors or staff can create secure patient logins, allowing patients to view their previous prescriptions, vital trends, and follow-up dates.'
    }
];

const featureList = props.landingContent?.features && props.landingContent.features.length ? props.landingContent.features : [
    {
        title: 'Smart Type-Ahead Rx Pad',
        desc: "Blazing fast dosage shortcuts ('1+0+1', '1+1+1+1'), intelligent meal advice chips, auto-duration calculations, and customized drug favorites.",
        color: 'teal'
    },
    {
        title: 'Multi-Chamber & Visiting Slots',
        desc: 'Manage multiple private clinics and hospital chambers with specific visiting days, consulting hours, fees, room numbers, and active switchers.',
        color: 'cyan'
    },
    {
        title: 'Live Queue & Patient Vitals',
        desc: 'Organize daily patient queues, capture comprehensive vitals (BP, Pulse, SpO2, BMI, Temp), and update consultation status in real-time.',
        color: 'indigo'
    },
    {
        title: 'Clinical & Drug Database',
        desc: 'Pre-loaded lookup libraries for Chief Complaints, ICD-10 Diagnoses, Investigations, Advice, and custom brand/generic medicines.',
        color: 'emerald'
    },
    {
        title: '1-Click High-Res Print & PDF',
        desc: 'Generate clean, international medical print layouts with degrees, Reg. No, customized seals, QR verification, and margin adjustment.',
        color: 'purple'
    },
    {
        title: 'Patient Portal & History',
        desc: 'Enable patient portal access for digital prescriptions, past visit summaries, follow-up date notifications, and paperless medical records.',
        color: 'rose'
    }
];

const testimonialsList = props.landingContent?.testimonials && props.landingContent.testimonials.length ? props.landingContent.testimonials : [
    {
        quote: 'Doc Helper AI cut my consultation prescription time from 5 minutes down to 35 seconds. The dual-chamber switcher lets me manage both my hospital and private practice seamlessly.',
        author: 'Dr. Alexander King',
        title: 'Cardiologist, Apollo Health',
        rating: 5,
    },
    {
        quote: 'The print layout is crisp and professional. All my qualifications and medical registration details are automatically positioned. Patients love the clean, legible prescriptions.',
        author: 'Dr. Elena Morales',
        title: 'Senior Consultant Pediatrician',
        rating: 5,
    },
    {
        quote: 'The best SaaS medical tool we have deployed. As a clinic director, managing 12 visiting specialists with distinct schedules and queues has never been this smooth.',
        author: 'Dr. Robert Jenkins',
        title: 'Medical Director, Apex Health',
        rating: 5,
    }
];

const stats = props.landingContent?.stats && props.landingContent.stats.length ? props.landingContent.stats : [
    { value: '< 45s', label: 'Average Rx Creation Time', sub: 'Save 3+ hours daily' },
    { value: '2.5M+', label: 'Prescriptions Generated', sub: 'Across 40+ countries' },
    { value: '99.8%', label: 'Dosage Precision Rate', sub: 'Zero handwriting errors' },
    { value: '45+', label: 'Clinical Specialties', sub: 'General to Sub-specialties' },
];

const specialties = props.landingContent?.specialties && props.landingContent.specialties.length ? props.landingContent.specialties : [
    'Cardiology', 'Pediatrics', 'Gynecology & OBS', 'General Medicine',
    'Orthopedics', 'Dermatology', 'Neurology', 'ENT', 'Gastroenterology',
    'Psychiatry', 'Ophthalmology', 'Pulmonology', 'Endocrinology', 'Urology'
];
</script>

<template>
    <Head :title="lc.meta_title" />

    <div class="min-h-screen bg-slate-50 text-slate-800 font-sans selection:bg-teal-600 selection:text-white relative overflow-x-hidden">
        
        <!-- Background Ambient Mesh Gradients -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[650px] pointer-events-none overflow-hidden z-0">
            <div class="absolute top-[-100px] left-1/4 w-[500px] h-[500px] bg-teal-200/40 rounded-full blur-[140px]"></div>
            <div class="absolute top-[100px] right-1/4 w-[600px] h-[600px] bg-sky-200/40 rounded-full blur-[160px]"></div>
            <div class="absolute top-[300px] left-1/3 w-[400px] h-[400px] bg-indigo-200/30 rounded-full blur-[130px]"></div>
        </div>

        <!-- Sticky Header Navigation -->
        <header class="sticky top-0 z-50 backdrop-blur-xl bg-white/85 border-b border-slate-200/80 transition-all shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                
                <!-- Logo & Brand -->
                <Link :href="route('dashboard')" class="flex items-center gap-3 group">
                    <!-- Custom Image Logo if uploaded -->
                    <template v-if="lc.brand_logo_url">
                        <img :src="lc.brand_logo_url" :alt="lc.brand_name" class="h-10 sm:h-11 w-auto max-w-[160px] object-contain group-hover:scale-105 transition-transform duration-300" />
                    </template>
                    <!-- Default SVG Icon Logo -->
                    <template v-else>
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-teal-600 via-teal-500 to-cyan-500 p-0.5 shadow-md shadow-teal-600/20 group-hover:scale-105 transition-transform duration-300">
                            <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center">
                                <Stethoscope class="w-6 h-6 text-teal-600 group-hover:rotate-6 transition-transform" />
                            </div>
                        </div>
                    </template>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xl font-bold tracking-tight text-slate-900">{{ lc.brand_name }}</span>
                            <span v-if="lc.brand_name_highlight" class="text-xs font-black tracking-wider uppercase px-2 py-0.5 rounded-md bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-mono shadow-xs">{{ lc.brand_name_highlight }}</span>
                        </div>
                        <p v-if="lc.brand_tagline" class="text-[10px] font-semibold text-teal-700 tracking-wider uppercase -mt-0.5">{{ lc.brand_tagline }}</p>
                    </div>
                </Link>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                    <a href="#features" class="hover:text-teal-600 transition-colors">Features</a>
                    <a href="#live-demo" class="hover:text-teal-600 transition-colors flex items-center gap-1.5">
                        <Sparkles class="w-3.5 h-3.5 text-teal-600" />
                        Interactive Rx
                    </a>
                    <a href="#specialties" class="hover:text-teal-600 transition-colors">Specialties</a>
                    <a href="#pricing" class="hover:text-teal-600 transition-colors">Pricing</a>
                    <a href="#faq" class="hover:text-teal-600 transition-colors">FAQ</a>
                </nav>

                <!-- Auth / Action Buttons -->
                <div class="flex items-center gap-3">
                    <template v-if="$page.props.auth?.user">
                        <Link
                            :href="route('dashboard')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold text-sm shadow-md shadow-teal-600/20 hover:shadow-teal-600/30 hover:scale-[1.02] active:scale-[0.98] transition-all"
                        >
                            <Activity class="w-4 h-4" />
                            Go to Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="px-4 py-2 text-sm font-semibold text-slate-700 hover:text-teal-600 hover:bg-slate-100 rounded-xl transition-all"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold text-sm shadow-md shadow-teal-600/25 hover:shadow-teal-600/35 hover:scale-[1.02] active:scale-[0.98] transition-all"
                        >
                            <span>Get Started</span>
                            <ArrowRight class="w-4 h-4" />
                        </Link>
                    </template>
                </div>

            </div>
        </header>

        <!-- Main Hero Section -->
        <section class="relative pt-12 pb-20 lg:pt-20 lg:pb-28 overflow-hidden z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Headline & CTA -->
                <div class="text-center max-w-4xl mx-auto space-y-6">
                    
                    <!-- Notification Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-teal-50 border border-teal-200 text-teal-800 text-xs sm:text-sm font-semibold shadow-xs">
                        <Sparkles class="w-4 h-4 text-teal-600 animate-pulse" />
                        <span>{{ lc.hero_badge }}</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-600"></span>
                        <span class="text-teal-700 hidden sm:inline">{{ lc.hero_version_text }}</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-slate-900 leading-[1.1]">
                        {{ lc.hero_title_line1 }}
                        <span class="bg-gradient-to-r from-teal-600 via-cyan-600 to-indigo-600 bg-clip-text text-transparent block mt-1 sm:mt-2">
                            {{ lc.hero_title_highlight }}
                        </span>
                        {{ lc.hero_title_line2 }}
                    </h1>

                    <!-- Subtitle -->
                    <p class="text-base sm:text-xl text-slate-600 max-w-3xl mx-auto font-normal leading-relaxed">
                        {{ lc.hero_subtitle }}
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                        <Link
                            :href="lc.hero_cta_primary_link || route('register')"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-gradient-to-r from-teal-600 via-cyan-600 to-indigo-600 text-white font-bold text-base shadow-xl shadow-teal-600/25 hover:shadow-teal-600/35 hover:scale-[1.02] active:scale-[0.98] transition-all"
                        >
                            <Zap class="w-5 h-5 fill-current" />
                            <span>{{ lc.hero_cta_primary_text }}</span>
                            <ArrowRight class="w-5 h-5" />
                        </Link>
                        <a
                            :href="lc.hero_cta_secondary_link || '#live-demo'"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-4 rounded-2xl bg-white border border-slate-300 hover:border-slate-400 text-slate-700 font-semibold text-base hover:bg-slate-50 transition-all shadow-sm"
                        >
                            <FileText class="w-5 h-5 text-teal-600" />
                            <span>{{ lc.hero_cta_secondary_text }}</span>
                        </a>
                    </div>

                    <!-- Trust Points -->
                    <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10 pt-4 text-xs sm:text-sm text-slate-500 font-medium">
                        <div class="flex items-center gap-2">
                            <CheckCircle2 class="w-4 h-4 text-teal-600" />
                            <span>{{ lc.trust_point_1 }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <ShieldCheck class="w-4 h-4 text-teal-600" />
                            <span>{{ lc.trust_point_2 }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <Star class="w-4 h-4 text-amber-500 fill-amber-500" />
                            <span>{{ lc.trust_point_3 }}</span>
                        </div>
                    </div>

                </div>

                <!-- Hero Interactive App Snapshot Mockup / Uploaded Custom Hero Image -->
                <div class="mt-14 relative max-w-5xl mx-auto">
                    
                    <!-- Decorative border glow -->
                    <div class="absolute -inset-1 rounded-3xl bg-gradient-to-r from-teal-400/30 via-cyan-400/30 to-indigo-400/30 blur-xl opacity-70"></div>

                    <!-- Custom Uploaded Hero Image if present -->
                    <div v-if="lc.hero_image_url" class="relative rounded-2xl bg-white border border-slate-200/90 shadow-2xl overflow-hidden">
                        <img :src="lc.hero_image_url" alt="Doc Helper AI Platform Preview" class="w-full h-auto object-cover rounded-2xl" />
                    </div>

                    <!-- Default High-Fidelity Interactive Mockup -->
                    <div v-else class="relative rounded-2xl bg-white border border-slate-200/90 shadow-2xl overflow-hidden">
                        
                        <!-- Mockup Top Window Bar -->
                        <div class="px-5 py-3.5 bg-slate-100/90 border-b border-slate-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                <span class="ml-2 text-xs font-mono text-slate-600 font-medium">Doc Helper AI — Clinical Prescription Pad v2.4</span>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-mono">
                                <span class="px-2.5 py-1 rounded bg-teal-50 border border-teal-200 text-teal-800 font-semibold flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-teal-600 animate-ping"></span>
                                    Live Practice Active
                                </span>
                            </div>
                        </div>

                        <!-- Mockup Inner Content -->
                        <div class="p-6 sm:p-8 bg-slate-50/70">
                            
                            <!-- Doctor Header Preview inside mockup -->
                            <div class="p-5 rounded-xl bg-white border border-slate-200/90 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-600 to-cyan-600 flex items-center justify-center text-white font-black text-xl shadow-md">
                                        DH
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-bold text-slate-900">Dr. Daren Hirthe Jr.</h3>
                                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-teal-100 text-teal-800 border border-teal-200">Verified Doctor</span>
                                        </div>
                                        <p class="text-xs text-teal-700 font-semibold">MD (Harvard), FACC (Cardiology), Board Certified Physician</p>
                                        <p class="text-xs text-slate-500 mt-0.5">Reg. No: <span class="text-slate-800 font-mono font-bold">84920</span> • Senior Consultant Cardiologist</p>
                                    </div>
                                </div>
                                <div class="text-left md:text-right border-t md:border-t-0 pt-3 md:pt-0 border-slate-200">
                                    <div class="text-xs font-bold text-slate-800">Evercare Medical Hospital</div>
                                    <div class="text-xs text-slate-500">Room 402 • 05:00 PM - 09:00 PM</div>
                                    <div class="text-[11px] text-teal-700 font-bold mt-1 font-mono">Serial Queue: #08 (Active)</div>
                                </div>
                            </div>

                            <!-- Patient Vitals Bar inside mockup -->
                            <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2.5">
                                <div class="p-3 rounded-lg bg-white border border-slate-200/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Patient</span>
                                    <div class="text-sm font-bold text-slate-900 mt-0.5">Sarah Jenkins</div>
                                    <span class="text-[10px] text-slate-500">38 Yrs • Female</span>
                                </div>
                                <div class="p-3 rounded-lg bg-white border border-slate-200/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Blood Pressure</span>
                                    <div class="text-sm font-bold text-emerald-700 mt-0.5">120/80 <span class="text-[10px] text-slate-500 font-normal">mmHg</span></div>
                                    <span class="text-[10px] text-emerald-600 font-medium">Normal Range</span>
                                </div>
                                <div class="p-3 rounded-lg bg-white border border-slate-200/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Pulse / HR</span>
                                    <div class="text-sm font-bold text-cyan-700 mt-0.5">74 <span class="text-[10px] text-slate-500 font-normal">bpm</span></div>
                                    <span class="text-[10px] text-slate-500">Regular Rhythm</span>
                                </div>
                                <div class="p-3 rounded-lg bg-white border border-slate-200/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Weight & BMI</span>
                                    <div class="text-sm font-bold text-slate-900 mt-0.5">64 kg <span class="text-[10px] text-slate-500 font-normal">(22.4)</span></div>
                                    <span class="text-[10px] text-teal-700 font-medium">Healthy</span>
                                </div>
                                <div class="p-3 rounded-lg bg-white border border-slate-200/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">SpO2</span>
                                    <div class="text-sm font-bold text-emerald-700 mt-0.5">99% <span class="text-[10px] text-slate-500 font-normal">Room Air</span></div>
                                    <span class="text-[10px] text-emerald-600 font-medium">Optimal</span>
                                </div>
                                <div class="p-3 rounded-lg bg-white border border-slate-200/80 shadow-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Temperature</span>
                                    <div class="text-sm font-bold text-slate-900 mt-0.5">98.4°F</div>
                                    <span class="text-[10px] text-slate-500">Afebrile</span>
                                </div>
                            </div>

                            <!-- Dual-Column Rx Pad inside mockup -->
                            <div class="mt-4 grid grid-cols-1 lg:grid-cols-12 gap-4">
                                
                                <!-- Left Column: Clinical Notes -->
                                <div class="lg:col-span-4 p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-4">
                                    <div>
                                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                                            <span>Chief Complaints (C/C)</span>
                                            <span class="text-[10px] text-teal-700 font-mono font-bold">2 Added</span>
                                        </div>
                                        <div class="space-y-1.5">
                                            <div class="px-2.5 py-1.5 rounded-md bg-slate-50 border border-slate-200 text-xs text-slate-700">
                                                • Chest tightness on climbing stairs (3 days)
                                            </div>
                                            <div class="px-2.5 py-1.5 rounded-md bg-slate-50 border border-slate-200 text-xs text-slate-700">
                                                • Shortness of breath on exertion
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                                            <span>Diagnosis (Dx)</span>
                                            <span class="text-[10px] text-indigo-700 font-mono font-bold">ICD-10</span>
                                        </div>
                                        <div class="px-2.5 py-1.5 rounded-md bg-indigo-50 border border-indigo-200 text-xs text-indigo-900 font-semibold">
                                            ✦ Unstable Angina Pectoris (I20.0)
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                            <span>Investigations (Ix)</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200 text-[11px] text-slate-700 font-medium">ECG (12-Lead)</span>
                                            <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200 text-[11px] text-slate-700 font-medium">2D Echocardiogram</span>
                                            <span class="px-2 py-1 rounded bg-slate-100 border border-slate-200 text-[11px] text-slate-700 font-medium">Serum Troponin-I</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Rx Medication List -->
                                <div class="lg:col-span-8 p-4 rounded-xl bg-white border border-slate-200/80 shadow-xs space-y-3">
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl font-black text-teal-600 font-serif italic">Rx</span>
                                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Prescribed Medications</span>
                                        </div>
                                        <span class="text-xs text-slate-500 font-mono">3 Items</span>
                                    </div>

                                    <div class="space-y-2.5">
                                        
                                        <!-- Medication Item 1 -->
                                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200/90 flex items-center justify-between">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-teal-700 font-mono">1. Tab.</span>
                                                    <span class="text-sm font-bold text-slate-900">Rosuvastatin 20mg</span>
                                                    <span class="text-[10px] text-slate-500">(Lipid Lowering)</span>
                                                </div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="px-2 py-0.5 rounded bg-teal-100 border border-teal-200 text-teal-900 text-xs font-mono font-bold">0+0+1</span>
                                                    <span class="text-xs text-slate-600">• At Bedtime</span>
                                                    <span class="text-xs text-slate-600">• Continue for 30 Days</span>
                                                </div>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-md bg-emerald-100 border border-emerald-200 text-emerald-800 text-[11px] font-semibold hidden sm:inline">Safe</span>
                                        </div>

                                        <!-- Medication Item 2 -->
                                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200/90 flex items-center justify-between">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-teal-700 font-mono">2. Tab.</span>
                                                    <span class="text-sm font-bold text-slate-900">Clopidogrel + Aspirin (75mg + 75mg)</span>
                                                </div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="px-2 py-0.5 rounded bg-teal-100 border border-teal-200 text-teal-900 text-xs font-mono font-bold">1+0+0</span>
                                                    <span class="text-xs text-slate-600">• After Breakfast</span>
                                                    <span class="text-xs text-slate-600">• Continue for 30 Days</span>
                                                </div>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-md bg-emerald-100 border border-emerald-200 text-emerald-800 text-[11px] font-semibold hidden sm:inline">Safe</span>
                                        </div>

                                        <!-- Medication Item 3 -->
                                        <div class="p-3 rounded-lg bg-slate-50 border border-slate-200/90 flex items-center justify-between">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-teal-700 font-mono">3. Cap.</span>
                                                    <span class="text-sm font-bold text-slate-900">Pantoprazole 40mg</span>
                                                </div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <span class="px-2 py-0.5 rounded bg-teal-100 border border-teal-200 text-teal-900 text-xs font-mono font-bold">1+0+1</span>
                                                    <span class="text-xs text-slate-600">• 30 mins Before Meal</span>
                                                    <span class="text-xs text-slate-600">• 14 Days</span>
                                                </div>
                                            </div>
                                            <span class="px-2.5 py-1 rounded-md bg-emerald-100 border border-emerald-200 text-emerald-800 text-[11px] font-semibold hidden sm:inline">Safe</span>
                                        </div>

                                    </div>

                                    <!-- Bottom Action Strip in Mockup -->
                                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <Clock class="w-3.5 h-3.5 text-teal-600" />
                                            <span>Next Visit: <strong class="text-slate-800">14 Days Later</strong></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-3 py-1.5 rounded-lg bg-teal-600 text-white font-bold flex items-center gap-1.5 shadow-sm">
                                                <Printer class="w-3.5 h-3.5" />
                                                1-Click Print Ready
                                            </span>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>
        </section>

        <!-- Stats Counter Section -->
        <section class="py-12 border-y border-slate-200 bg-white relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                    <div v-for="(stat, idx) in stats" :key="idx" class="space-y-1">
                        <div class="text-3xl sm:text-5xl font-black bg-gradient-to-r from-teal-600 to-cyan-600 bg-clip-text text-transparent font-mono">
                            {{ stat.value }}
                        </div>
                        <div class="text-sm font-bold text-slate-900">{{ stat.label }}</div>
                        <div class="text-xs text-slate-500 font-medium">{{ stat.sub }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Core Features Grid -->
        <section id="features" class="py-24 relative z-10 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
                
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-800 text-xs font-bold uppercase tracking-wider">
                        {{ lc.features_badge }}
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">
                        {{ lc.features_title }}
                    </h2>
                    <p class="text-base sm:text-lg text-slate-600">
                        {{ lc.features_subtitle }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    <div
                        v-for="(feat, fIdx) in featureList"
                        :key="fIdx"
                        class="p-8 rounded-3xl bg-white border border-slate-200/90 hover:border-teal-400 hover:shadow-xl hover:shadow-teal-600/5 transition-all duration-300 group hover:-translate-y-1 shadow-sm"
                    >
                        <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-center text-teal-600 group-hover:scale-110 group-hover:bg-teal-600 group-hover:text-white transition-all duration-300 mb-6 shadow-xs">
                            <Sparkles class="w-7 h-7" />
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">{{ feat.title }}</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            {{ feat.desc }}
                        </p>
                    </div>
                </div>

            </div>
        </section>

        <!-- Live Interactive Prescription Sandbox Section -->
        <section id="live-demo" class="py-24 border-y border-slate-200 bg-white relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                
                <div class="text-center max-w-3xl mx-auto space-y-3">
                    <span class="px-3 py-1 rounded-full bg-teal-50 border border-teal-200 text-teal-800 text-xs font-bold uppercase tracking-wider">
                        Interactive Sandbox
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                        Try Adding Medications in Real-Time
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600">
                        Experience how fast and effortless Doc Helper AI organizes your clinical prescriptions.
                    </p>
                </div>

                <!-- Interactive Card -->
                <div class="max-w-4xl mx-auto rounded-3xl bg-slate-50 border border-slate-200/90 shadow-xl p-6 sm:p-8 space-y-6">
                    
                    <!-- Quick Add Drug Bar -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                            <input
                                v-model="newDrugName"
                                @keyup.enter="addDemoDrug"
                                type="text"
                                placeholder="Type a medicine name (e.g. Cefuroxime 500mg, Montelukast 10mg)..."
                                class="w-full pl-10 pr-4 py-3 rounded-xl bg-white border border-slate-300 text-slate-900 placeholder-slate-400 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition shadow-xs"
                            />
                        </div>
                        <button
                            @click="addDemoDrug"
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-bold text-sm hover:opacity-95 flex items-center justify-center gap-2 shadow-md shadow-teal-600/20"
                        >
                            <Plus class="w-4 h-4" />
                            <span>Add to Rx</span>
                        </button>
                    </div>

                    <!-- Quick Suggestion Badges -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-500 font-semibold">Quick Suggestions:</span>
                        <button
                            @click="newDrugName = 'Amoxicillin + Clavulanic Acid 625mg'; addDemoDrug()"
                            class="px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-700 hover:text-teal-700 hover:border-teal-400 hover:bg-teal-50/50 transition font-medium shadow-xs"
                        >
                            + Amoxicillin 625mg
                        </button>
                        <button
                            @click="newDrugName = 'Atorvastatin 20mg'; addDemoDrug()"
                            class="px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-700 hover:text-teal-700 hover:border-teal-400 hover:bg-teal-50/50 transition font-medium shadow-xs"
                        >
                            + Atorvastatin 20mg
                        </button>
                        <button
                            @click="newDrugName = 'Metformin 500mg'; addDemoDrug()"
                            class="px-2.5 py-1 rounded-md bg-white border border-slate-200 text-slate-700 hover:text-teal-700 hover:border-teal-400 hover:bg-teal-50/50 transition font-medium shadow-xs"
                        >
                            + Metformin 500mg
                        </button>
                    </div>

                    <!-- Live Drug List -->
                    <div class="space-y-3 pt-2">
                        <div class="text-xs font-bold text-teal-800 uppercase tracking-wider flex items-center justify-between pb-2 border-b border-slate-200">
                            <span class="flex items-center gap-1.5 font-serif italic text-base text-teal-600">
                                Rx <span class="font-sans not-italic text-xs text-slate-700 font-bold">Active Medication List</span>
                            </span>
                            <span class="text-slate-500 font-mono">{{ demoDrugs.length }} Prescribed</span>
                        </div>

                        <div v-if="demoDrugs.length === 0" class="text-center py-8 text-slate-400 text-sm">
                            No medications added yet. Type a medicine above or click a suggestion!
                        </div>

                        <div
                            v-for="(drug, index) in demoDrugs"
                            :key="index"
                            class="p-4 rounded-xl bg-white border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 group hover:border-slate-300 hover:shadow-xs transition"
                        >
                            <div class="flex items-start sm:items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-teal-100 text-teal-800 text-xs font-bold flex items-center justify-center shrink-0">
                                    {{ index + 1 }}
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-teal-700">{{ drug.type }}</span>
                                        <span class="text-sm font-bold text-slate-900">{{ drug.name }}</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs text-slate-500">
                                        <span class="px-2 py-0.5 rounded bg-teal-50 border border-teal-200 text-teal-800 font-mono font-bold">{{ drug.dosage }}</span>
                                        <span>• {{ drug.advice }}</span>
                                        <span>• {{ drug.duration }}</span>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="removeDemoDrug(index)"
                                class="self-end sm:self-center px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition"
                            >
                                Remove
                            </button>
                        </div>

                    </div>

                    <!-- Bottom Sandbox CTA -->
                    <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <CheckCircle2 class="w-5 h-5 text-teal-600 shrink-0" />
                            <span class="text-xs sm:text-sm text-slate-800 font-medium">
                                Ready to experience the full AI drug lookup database and print templates?
                            </span>
                        </div>
                        <Link
                            :href="route('register')"
                            class="px-5 py-2.5 rounded-xl bg-teal-600 text-white font-bold text-xs hover:bg-teal-700 text-center shrink-0 transition shadow-xs"
                        >
                            Create Doctor Account Free →
                        </Link>
                    </div>

                </div>

            </div>
        </section>

        <!-- Supported Specialties Section -->
        <section id="specialties" class="py-20 relative z-10 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900">
                        {{ lc.specialties_title }}
                    </h2>
                    <p class="text-sm text-slate-600">
                        {{ lc.specialties_subtitle }}
                    </p>
                </div>

                <div class="flex flex-wrap justify-center gap-3 max-w-4xl mx-auto">
                    <div
                        v-for="(spec, idx) in specialties"
                        :key="idx"
                        class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-sm font-medium text-slate-700 hover:text-teal-700 hover:border-teal-400 hover:bg-teal-50/50 transition cursor-default flex items-center gap-2 shadow-xs"
                    >
                        <HeartPulse class="w-3.5 h-3.5 text-teal-600" />
                        <span>{{ spec }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- SaaS Pricing Section -->
        <section id="pricing" class="py-24 border-t border-slate-200 bg-white relative z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
                
                <div class="text-center max-w-3xl mx-auto space-y-4">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-teal-50 border border-teal-200 text-teal-800 text-xs font-bold uppercase tracking-wider">
                        Simple, Transparent SaaS Pricing
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">
                        Choose the Plan Built for Your Practice
                    </h2>
                    <p class="text-base text-slate-600">
                        No hidden fees. Upgrade, downgrade, or cancel anytime. CodeCanyon Lifetime license available for self-hosting.
                    </p>

                    <!-- Billing Toggle -->
                    <div class="inline-flex items-center p-1.5 rounded-2xl bg-slate-100 border border-slate-200 mt-4">
                        <button
                            @click="billingPeriod = 'monthly'"
                            :class="[
                                'px-5 py-2 rounded-xl text-xs font-bold transition-all',
                                billingPeriod === 'monthly'
                                    ? 'bg-white text-slate-900 shadow-sm'
                                    : 'text-slate-600 hover:text-slate-900'
                            ]"
                        >
                            Monthly Billing
                        </button>
                        <button
                            @click="billingPeriod = 'yearly'"
                            :class="[
                                'px-5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2',
                                billingPeriod === 'yearly'
                                    ? 'bg-gradient-to-r from-teal-600 to-cyan-600 text-white shadow-sm'
                                    : 'text-slate-600 hover:text-slate-900'
                            ]"
                        >
                            <span>Annual Billing</span>
                            <span v-if="annualDiscountBadge" class="px-2 py-0.5 rounded-full text-[10px] uppercase font-black tracking-wider bg-teal-100 text-teal-900">
                                {{ annualDiscountBadge }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Pricing Cards Grid (Dynamic from Database / Super Admin) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto items-stretch">
                    <div
                        v-for="plan in (pricingPlans && pricingPlans.length ? pricingPlans : [
                            {
                                name: 'Solo Physician',
                                tagline: 'Essential digital prescription pad for independent private practitioners.',
                                currency: '$',
                                monthly_price: 25,
                                annual_price: 228,
                                annual_discount_text: 'Billed annually ($228/yr)',
                                is_featured: false,
                                button_text: 'Start 14-Day Free Trial',
                                button_link: '/register',
                                features: [
                                    'Unlimited Digital Prescriptions',
                                    'Single Chamber & Schedule',
                                    'Standard Print & PDF Layouts',
                                    'Doctor Profile & Degree Customizer',
                                    'Basic Drug & Lookup Database',
                                ]
                            },
                            {
                                name: 'Professional Practice',
                                tagline: 'Complete multi-chamber and AI-powered practice system.',
                                currency: '$',
                                monthly_price: 49,
                                annual_price: 468,
                                annual_discount_text: 'Billed annually ($468/yr)',
                                badge_text: '★ Most Popular for Clinics',
                                is_featured: true,
                                button_text: 'Get Started with Pro Practice',
                                button_link: '/register',
                                features: [
                                    'Everything in Solo Physician',
                                    'Unlimited Multi-Chamber Visiting Slots',
                                    'AI Drug Interaction & Allergy Checker',
                                    'AI Clinical Chat Copilot',
                                    'Live Patient Queue & Vitals Tracking',
                                    'Patient Portal & Account Access',
                                    'Custom Digital Seal & Signature',
                                    'Full Clinical & Drug Lookup Manager',
                                ]
                            },
                            {
                                name: 'Hospital & Enterprise',
                                tagline: 'For multi-doctor clinics, hospitals, or CodeCanyon full SaaS deployment.',
                                currency: '$',
                                monthly_price: 129,
                                annual_price: 999,
                                annual_discount_text: 'Or Lifetime CodeCanyon License',
                                is_featured: false,
                                button_text: 'Contact Enterprise Team',
                                button_link: '/register',
                                features: [
                                    'Unlimited Doctors & Staff',
                                    'Multi-Branch Clinic Management',
                                    'Centralized Super Admin Control',
                                    'Custom Domain & White-Labeling',
                                    'HL7 / FHIR Medical Data Export',
                                    'Dedicated 24/7 SLA & Setup Support',
                                ]
                            }
                        ])"
                        :key="plan.id || plan.name"
                        class="rounded-3xl p-8 flex flex-col justify-between space-y-8 transition relative"
                        :class="[
                            plan.is_featured
                                ? 'bg-white border-2 border-teal-600 shadow-xl shadow-teal-600/10 hover:scale-[1.02]'
                                : 'bg-slate-50 border border-slate-200 hover:border-slate-300 hover:shadow-lg'
                        ]"
                    >
                        <!-- Most Popular / Custom Badge -->
                        <div
                            v-if="plan.badge_text"
                            class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-gradient-to-r from-teal-600 to-cyan-600 text-white text-[11px] font-black uppercase tracking-wider shadow-md"
                        >
                            {{ plan.badge_text }}
                        </div>

                        <div class="space-y-4">
                            <div class="text-lg font-bold text-slate-900 flex items-center justify-between">
                                <span>{{ plan.name }}</span>
                                <Sparkles v-if="plan.is_featured" class="w-5 h-5 text-teal-600" />
                            </div>
                            <p class="text-xs text-slate-600 min-h-[32px]">{{ plan.tagline }}</p>
                            
                            <div class="pt-2">
                                <div class="text-4xl font-black text-slate-900 font-mono">
                                    {{ plan.currency || '$' }}{{ billingPeriod === 'yearly' ? Math.round(plan.annual_price / 12) : plan.monthly_price }}
                                    <span class="text-sm font-normal text-slate-500 font-sans">/ doctor / month</span>
                                </div>
                                <div class="text-xs text-teal-700 font-mono font-semibold mt-1">
                                    {{ billingPeriod === 'yearly' ? (plan.annual_discount_text || `Billed annually (${plan.currency || '$'}${plan.annual_price}/yr)`) : 'Billed monthly' }}
                                </div>
                            </div>

                            <ul class="space-y-3 text-xs text-slate-800 pt-4 border-t border-slate-200">
                                <li
                                    v-for="(feat, fIdx) in plan.features"
                                    :key="fIdx"
                                    class="flex items-center gap-2.5"
                                >
                                    <Check class="w-4 h-4 text-teal-600 shrink-0" />
                                    <span>{{ feat }}</span>
                                </li>
                            </ul>
                        </div>

                        <Link
                            :href="getPlanButtonLink(plan)"
                            class="w-full py-3.5 rounded-xl font-bold text-xs text-center transition block shadow-xs"
                            :class="[
                                plan.is_featured
                                    ? 'bg-gradient-to-r from-teal-600 to-cyan-600 text-white shadow-lg shadow-teal-600/25 hover:shadow-teal-600/35 hover:scale-[1.01]'
                                    : 'bg-white hover:bg-slate-100 text-slate-900 border border-slate-300'
                            ]"
                        >
                            {{ plan.button_text || 'Start 14-Day Free Trial' }}
                        </Link>
                    </div>
                </div>

            </div>
        </section>

        <!-- Testimonials / Doctor Reviews -->
        <section class="py-20 relative z-10 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900">
                        {{ lc.testimonials_title }}
                    </h2>
                    <p class="text-sm text-slate-600">
                        {{ lc.testimonials_subtitle }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="(test, tIdx) in testimonialsList"
                        :key="tIdx"
                        class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-4"
                    >
                        <div class="flex items-center gap-1 text-amber-500">
                            <Star v-for="i in (test.rating || 5)" :key="i" class="w-4 h-4 fill-amber-500" />
                        </div>
                        <p class="text-sm text-slate-700 italic">
                            "{{ test.quote }}"
                        </p>
                        <div class="pt-2 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-teal-100 text-teal-800 font-bold flex items-center justify-center text-sm">
                                {{ test.author ? test.author.substring(0, 2).toUpperCase() : 'DR' }}
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">{{ test.author }}</div>
                                <div class="text-[11px] text-teal-700 font-medium">{{ test.title }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Accordion Section -->
        <section id="faq" class="py-20 border-t border-slate-200 bg-white relative z-10">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                
                <div class="text-center space-y-2">
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900">
                        {{ lc.faqs_title }}
                    </h2>
                    <p class="text-sm text-slate-600">
                        {{ lc.faqs_subtitle }}
                    </p>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="(faq, idx) in faqs"
                        :key="idx"
                        class="rounded-2xl bg-slate-50 border border-slate-200 overflow-hidden transition shadow-xs"
                    >
                        <button
                            @click="toggleFaq(idx)"
                            class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 text-sm sm:text-base font-bold text-slate-900 hover:text-teal-700 transition"
                        >
                            <span>{{ faq.q }}</span>
                            <ChevronDown
                                :class="[
                                    'w-5 h-5 text-teal-600 transition-transform duration-200 shrink-0',
                                    activeFaq === idx ? 'rotate-180' : ''
                                ]"
                            />
                        </button>
                        <div
                            v-show="activeFaq === idx"
                            class="px-6 pb-5 text-xs sm:text-sm text-slate-600 leading-relaxed border-t border-slate-200 pt-3"
                        >
                            {{ faq.a }}
                        </div>
                    </div>
                </div>

            </div>
        </section>

                <!-- Contact & Support Helpdesk Section -->
        <section id="contact" class="py-24 bg-white relative z-10 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
                    <span class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-teal-50 text-teal-700 border border-teal-200">
                        Direct Communication & Sales
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Get in Touch with Our Executive Team
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        Have questions about pricing packages, custom hospital deployments, or AI copilot integration? Send us a message or contact our official hotline.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                    
                    <!-- Left: Official Contact Info Card -->
                    <div class="lg:col-span-5 bg-gradient-to-br from-[#0A0D36] via-slate-900 to-teal-950 text-white p-8 sm:p-10 rounded-3xl shadow-2xl space-y-8 relative overflow-hidden">
                        
                        <div class="space-y-2">
                            <h3 class="text-xl font-black text-white tracking-tight">Official Support Channels</h3>
                            <p class="text-xs text-slate-300 leading-relaxed font-normal">
                                Our dedicated healthcare customer success managers are ready to assist you during business hours.
                            </p>
                        </div>

                        <div class="space-y-5">
                            <!-- Email -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white/10 text-teal-300 flex items-center justify-center shrink-0">
                                    <Mail class="w-5 h-5" />
                                </div>
                                <div class="space-y-0.5">
                                    <span class="text-[11px] font-bold uppercase text-slate-400">Email Address</span>
                                    <a :href="'mailto:' + supportContacts.email" class="text-sm font-bold text-white hover:text-teal-300 transition block">
                                        {{ supportContacts.email }}
                                    </a>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white/10 text-teal-300 flex items-center justify-center shrink-0">
                                    <Phone class="w-5 h-5" />
                                </div>
                                <div class="space-y-0.5">
                                    <span class="text-[11px] font-bold uppercase text-slate-400">Helpline / Phone</span>
                                    <a :href="'tel:' + supportContacts.phone" class="text-sm font-bold text-white hover:text-teal-300 transition block">
                                        {{ supportContacts.phone }}
                                    </a>
                                </div>
                            </div>

                            <!-- WhatsApp -->
                            <div class="flex items-start gap-4" v-if="supportContacts.whatsapp">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                    <MessageCircle class="w-5 h-5" />
                                </div>
                                <div class="space-y-0.5">
                                    <span class="text-[11px] font-bold uppercase text-slate-400">WhatsApp Live Chat</span>
                                    <a :href="'https://wa.me/' + supportContacts.whatsapp.replace(/[^0-9]/g, '')" target="_blank" class="text-sm font-bold text-emerald-300 hover:underline transition block">
                                        {{ supportContacts.whatsapp }}
                                    </a>
                                </div>
                            </div>

                            <!-- Hours -->
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white/10 text-teal-300 flex items-center justify-center shrink-0">
                                    <Clock class="w-5 h-5" />
                                </div>
                                <div class="space-y-0.5">
                                    <span class="text-[11px] font-bold uppercase text-slate-400">Support Working Hours</span>
                                    <p class="text-xs font-semibold text-slate-200">
                                        {{ supportContacts.hours }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-white/5 border border-white/10 text-[11px] text-teal-200/90 leading-relaxed">
                            💡 Are you a registered doctor with an active clinic? You can also open real-time priority support tickets directly from your Doctor Dashboard.
                        </div>

                    </div>

                    <!-- Right: Interactive Contact & Inquiry Form -->
                    <div class="lg:col-span-7 bg-slate-50 p-8 sm:p-10 rounded-3xl border border-slate-200/80 shadow-xs">
                        
                        <div v-if="isSubmitted" class="p-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 space-y-2 mb-6 animate-in fade-in">
                            <div class="flex items-center gap-2 font-bold">
                                <CheckCircle2 class="w-5 h-5 text-emerald-600" />
                                <span>Inquiry Sent Successfully!</span>
                            </div>
                            <p class="text-xs text-emerald-700">
                                Thank you for reaching out. Our support team has received your message and will reply via email or phone shortly.
                            </p>
                        </div>

                        <form @submit.prevent="submitContact" class="space-y-4">
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Your Full Name <span class="text-teal-600">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="contactForm.name"
                                        required
                                        placeholder="e.g. Dr. Sarah Jenkins"
                                        class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:border-teal-600 focus:outline-none"
                                    />
                                    <p v-if="contactForm.errors.name" class="text-xs text-rose-500 mt-1">{{ contactForm.errors.name }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Email Address <span class="text-teal-600">*</span>
                                    </label>
                                    <input
                                        type="email"
                                        v-model="contactForm.email"
                                        required
                                        placeholder="e.g. doctor@clinic.com"
                                        class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-mono focus:border-teal-600 focus:outline-none"
                                    />
                                    <p v-if="contactForm.errors.email" class="text-xs text-rose-500 mt-1">{{ contactForm.errors.email }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Phone / Mobile Number
                                    </label>
                                    <input
                                        type="tel"
                                        v-model="contactForm.phone"
                                        placeholder="e.g. +1 (555) 234-5678"
                                        class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:border-teal-600 focus:outline-none"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                        Inquiry Type <span class="text-teal-600">*</span>
                                    </label>
                                    <select
                                        v-model="contactForm.inquiry_type"
                                        required
                                        class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-bold focus:border-teal-600 focus:outline-none"
                                    >
                                        <option value="sales_demo">Schedule Live Demo / Sales</option>
                                        <option value="enterprise">Hospital / Custom Deployment</option>
                                        <option value="billing">Pricing & Subscription Questions</option>
                                        <option value="support">Technical Support</option>
                                        <option value="general">General Feedback / Inquiries</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Subject
                                </label>
                                <input
                                    type="text"
                                    v-model="contactForm.subject"
                                    placeholder="Brief subject of your message"
                                    class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:border-teal-600 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    How can we help you? <span class="text-teal-600">*</span>
                                </label>
                                <textarea
                                    v-model="contactForm.message"
                                    rows="4"
                                    required
                                    placeholder="Tell us about your clinic practice, number of chambers, or specific questions..."
                                    class="w-full px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs focus:border-teal-600 focus:outline-none"
                                ></textarea>
                                <p v-if="contactForm.errors.message" class="text-xs text-rose-500 mt-1">{{ contactForm.errors.message }}</p>
                            </div>

                            <button
                                type="submit"
                                :disabled="contactForm.processing"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-black shadow-lg shadow-teal-600/20 transition cursor-pointer"
                            >
                                <Send class="w-4 h-4" />
                                <span>{{ contactForm.processing ? 'Sending Inquiry...' : 'Submit Contact Message' }}</span>
                            </button>

                        </form>

                    </div>

                </div>

            </div>
        </section>
        <!-- Bottom Call to Action Section -->
        <section class="py-24 relative z-10 overflow-hidden bg-slate-50 border-t border-slate-200">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="p-10 sm:p-16 rounded-3xl bg-gradient-to-br from-teal-600 via-cyan-600 to-indigo-700 shadow-2xl text-white relative space-y-6">
                    
                    <div class="w-16 h-16 rounded-2xl bg-white/20 text-white flex items-center justify-center mx-auto shadow-inner backdrop-blur-xs">
                        <Stethoscope class="w-8 h-8" />
                    </div>

                    <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">
                        {{ lc.bottom_cta_title }}
                    </h2>

                    <p class="text-base sm:text-lg text-teal-50 max-w-2xl mx-auto font-normal">
                        {{ lc.bottom_cta_subtitle }}
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                        <Link
                            :href="route('register')"
                            class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-teal-900 font-black text-base shadow-xl hover:bg-teal-50 hover:scale-105 transition"
                        >
                            {{ lc.bottom_cta_btn_text }}
                        </Link>
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-teal-800/60 border border-white/30 text-white font-bold text-base hover:bg-teal-800 transition backdrop-blur-xs"
                        >
                            Sign In to Portal
                        </Link>
                    </div>

                    <p class="text-xs text-teal-100/90 pt-2 font-medium">
                        {{ lc.bottom_cta_note }}
                    </p>

                </div>
            </div>
        </section>

        <!-- Ultra-Modern Medical SaaS Footer -->
        <footer class="border-t border-slate-800/80 bg-slate-950 text-slate-400 py-16 text-xs relative z-10">
            <!-- Background Glow Effect -->
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full max-w-6xl h-[300px] pointer-events-none overflow-hidden opacity-30">
                <div class="absolute bottom-0 left-1/3 w-[500px] h-[300px] bg-teal-500/10 rounded-full blur-[120px]"></div>
                <div class="absolute bottom-0 right-1/4 w-[400px] h-[250px] bg-cyan-500/10 rounded-full blur-[100px]"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 relative">
                
                <!-- Main Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 lg:gap-8">
                    
                    <!-- Column 1: Brand & Credibility (2 Columns on Large) -->
                    <div class="lg:col-span-2 space-y-5">
                        <div class="flex items-center gap-3">
                            <template v-if="lc.brand_logo_url">
                                <img :src="lc.brand_logo_url" :alt="lc.brand_name" class="h-9 w-auto max-w-[150px] object-contain" />
                            </template>
                            <template v-else>
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-500 to-cyan-500 p-0.5 shadow-lg shadow-teal-500/20">
                                    <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center">
                                        <Stethoscope class="w-5 h-5 text-teal-400" />
                                    </div>
                                </div>
                            </template>
                            <div>
                                <span class="text-xl font-extrabold text-white tracking-tight block">
                                    {{ lc.brand_name }} <span v-if="lc.brand_name_highlight" class="text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-cyan-400 font-black">{{ lc.brand_name_highlight }}</span>
                                </span>
                                <span v-if="lc.brand_tagline" class="text-[10px] font-semibold text-teal-400/90 tracking-wider uppercase block -mt-0.5">{{ lc.brand_tagline }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                            {{ lc.footer_about }}
                        </p>

                        <!-- System Trust & Compliance Badges -->
                        <div class="space-y-2 pt-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-teal-400 text-[11px] font-medium shadow-xs">
                                <ShieldCheck class="w-4 h-4 text-teal-400 shrink-0" />
                                <span>HIPAA Compliant Architecture • 256-Bit SSL Encrypted</span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span class="text-slate-300 font-medium">All Cloud Systems Operational</span>
                                <span class="text-slate-600">•</span>
                                <span class="text-slate-400">99.98% High Availability</span>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: User Portals & Access -->
                    <div class="space-y-3.5">
                        <div class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Users class="w-3.5 h-3.5 text-teal-400" />
                            <span>Portals & Access</span>
                        </div>
                        <ul class="space-y-2.5 text-xs">
                            <li>
                                <Link :href="route('login')" class="hover:text-teal-400 transition-colors flex items-center gap-1.5 group">
                                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500 group-hover:scale-125 transition-transform"></span>
                                    <span>Doctor Practice Login</span>
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('login')" class="hover:text-teal-400 transition-colors flex items-center gap-1.5 group">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 group-hover:scale-125 transition-transform"></span>
                                    <span>Staff Chamber Desk</span>
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('login')" class="hover:text-teal-400 transition-colors flex items-center gap-1.5 group">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 group-hover:scale-125 transition-transform"></span>
                                    <span>Patient Medical Portal</span>
                                </Link>
                            </li>
                            <li>
                                <Link :href="route('register')" class="hover:text-teal-400 transition-colors flex items-center gap-1.5 group">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 group-hover:scale-125 transition-transform"></span>
                                    <span>Start 14-Day Free Trial</span>
                                </Link>
                            </li>
                            <li>
                                <a href="#live-demo" class="hover:text-teal-400 transition-colors flex items-center gap-1.5 group">
                                    <Sparkles class="w-3.5 h-3.5 text-teal-400" />
                                    <span>Try Interactive Rx Pad</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Column 3: Clinical Features & Solutions -->
                    <div class="space-y-3.5">
                        <div class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                            <Stethoscope class="w-3.5 h-3.5 text-teal-400" />
                            <span>Clinical Suite</span>
                        </div>
                        <ul class="space-y-2.5 text-xs">
                            <li><a href="#features" class="hover:text-teal-400 transition-colors">Smart Rx & Dosage Engine</a></li>
                            <li><a href="#features" class="hover:text-teal-400 transition-colors">Multi-Chamber Switcher</a></li>
                            <li><a href="#features" class="hover:text-teal-400 transition-colors">Live Patient Queue & Vitals</a></li>
                            <li><a href="#features" class="hover:text-teal-400 transition-colors">ICD-10 & Diagnosis DB</a></li>
                            <li><a href="#pricing" class="hover:text-teal-400 transition-colors">Pricing & Subscription Plans</a></li>
                            <li><a href="#faq" class="hover:text-teal-400 transition-colors">Frequently Asked Questions</a></li>
                        </ul>
                    </div>

                    <!-- Column 4: Contact, Support & Legal Desk -->
                    <div class="space-y-3.5">
                        <div class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                            <HelpCircle class="w-3.5 h-3.5 text-teal-400" />
                            <span>Support & Helpdesk</span>
                        </div>
                        
                        <!-- Contact Chips -->
                        <div class="space-y-2.5 text-xs">
                            <a 
                                :href="`mailto:${props.supportContacts?.email || 'contact@cannyapps.com'}`" 
                                class="flex items-center gap-2 text-slate-300 hover:text-teal-400 transition-colors group"
                            >
                                <Mail class="w-4 h-4 text-teal-400 shrink-0" />
                                <span class="truncate">{{ props.supportContacts?.email || 'contact@cannyapps.com' }}</span>
                            </a>
                            <a 
                                :href="`tel:${(props.supportContacts?.phone || '+18005550199').replace(/\\s+/g, '')}`" 
                                class="flex items-center gap-2 text-slate-300 hover:text-teal-400 transition-colors group"
                            >
                                <Phone class="w-4 h-4 text-teal-400 shrink-0" />
                                <span>{{ props.supportContacts?.phone || '+1 (800) 555-0199' }}</span>
                            </a>
                        </div>

                        <!-- Legal & Compliance Links with Interactive Modals -->
                        <div class="pt-2 border-t border-slate-800 space-y-1.5">
                            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Legal & Compliance</div>
                            <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs">
                                <button type="button" @click="openLegalModal('privacy')" class="text-slate-400 hover:text-teal-300 transition-colors cursor-pointer text-left">
                                    Privacy Policy
                                </button>
                                <span class="text-slate-700">•</span>
                                <button type="button" @click="openLegalModal('terms')" class="text-slate-400 hover:text-teal-300 transition-colors cursor-pointer text-left">
                                    Terms of Service
                                </button>
                                <span class="text-slate-700">•</span>
                                <button type="button" @click="openLegalModal('disclaimer')" class="text-slate-400 hover:text-teal-300 transition-colors cursor-pointer text-left">
                                    Medical Disclaimer
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Bottom Bar -->
                <div class="pt-8 border-t border-slate-800/90 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-400">
                    <div class="text-center sm:text-left">
                        <p>{{ lc.footer_copyright }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Engineered for high-volume clinical accuracy and HIPAA data standards.</p>
                    </div>
                    
                    <div class="flex items-center gap-4 text-[11px]">
                        <span class="inline-flex items-center gap-1 text-slate-400">
                            <Globe class="w-3.5 h-3.5 text-teal-400" />
                            <span>Global SaaS Edition</span>
                        </span>
                        <span>•</span>
                        <button 
                            type="button" 
                            @click="scrollToTop" 
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 transition-all cursor-pointer"
                        >
                            <span>Back to Top</span>
                            <ArrowUp class="w-3.5 h-3.5 text-teal-400" />
                        </button>
                    </div>
                </div>

            </div>
        </footer>

        <!-- Interactive Legal & Policy Modals -->
        <Teleport to="body">
            <div v-if="activeLegalModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm animate-in fade-in duration-200">
                <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh] animate-in zoom-in-95 duration-200">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-5 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/20 border border-teal-500/30 flex items-center justify-center text-teal-400">
                                <ShieldCheck v-if="activeLegalModal === 'privacy'" class="w-5 h-5" />
                                <FileText v-else-if="activeLegalModal === 'terms'" class="w-5 h-5" />
                                <HelpCircle v-else class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-white">
                                    {{ activeLegalModal === 'privacy' ? 'Patient & Doctor Privacy Policy' : (activeLegalModal === 'terms' ? 'Terms of Service & Licensing' : 'Clinical & Medical Disclaimer') }}
                                </h3>
                                <p class="text-xs text-slate-400">Doc Helper AI • Regulatory Compliance Document</p>
                            </div>
                        </div>
                        <button @click="closeLegalModal" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-xl transition cursor-pointer">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Modal Body Content -->
                    <div class="p-6 sm:p-8 overflow-y-auto space-y-4 text-xs sm:text-sm text-slate-600 leading-relaxed">
                        
                        <!-- Privacy Policy Content -->
                        <template v-if="activeLegalModal === 'privacy'">
                            <div v-if="lc.privacy_policy" class="whitespace-pre-line leading-relaxed text-slate-700">
                                {{ lc.privacy_policy }}
                            </div>
                            <div v-else class="space-y-4">
                                <div class="p-3.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-900 text-xs font-medium">
                                    🔒 Your clinical and patient data is encrypted in transit and at rest using industry-standard 256-bit AES encryption.
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1">1. Patient Medical Record Confidentiality</h4>
                                    <p>Doc Helper AI complies strictly with international healthcare data protection frameworks including HIPAA guidelines. All patient vitals, diagnoses, clinical history, and prescription records are stored in isolated doctor-tenant databases.</p>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1">2. No Unauthorized Data Commercialization</h4>
                                    <p>We do not sell, rent, or distribute patient medical records, physician prescription trends, or personal contact details to any third-party pharmaceutical entities or advertising networks.</p>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1">3. Doctor Ownership of Clinical Data</h4>
                                    <p>Physicians and registered clinics maintain full ownership of all clinical lookup databases, patient records, and generated prescription files. Doctors can export practice records at any time in CSV/PDF format.</p>
                                </div>
                            </div>
                        </template>

                        <!-- Terms of Service Content -->
                        <template v-else-if="activeLegalModal === 'terms'">
                            <div v-if="lc.terms_of_service" class="whitespace-pre-line leading-relaxed text-slate-700">
                                {{ lc.terms_of_service }}
                            </div>
                            <div v-else class="space-y-4">
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1">1. SaaS Subscription & Service License</h4>
                                    <p>Doc Helper AI grants licensed physicians and clinical practices a non-exclusive, non-transferable license to access and utilize the prescription pad, multi-chamber queue, and patient management platform according to their subscribed plan tier.</p>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1">2. Multi-Chamber & Staff Account Usage</h4>
                                    <p>Practice owners are responsible for safeguarding staff credentials and ensuring appropriate role-based permissions are assigned for appointment desk operators and nursing staff.</p>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1">3. Uptime & Service Level Agreement (SLA)</h4>
                                    <p>We target 99.9% service availability. Scheduled system upgrades and database optimizations are performed during off-peak clinical hours with advance dashboard notices.</p>
                                </div>
                            </div>
                        </template>

                        <!-- Medical Disclaimer Content -->
                        <template v-else>
                            <div v-if="lc.medical_disclaimer" class="whitespace-pre-line leading-relaxed text-slate-700">
                                {{ lc.medical_disclaimer }}
                            </div>
                            <div v-else class="space-y-4">
                                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-medium">
                                    ⚠️ Notice: Doc Helper AI is a clinical workflow and practice management tool for licensed medical professionals.
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1">1. Clinical Professional Judgment</h4>
                                    <p>The prescription suggestions, type-ahead dosage hints, and drug lookup libraries provided within the platform are decision-support aids designed to accelerate typing and minimize transcription errors. They do not replace independent clinical judgment.</p>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm mb-1">2. Prescribing Authority</h4>
                                    <p>Only certified and legally registered healthcare practitioners are authorized to generate, finalize, and sign medical prescriptions for patients.</p>
                                </div>
                            </div>
                        </template>

                    </div>

                    <!-- Modal Footer -->
                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end">
                        <button 
                            type="button" 
                            @click="closeLegalModal" 
                            class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition cursor-pointer"
                        >
                            Close Document
                        </button>
                    </div>

                </div>
            </div>
        </Teleport>

    </div>
</template>
