<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Globe,
    LayoutTemplate,
    Sparkles,
    CheckCircle2,
    Save,
    Plus,
    Trash2,
    HelpCircle,
    Star,
    Zap,
    Building2,
    ArrowRight,
    ExternalLink,
    Image as ImageIcon,
    Upload,
    X,
    Eye
} from 'lucide-vue-next';

const props = defineProps({
    content: Object,
});

const activeTab = ref('branding'); // 'branding' | 'hero' | 'stats' | 'features' | 'specialties' | 'testimonials' | 'faqs' | 'cta_footer'

// File preview refs
const logoPreview = ref(props.content.brand_logo_url || null);
const faviconPreview = ref(props.content.brand_favicon_url || null);
const heroImagePreview = ref(props.content.hero_image_url || null);

const form = useForm({
    _method: 'POST',

    // Brand & Logo
    brand_name: props.content.brand_name || 'Doc Helper',
    brand_name_highlight: props.content.brand_name_highlight || 'AI',
    brand_tagline: props.content.brand_tagline || 'Clinical Practice OS',
    brand_logo: null,
    brand_favicon: null,
    remove_logo: false,
    remove_favicon: false,

    // Meta
    meta_title: props.content.meta_title || '',
    meta_description: props.content.meta_description || '',

    // Hero Section
    hero_badge: props.content.hero_badge || '',
    hero_version_text: props.content.hero_version_text || '',
    hero_title_line1: props.content.hero_title_line1 || '',
    hero_title_highlight: props.content.hero_title_highlight || '',
    hero_title_line2: props.content.hero_title_line2 || '',
    hero_subtitle: props.content.hero_subtitle || '',
    hero_cta_primary_text: props.content.hero_cta_primary_text || '',
    hero_cta_primary_link: props.content.hero_cta_primary_link || '',
    hero_cta_secondary_text: props.content.hero_cta_secondary_text || '',
    hero_cta_secondary_link: props.content.hero_cta_secondary_link || '',
    hero_image: null,
    remove_hero_image: false,

    // Trust points
    trust_point_1: props.content.trust_point_1 || '',
    trust_point_2: props.content.trust_point_2 || '',
    trust_point_3: props.content.trust_point_3 || '',

    // Stats
    stats: props.content.stats ? JSON.parse(JSON.stringify(props.content.stats)) : [],

    // Features
    features_badge: props.content.features_badge || '',
    features_title: props.content.features_title || '',
    features_subtitle: props.content.features_subtitle || '',
    features: props.content.features ? JSON.parse(JSON.stringify(props.content.features)) : [],

    // Specialties
    specialties_title: props.content.specialties_title || '',
    specialties_subtitle: props.content.specialties_subtitle || '',
    specialties: props.content.specialties ? JSON.parse(JSON.stringify(props.content.specialties)) : [],
    newSpecialty: '',

    // Testimonials
    testimonials_title: props.content.testimonials_title || '',
    testimonials_subtitle: props.content.testimonials_subtitle || '',
    testimonials: props.content.testimonials ? JSON.parse(JSON.stringify(props.content.testimonials)) : [],

    // FAQs
    faqs_title: props.content.faqs_title || '',
    faqs_subtitle: props.content.faqs_subtitle || '',
    faqs: props.content.faqs ? JSON.parse(JSON.stringify(props.content.faqs)) : [],

    // Bottom CTA
    bottom_cta_title: props.content.bottom_cta_title || '',
    bottom_cta_subtitle: props.content.bottom_cta_subtitle || '',
    bottom_cta_btn_text: props.content.bottom_cta_btn_text || '',
    bottom_cta_note: props.content.bottom_cta_note || '',

    // Footer & Legal Policies
    footer_about: props.content.footer_about || '',
    footer_copyright: props.content.footer_copyright || '',
    privacy_policy: props.content.privacy_policy || '',
    terms_of_service: props.content.terms_of_service || '',
    medical_disclaimer: props.content.medical_disclaimer || '',
});

// Handle File Selection with Live Preview
const handleLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.brand_logo = file;
        form.remove_logo = false;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleRemoveLogo = () => {
    form.brand_logo = null;
    form.remove_logo = true;
    logoPreview.value = null;
};

const handleFaviconChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.brand_favicon = file;
        form.remove_favicon = false;
        faviconPreview.value = URL.createObjectURL(file);
    }
};

const handleRemoveFavicon = () => {
    form.brand_favicon = null;
    form.remove_favicon = true;
    faviconPreview.value = null;
};

const handleHeroImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.hero_image = file;
        form.remove_hero_image = false;
        heroImagePreview.value = URL.createObjectURL(file);
    }
};

const handleRemoveHeroImage = () => {
    form.hero_image = null;
    form.remove_hero_image = true;
    heroImagePreview.value = null;
};

// Helper for Stats
const addStat = () => {
    form.stats.push({ value: '100+', label: 'New Metric', sub: 'Short description' });
};
const removeStat = (index) => {
    form.stats.splice(index, 1);
};

// Helper for Features
const addFeature = () => {
    form.features.push({
        title: 'New Feature Title',
        desc: 'Describe how this feature helps doctors and clinics improve workflow.',
        color: 'teal'
    });
};
const removeFeature = (index) => {
    form.features.splice(index, 1);
};

// Helper for Specialties
const addSpecialty = () => {
    if (!form.newSpecialty?.trim()) return;
    form.specialties.push(form.newSpecialty.trim());
    form.newSpecialty = '';
};
const removeSpecialty = (index) => {
    form.specialties.splice(index, 1);
};

// Helper for Testimonials
const addTestimonial = () => {
    form.testimonials.push({
        quote: 'This software has revolutionized my clinic workflow!',
        author: 'Dr. John Doe',
        title: 'Senior Specialist, City Hospital',
        rating: 5,
    });
};
const removeTestimonial = (index) => {
    form.testimonials.splice(index, 1);
};

// Helper for FAQs
const addFaq = () => {
    form.faqs.push({
        q: 'New Question title here?',
        a: 'Detailed explanation and answer to the question.'
    });
};
const removeFaq = (index) => {
    form.faqs.splice(index, 1);
};

const saveContent = () => {
    form.post(route('admin.landing-content.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Landing Page Content & Logo Manager - Super Admin" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-7xl mx-auto">
            
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400">
                            <Globe class="w-6 h-6" />
                        </span>
                        <div>
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                Landing Page & Branding CMS
                                <span class="px-2 py-0.5 rounded-md text-[10px] uppercase font-black tracking-wider bg-teal-500/20 text-teal-600 dark:text-teal-400 border border-teal-500/30">
                                    Live Customizer
                                </span>
                            </h1>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Upload custom logo images, brand names, hero screenshots, headlines, statistics, features, and testimonials.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="/"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition shadow-xs"
                    >
                        <ExternalLink class="w-4 h-4" />
                        <span>View Live Landing Page</span>
                    </a>
                    <button
                        @click="saveContent"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-black shadow-md shadow-teal-600/20 transition cursor-pointer"
                    >
                        <Save class="w-4 h-4" />
                        <span>{{ form.processing ? 'Saving...' : 'Save All Changes' }}</span>
                    </button>
                </div>
            </div>

            <!-- Main Workspace Card -->
            <div class="bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                
                <!-- Section Tabs Bar -->
                <div class="flex items-center overflow-x-auto border-b border-slate-200 dark:border-[#21262D] bg-slate-50/70 dark:bg-[#1C2128]/50 p-2 gap-1.5 custom-scrollbar">
                    <button
                        @click="activeTab = 'branding'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2',
                            activeTab === 'branding'
                                ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-[#30363D]'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <ImageIcon class="w-3.5 h-3.5" />
                        <span>Logo & Brand Identity</span>
                    </button>

                    <button
                        @click="activeTab = 'hero'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2',
                            activeTab === 'hero'
                                ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-[#30363D]'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <Sparkles class="w-3.5 h-3.5" />
                        <span>Hero Slogans & Image</span>
                    </button>

                    <button
                        @click="activeTab = 'stats'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2',
                            activeTab === 'stats'
                                ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-[#30363D]'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <Zap class="w-3.5 h-3.5" />
                        <span>Stats Counters</span>
                    </button>

                    <button
                        @click="activeTab = 'features'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2',
                            activeTab === 'features'
                                ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-[#30363D]'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <LayoutTemplate class="w-3.5 h-3.5" />
                        <span>Core Features</span>
                    </button>

                    <button
                        @click="activeTab = 'specialties'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2',
                            activeTab === 'specialties'
                                ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-[#30363D]'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <Building2 class="w-3.5 h-3.5" />
                        <span>Specialties</span>
                    </button>

                    <button
                        @click="activeTab = 'testimonials'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2',
                            activeTab === 'testimonials'
                                ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-[#30363D]'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <Star class="w-3.5 h-3.5" />
                        <span>Doctor Reviews</span>
                    </button>

                    <button
                        @click="activeTab = 'faqs'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2',
                            activeTab === 'faqs'
                                ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-[#30363D]'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <HelpCircle class="w-3.5 h-3.5" />
                        <span>FAQs</span>
                    </button>

                    <button
                        @click="activeTab = 'cta_footer'"
                        :class="[
                            'px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap flex items-center gap-2',
                            activeTab === 'cta_footer'
                                ? 'bg-white dark:bg-[#161B22] text-teal-600 dark:text-teal-400 shadow-sm border border-slate-200/80 dark:border-[#30363D]'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <ArrowRight class="w-3.5 h-3.5" />
                        <span>Bottom CTA & Footer</span>
                    </button>
                </div>

                <!-- Form Body -->
                <form @submit.prevent="saveContent" class="p-6 sm:p-8 space-y-6">
                    
                    <!-- TAB 0: LOGO & BRAND IDENTITY -->
                    <div v-show="activeTab === 'branding'" class="space-y-6">
                        
                        <div class="border-b border-slate-200 dark:border-[#21262D] pb-3">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                1. Header Logo & Brand Name Customization
                            </h3>
                            <p class="text-xs text-slate-500">
                                Upload your custom clinic or company logo, set the brand name with optional gradient badge, and configure favicon.
                            </p>
                        </div>

                        <!-- Brand Logo Upload Card -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Left: Header Logo Upload -->
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                            <span>Header / Brand Logo Image</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-900 dark:bg-teal-900/60 dark:text-teal-300">
                                                Size: 240×60 px or 200×50 px
                                            </span>
                                        </h4>
                                        <p class="text-[11px] text-slate-500">Recommended: Transparent PNG or SVG (Max height: 50px, Ratio: 4:1)</p>
                                    </div>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                        Max 3MB
                                    </span>
                                </div>

                                <!-- Logo Preview Box -->
                                <div class="p-4 rounded-xl bg-white dark:bg-[#161B22] border border-dashed border-slate-300 dark:border-[#30363D] flex items-center justify-center min-h-[110px] relative">
                                    <template v-if="logoPreview">
                                        <div class="flex flex-col items-center gap-2">
                                            <img :src="logoPreview" alt="Brand Logo Preview" class="h-12 w-auto max-w-[200px] object-contain" />
                                            <button
                                                type="button"
                                                @click="handleRemoveLogo"
                                                class="px-2.5 py-1 rounded-md text-[11px] font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition flex items-center gap-1"
                                            >
                                                <Trash2 class="w-3.5 h-3.5" />
                                                <span>Remove Logo (Use Default Icon)</span>
                                            </button>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="text-center text-slate-400 space-y-1">
                                            <ImageIcon class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600" />
                                            <p class="text-xs">No custom logo uploaded yet. Default Stethoscope icon active.</p>
                                        </div>
                                    </template>
                                </div>

                                <!-- Upload Input Button -->
                                <label class="w-full py-2.5 px-4 rounded-xl bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] hover:border-teal-500 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-xs transition">
                                    <Upload class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                    <span>Choose New Logo File</span>
                                    <input type="file" @change="handleLogoChange" accept="image/*" class="hidden" />
                                </label>
                                <p v-if="form.errors.brand_logo" class="text-xs text-rose-500">{{ form.errors.brand_logo }}</p>
                            </div>

                            <!-- Right: Favicon Upload -->
                            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                            <span>Browser Tab Favicon</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-900 dark:bg-teal-900/60 dark:text-teal-300">
                                                Size: 32×32 or 64×64 px
                                            </span>
                                        </h4>
                                        <p class="text-[11px] text-slate-500">Square PNG, ICO or SVG (Ratio: 1:1)</p>
                                    </div>
                                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                        Max 1MB
                                    </span>
                                </div>

                                <!-- Favicon Preview Box -->
                                <div class="p-4 rounded-xl bg-white dark:bg-[#161B22] border border-dashed border-slate-300 dark:border-[#30363D] flex items-center justify-center min-h-[110px] relative">
                                    <template v-if="faviconPreview">
                                        <div class="flex flex-col items-center gap-2">
                                            <img :src="faviconPreview" alt="Favicon Preview" class="w-8 h-8 object-contain rounded-md" />
                                            <button
                                                type="button"
                                                @click="handleRemoveFavicon"
                                                class="px-2.5 py-1 rounded-md text-[11px] font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition flex items-center gap-1"
                                            >
                                                <Trash2 class="w-3.5 h-3.5" />
                                                <span>Remove Favicon</span>
                                            </button>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="text-center text-slate-400 space-y-1">
                                            <Globe class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-600" />
                                            <p class="text-xs">Default favicon active.</p>
                                        </div>
                                    </template>
                                </div>

                                <!-- Upload Input Button -->
                                <label class="w-full py-2.5 px-4 rounded-xl bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] hover:border-teal-500 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-xs transition">
                                    <Upload class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                    <span>Choose New Favicon File</span>
                                    <input type="file" @change="handleFaviconChange" accept="image/*" class="hidden" />
                                </label>
                                <p v-if="form.errors.brand_favicon" class="text-xs text-rose-500">{{ form.errors.brand_favicon }}</p>
                            </div>

                        </div>

                        <!-- Brand Texts & Subtitle -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Brand Main Name
                                </label>
                                <input
                                    type="text"
                                    v-model="form.brand_name"
                                    placeholder="Doc Helper"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold focus:border-teal-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Brand Highlight Badge
                                </label>
                                <input
                                    type="text"
                                    v-model="form.brand_name_highlight"
                                    placeholder="AI"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-mono font-bold focus:border-teal-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Brand Tagline / Category
                                </label>
                                <input
                                    type="text"
                                    v-model="form.brand_tagline"
                                    placeholder="Clinical Practice OS"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs focus:border-teal-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- Live Header Preview Simulation -->
                        <div class="p-5 rounded-2xl bg-slate-100 dark:bg-[#1C2128]/80 border border-slate-200 dark:border-[#30363D] space-y-2">
                            <span class="text-[10px] font-bold uppercase text-slate-500 tracking-wider flex items-center gap-1.5">
                                <Eye class="w-3.5 h-3.5" />
                                <span>Live Header Brand Preview (How Visitors See It)</span>
                            </span>
                            <div class="p-4 rounded-xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] flex items-center gap-3">
                                <template v-if="logoPreview">
                                    <img :src="logoPreview" alt="Preview" class="h-10 w-auto max-w-[150px] object-contain" />
                                </template>
                                <template v-else>
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-teal-600 via-teal-500 to-cyan-500 p-0.5 shadow-xs">
                                        <div class="w-full h-full bg-white dark:bg-[#161B22] rounded-[10px] flex items-center justify-center">
                                            <Sparkles class="w-5 h-5 text-teal-600" />
                                        </div>
                                    </div>
                                </template>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-base font-bold text-slate-900 dark:text-white">{{ form.brand_name }}</span>
                                        <span v-if="form.brand_name_highlight" class="text-[10px] font-black tracking-wider uppercase px-1.5 py-0.5 rounded bg-gradient-to-r from-teal-600 to-cyan-600 text-white font-mono">
                                            {{ form.brand_name_highlight }}
                                        </span>
                                    </div>
                                    <p v-if="form.brand_tagline" class="text-[9px] font-semibold text-teal-700 dark:text-teal-400 uppercase tracking-wider -mt-0.5">
                                        {{ form.brand_tagline }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 1: HERO SLOGANS & HERO IMAGE -->
                    <div v-show="activeTab === 'hero'" class="space-y-6">
                        
                        <div class="border-b border-slate-200 dark:border-[#21262D] pb-3">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                2. Hero Headlines & Screenshot Image
                            </h3>
                            <p class="text-xs text-slate-500">
                                Customize the 3-part main headline, badge, subtitle, action buttons, and hero mockup image.
                            </p>
                        </div>

                        <!-- Custom Hero Banner/Screenshot Upload -->
                        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                        <span>Custom Hero Platform Screenshot / Banner</span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-900 dark:bg-teal-900/60 dark:text-teal-300">
                                            Size: 1200×750 px or 1920×1080 px
                                        </span>
                                    </h4>
                                    <p class="text-[11px] text-slate-500">
                                        Upload your own custom UI screenshot or banner (16:9 or 16:10). If empty, the interactive dual-column live mockup is shown.
                                    </p>
                                </div>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-teal-50 dark:bg-teal-950/50 text-teal-700 dark:text-teal-300 border border-teal-200 dark:border-teal-800">
                                    Max 5MB
                                </span>
                            </div>

                            <div class="p-4 rounded-xl bg-white dark:bg-[#161B22] border border-dashed border-slate-300 dark:border-[#30363D] flex items-center justify-center min-h-[140px]">
                                <template v-if="heroImagePreview">
                                    <div class="flex flex-col items-center gap-3 w-full max-w-lg">
                                        <img :src="heroImagePreview" alt="Hero Screenshot Preview" class="w-full h-auto max-h-56 object-cover rounded-xl shadow-md" />
                                        <button
                                            type="button"
                                            @click="handleRemoveHeroImage"
                                            class="px-3 py-1 rounded-lg text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition flex items-center gap-1"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                            <span>Remove Custom Image (Restore Interactive Rx Mockup)</span>
                                        </button>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="text-center text-slate-400 space-y-1">
                                        <ImageIcon class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" />
                                        <p class="text-xs">Interactive built-in Prescription & Vitals Mockup is currently active.</p>
                                    </div>
                                </template>
                            </div>

                            <label class="w-full py-2.5 px-4 rounded-xl bg-white dark:bg-[#161B22] border border-slate-300 dark:border-[#30363D] hover:border-teal-500 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center justify-center gap-2 cursor-pointer shadow-xs transition">
                                <Upload class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span>Upload Custom Hero Screenshot</span>
                                <input type="file" @change="handleHeroImageChange" accept="image/*" class="hidden" />
                            </label>
                            <p v-if="form.errors.hero_image" class="text-xs text-rose-500">{{ form.errors.hero_image }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Hero Badge Text
                                </label>
                                <input
                                    type="text"
                                    v-model="form.hero_badge"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs focus:border-teal-500 focus:outline-none"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Version Pill Tag
                                </label>
                                <input
                                    type="text"
                                    v-model="form.hero_version_text"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-mono focus:border-teal-500 focus:outline-none"
                                />
                            </div>
                        </div>

                        <!-- 3-Part Headline -->
                        <div class="p-5 rounded-2xl bg-teal-500/5 border border-teal-500/20 space-y-4">
                            <label class="block text-xs font-bold text-teal-800 dark:text-teal-300 uppercase tracking-wider">
                                3-Part Main Headline Builder
                            </label>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <span class="text-[11px] text-slate-500 block mb-1">Line 1 (Top)</span>
                                    <input
                                        type="text"
                                        v-model="form.hero_title_line1"
                                        placeholder="The Smart AI-Powered"
                                        class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold focus:border-teal-500 focus:outline-none"
                                    />
                                </div>
                                <div>
                                    <span class="text-[11px] text-teal-600 dark:text-teal-400 font-bold block mb-1">Gradient Highlight Line</span>
                                    <input
                                        type="text"
                                        v-model="form.hero_title_highlight"
                                        placeholder="Prescription & Practice OS"
                                        class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#1C2128] border border-teal-400 text-teal-600 dark:text-teal-400 text-xs font-black focus:outline-none"
                                    />
                                </div>
                                <div>
                                    <span class="text-[11px] text-slate-500 block mb-1">Line 2 (Bottom)</span>
                                    <input
                                        type="text"
                                        v-model="form.hero_title_line2"
                                        placeholder="for Modern Doctors"
                                        class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold focus:border-teal-500 focus:outline-none"
                                    />
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Hero Subtitle Description
                            </label>
                            <textarea
                                v-model="form.hero_subtitle"
                                rows="3"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs leading-relaxed focus:border-teal-500 focus:outline-none"
                            ></textarea>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-3">
                                <span class="text-xs font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider block">Primary CTA Button</span>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">Button Label</span>
                                    <input
                                        type="text"
                                        v-model="form.hero_cta_primary_text"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-900 dark:text-white font-bold"
                                    />
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">Button Link</span>
                                    <input
                                        type="text"
                                        v-model="form.hero_cta_primary_link"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-mono text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-3">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider block">Secondary Demo Button</span>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">Button Label</span>
                                    <input
                                        type="text"
                                        v-model="form.hero_cta_secondary_text"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-900 dark:text-white font-bold"
                                    />
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">Button Link / Anchor</span>
                                    <input
                                        type="text"
                                        v-model="form.hero_cta_secondary_link"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-mono text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Trust Points -->
                        <div class="space-y-3 pt-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                3 Trust Points Under Hero
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <input
                                    type="text"
                                    v-model="form.trust_point_1"
                                    placeholder="Point 1"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                                />
                                <input
                                    type="text"
                                    v-model="form.trust_point_2"
                                    placeholder="Point 2"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                                />
                                <input
                                    type="text"
                                    v-model="form.trust_point_3"
                                    placeholder="Point 3"
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                                />
                            </div>
                        </div>

                    </div>

                    <!-- TAB 2: STATS COUNTERS -->
                    <div v-show="activeTab === 'stats'" class="space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#21262D] pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    3. Global Stats & Counter Bar
                                </h3>
                                <p class="text-xs text-slate-500">Highlighted achievement numbers displayed across the homepage strip.</p>
                            </div>
                            <button
                                type="button"
                                @click="addStat"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 text-xs font-bold hover:bg-teal-100 transition"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Statistic</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div
                                v-for="(stat, sIdx) in form.stats"
                                :key="sIdx"
                                class="p-4 rounded-2xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-3 relative group"
                            >
                                <button
                                    type="button"
                                    @click="removeStat(sIdx)"
                                    class="absolute top-3 right-3 p-1 rounded-lg text-slate-400 hover:text-rose-600 transition"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-teal-600 dark:text-teal-400">Large Value / Number</span>
                                    <input
                                        type="text"
                                        v-model="stat.value"
                                        placeholder="e.g. 2.5M+ or < 45s"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-mono font-bold text-slate-900 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Title Label</span>
                                    <input
                                        type="text"
                                        v-model="stat.label"
                                        placeholder="Average Rx Creation Time"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-semibold text-slate-900 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Subtext Description</span>
                                    <input
                                        type="text"
                                        v-model="stat.sub"
                                        placeholder="Save 3+ hours daily"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-600 dark:text-slate-300"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: CORE FEATURES -->
                    <div v-show="activeTab === 'features'" class="space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#21262D] pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    4. Core Clinical Features Grid
                                </h3>
                                <p class="text-xs text-slate-500">Manage feature header text and individual feature cards.</p>
                            </div>
                            <button
                                type="button"
                                @click="addFeature"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 text-xs font-bold hover:bg-teal-100 transition"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Feature Card</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Section Pill Badge
                                </label>
                                <input
                                    type="text"
                                    v-model="form.features_badge"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Main Section Title
                                </label>
                                <input
                                    type="text"
                                    v-model="form.features_title"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Section Subtitle
                            </label>
                            <textarea
                                v-model="form.features_subtitle"
                                rows="2"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                            ></textarea>
                        </div>

                        <!-- Feature Cards List -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
                            <div
                                v-for="(feat, fIdx) in form.features"
                                :key="fIdx"
                                class="p-5 rounded-2xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-3 relative group"
                            >
                                <button
                                    type="button"
                                    @click="removeFeature(fIdx)"
                                    class="absolute top-3 right-3 p-1 rounded-lg text-slate-400 hover:text-rose-600 transition"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-teal-600 dark:text-teal-400">Feature Title</span>
                                    <input
                                        type="text"
                                        v-model="feat.title"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-bold text-slate-900 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Feature Description</span>
                                    <textarea
                                        v-model="feat.desc"
                                        rows="3"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-700 dark:text-slate-300 leading-relaxed"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: SPECIALTIES -->
                    <div v-show="activeTab === 'specialties'" class="space-y-6">
                        <div class="border-b border-slate-200 dark:border-[#21262D] pb-3">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                5. Supported Specialties
                            </h3>
                            <p class="text-xs text-slate-500">Add or remove clinical departments shown in specialty chips.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Specialties Title
                                </label>
                                <input
                                    type="text"
                                    v-model="form.specialties_title"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Specialties Subtitle
                                </label>
                                <input
                                    type="text"
                                    v-model="form.specialties_subtitle"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                                />
                            </div>
                        </div>

                        <!-- Add Specialty Input -->
                        <div class="flex items-center gap-3">
                            <input
                                type="text"
                                v-model="form.newSpecialty"
                                @keyup.enter.prevent="addSpecialty"
                                placeholder="Type a specialty name (e.g. Dermatology, Oncology)..."
                                class="flex-1 px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                            />
                            <button
                                type="button"
                                @click="addSpecialty"
                                class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-xs"
                            >
                                + Add Specialty
                            </button>
                        </div>

                        <!-- Specialty Badges -->
                        <div class="flex flex-wrap gap-2 pt-2">
                            <div
                                v-for="(spec, idx) in form.specialties"
                                :key="idx"
                                class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-xs font-medium text-slate-800 dark:text-slate-200 flex items-center gap-2"
                            >
                                <span>{{ spec }}</span>
                                <button
                                    type="button"
                                    @click="removeSpecialty(idx)"
                                    class="text-slate-400 hover:text-rose-500 font-bold"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: DOCTOR REVIEWS & TESTIMONIALS -->
                    <div v-show="activeTab === 'testimonials'" class="space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#21262D] pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    6. Doctor Testimonials & Reviews
                                </h3>
                                <p class="text-xs text-slate-500">Manage doctor quotes, doctor names, affiliations, and ratings.</p>
                            </div>
                            <button
                                type="button"
                                @click="addTestimonial"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 text-xs font-bold hover:bg-teal-100 transition"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Testimonial</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Section Title
                                </label>
                                <input
                                    type="text"
                                    v-model="form.testimonials_title"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Section Subtitle
                                </label>
                                <input
                                    type="text"
                                    v-model="form.testimonials_subtitle"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                            <div
                                v-for="(test, tIdx) in form.testimonials"
                                :key="tIdx"
                                class="p-5 rounded-2xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-3 relative group"
                            >
                                <button
                                    type="button"
                                    @click="removeTestimonial(tIdx)"
                                    class="absolute top-3 right-3 p-1 rounded-lg text-slate-400 hover:text-rose-600 transition"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-teal-600 dark:text-teal-400">Doctor Name</span>
                                    <input
                                        type="text"
                                        v-model="test.author"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-bold text-slate-900 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Specialty & Workplace</span>
                                    <input
                                        type="text"
                                        v-model="test.title"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-700 dark:text-slate-300"
                                    />
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Doctor Quote / Review</span>
                                    <textarea
                                        v-model="test.quote"
                                        rows="3"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-700 dark:text-slate-300 italic leading-relaxed"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 6: FAQS -->
                    <div v-show="activeTab === 'faqs'" class="space-y-6">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-[#21262D] pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                    7. Frequently Asked Questions (FAQ)
                                </h3>
                                <p class="text-xs text-slate-500">Manage FAQ questions and answers.</p>
                            </div>
                            <button
                                type="button"
                                @click="addFaq"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/50 border border-teal-200 dark:border-teal-800 text-teal-700 dark:text-teal-300 text-xs font-bold hover:bg-teal-100 transition"
                            >
                                <Plus class="w-4 h-4" />
                                <span>Add Question</span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    FAQ Section Title
                                </label>
                                <input
                                    type="text"
                                    v-model="form.faqs_title"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    FAQ Section Subtitle
                                </label>
                                <input
                                    type="text"
                                    v-model="form.faqs_subtitle"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                                />
                            </div>
                        </div>

                        <div class="space-y-4 pt-2">
                            <div
                                v-for="(faq, fIdx) in form.faqs"
                                :key="fIdx"
                                class="p-5 rounded-2xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] space-y-3 relative group"
                            >
                                <button
                                    type="button"
                                    @click="removeFaq(fIdx)"
                                    class="absolute top-4 right-4 p-1 rounded-lg text-slate-400 hover:text-rose-600 transition"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-teal-600 dark:text-teal-400">Question {{ fIdx + 1 }}</span>
                                    <input
                                        type="text"
                                        v-model="faq.q"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-bold text-slate-900 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Answer</span>
                                    <textarea
                                        v-model="faq.a"
                                        rows="3"
                                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-700 dark:text-slate-300 leading-relaxed"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 7: CTA & FOOTER -->
                    <div v-show="activeTab === 'cta_footer'" class="space-y-6">
                        <div class="border-b border-slate-200 dark:border-[#21262D] pb-3">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                8. Bottom CTA Banner & Global Footer
                            </h3>
                            <p class="text-xs text-slate-500">Configure final call to action banner and footer description.</p>
                        </div>

                        <!-- Bottom CTA Banner Card -->
                        <div class="p-5 rounded-2xl bg-teal-500/5 border border-teal-500/20 space-y-4">
                            <h4 class="text-xs font-bold text-teal-800 dark:text-teal-300 uppercase tracking-wider">
                                Big Call to Action Banner
                            </h4>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">Banner Headline</span>
                                    <input
                                        type="text"
                                        v-model="form.bottom_cta_title"
                                        class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-bold text-slate-900 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold">Button Text</span>
                                    <input
                                        type="text"
                                        v-model="form.bottom_cta_btn_text"
                                        class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs font-bold text-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Banner Subtitle</span>
                                <textarea
                                    v-model="form.bottom_cta_subtitle"
                                    rows="2"
                                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-900 dark:text-white"
                                ></textarea>
                            </div>

                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Bottom Note / Guarantee</span>
                                <input
                                    type="text"
                                    v-model="form.bottom_cta_note"
                                    class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-700 dark:text-slate-300"
                                />
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Footer About Paragraph
                                </label>
                                <textarea
                                    v-model="form.footer_about"
                                    rows="3"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs leading-relaxed"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Footer Copyright Text
                                </label>
                                <input
                                    type="text"
                                    v-model="form.footer_copyright"
                                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                                />
                            </div>

                            <!-- Legal & Compliance Documents Editor -->
                            <div class="pt-4 border-t border-slate-200 dark:border-[#21262D] space-y-4">
                                <div class="flex items-center gap-2">
                                    <span class="p-1.5 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400">
                                        <ShieldCheck class="w-4 h-4" />
                                    </span>
                                    <div>
                                        <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">
                                            Legal Documents & Compliance (Footer Modals)
                                        </h4>
                                        <p class="text-[11px] text-slate-500">Edit content displayed in the Privacy Policy, Terms of Service, and Medical Disclaimer popups.</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Privacy Policy (Patient & Clinical Data Protection)</span>
                                        <span class="text-[10px] text-teal-600 font-normal">Displayed in Privacy Modal</span>
                                    </label>
                                    <textarea
                                        v-model="form.privacy_policy"
                                        rows="4"
                                        placeholder="Enter full Privacy Policy terms here..."
                                        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs leading-relaxed font-mono"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Terms of Service & Platform Licensing</span>
                                        <span class="text-[10px] text-teal-600 font-normal">Displayed in Terms Modal</span>
                                    </label>
                                    <textarea
                                        v-model="form.terms_of_service"
                                        rows="4"
                                        placeholder="Enter SaaS Terms of Service and license rules here..."
                                        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs leading-relaxed font-mono"
                                    ></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Medical & Clinical Disclaimer</span>
                                        <span class="text-[10px] text-teal-600 font-normal">Displayed in Disclaimer Modal</span>
                                    </label>
                                    <textarea
                                        v-model="form.medical_disclaimer"
                                        rows="3"
                                        placeholder="Enter clinical decision support disclaimer..."
                                        class="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs leading-relaxed font-mono"
                                    ></textarea>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="pt-6 border-t border-slate-200 dark:border-[#21262D] flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <CheckCircle2 class="w-4 h-4 text-teal-600" />
                            <span>Logo, brand name, and hero screenshot updates apply immediately</span>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-8 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-black shadow-lg shadow-teal-600/25 transition cursor-pointer"
                        >
                            <Save class="w-4 h-4" />
                            <span>{{ form.processing ? 'Saving...' : 'Save All Changes' }}</span>
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </AuthenticatedLayout>
</template>