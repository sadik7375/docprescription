<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    CreditCard,
    Plus,
    Edit3,
    Trash2,
    CheckCircle2,
    X,
    Eye,
    EyeOff,
    Sparkles,
    Check,
    Crown,
    Star,
    ArrowRight,
    Sliders,
    Zap,
    Building2,
    Layers,
    Save
} from 'lucide-vue-next';

const props = defineProps({
    plans: Array,
    annualDiscountBadge: {
        type: String,
        default: 'SAVE 25%',
    },
});

const isAnnual = ref(true);

const settingsForm = useForm({
    pricing_annual_discount_badge: props.annualDiscountBadge || 'SAVE 25%',
});

const saveSettings = () => {
    settingsForm.post(route('admin.pricing-plans.update-settings'), {
        preserveScroll: true,
    });
};
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedPlanId = ref(null);

const form = useForm({
    name: '',
    slug: '',
    tagline: '',
    currency: '$',
    monthly_price: 19,
    annual_price: 228,
    annual_discount_text: 'Billed annually',
    badge_text: '',
    is_featured: false,
    is_active: true,
    button_text: 'Get Started',
    button_link: '/register',
    features: [],
    sort_order: 1,
});

const newFeatureInput = ref('');

const openCreateModal = () => {
    isEditing.value = false;
    selectedPlanId.value = null;
    form.reset();
    form.clearErrors();
    form.currency = '$';
    form.monthly_price = 29;
    form.annual_price = 290;
    form.annual_discount_text = 'Billed annually (Save 20%)';
    form.is_active = true;
    form.is_featured = false;
    form.button_text = 'Start 14-Day Free Trial';
    form.button_link = '/register';
    form.features = [
        'Unlimited Digital Prescriptions',
        'AI Drug-to-Drug Interaction Checker',
        'Multi-Chamber Visiting Slots',
        'Patient Queue & Vitals Management',
    ];
    newFeatureInput.value = '';
    isModalOpen.value = true;
};

const openEditModal = (plan) => {
    isEditing.value = true;
    selectedPlanId.value = plan.id;
    form.clearErrors();
    form.name = plan.name;
    form.slug = plan.slug;
    form.tagline = plan.tagline || '';
    form.currency = plan.currency || '$';
    form.monthly_price = plan.monthly_price;
    form.annual_price = plan.annual_price;
    form.annual_discount_text = plan.annual_discount_text || '';
    form.badge_text = plan.badge_text || '';
    form.is_featured = !!plan.is_featured;
    form.is_active = !!plan.is_active;
    form.button_text = plan.button_text || 'Get Started';
    form.button_link = plan.button_link || '/register';
    form.features = Array.isArray(plan.features) ? [...plan.features] : [];
    form.sort_order = plan.sort_order || 1;
    newFeatureInput.value = '';
    isModalOpen.value = true;
};

const addFeature = () => {
    const text = newFeatureInput.value.trim();
    if (text) {
        form.features.push(text);
        newFeatureInput.value = '';
    }
};

const removeFeature = (idx) => {
    form.features.splice(idx, 1);
};

const toggleStatus = (plan) => {
    router.patch(route('admin.pricing-plans.toggle-status', plan.id), {}, {
        preserveScroll: true,
    });
};

const deletePlan = (plan) => {
    if (confirm(`Are you sure you want to delete the plan "${plan.name}"?`)) {
        router.delete(route('admin.pricing-plans.destroy', plan.id), {
            preserveScroll: true,
        });
    }
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.pricing-plans.update', selectedPlanId.value), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        form.post(route('admin.pricing-plans.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
};
</script>

<template>
    <Head title="Pricing Plans Manager - Super Admin Console" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6 pb-16">
            <!-- Top Action Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-teal-500 to-emerald-500 text-white flex items-center justify-center shadow-xs">
                            <CreditCard class="w-4 h-4" />
                        </div>
                        <h1 class="text-lg font-black text-slate-900 dark:text-white">
                            Subscription Plans & Pricing Manager
                        </h1>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Control landing page pricing cards, monthly/annual rates, currency, features, and highlight badges dynamically.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        <Plus class="w-4 h-4" />
                        <span>Create New Plan</span>
                    </button>
                </div>
            </div>

            <!-- Flash Success Message -->
            <div v-if="$page.props.flash?.success" class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>{{ $page.props.flash.success }}</span>
            </div>

                        <!-- Discount Badge Quick Settings Card -->
            <div class="bg-white dark:bg-[#161B22] p-4 rounded-2xl border border-slate-200/80 dark:border-[#21262D] flex flex-col md:flex-row items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                        <Sparkles class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            Annual Billing Discount Badge
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 font-mono">
                                {{ settingsForm.pricing_annual_discount_badge }}
                            </span>
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Custom badge text displayed on the Annual Billing button across Landing Page & Doctor Portal.</p>
                    </div>
                </div>
                <form @submit.prevent="saveSettings" class="flex items-center gap-2 w-full md:w-auto shrink-0">
                    <input
                        v-model="settingsForm.pricing_annual_discount_badge"
                        type="text"
                        placeholder="e.g. SAVE 25%"
                        class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-[#30363D] bg-slate-50 dark:bg-[#0D1117] text-slate-800 dark:text-white font-bold w-36 uppercase tracking-wide focus:ring-2 focus:ring-teal-500 outline-none"
                    />
                    <button
                        type="submit"
                        :disabled="settingsForm.processing"
                        class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 dark:bg-white dark:text-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-xs disabled:opacity-50 inline-flex items-center gap-1.5 cursor-pointer"
                    >
                        <Save class="w-3.5 h-3.5" />
                        <span>{{ settingsForm.processing ? 'Saving...' : 'Update Badge' }}</span>
                    </button>
                </form>
            </div>

            <!-- Billing Cycle Switcher for Live Preview -->
            <div class="flex flex-col items-center justify-center space-y-2 pt-2">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Live Preview & Management</p>
                <div class="inline-flex items-center bg-slate-100 dark:bg-[#161B22] p-1 rounded-xl border border-slate-200 dark:border-[#21262D]">
                    <button
                        type="button"
                        @click="isAnnual = false"
                        :class="[
                            'px-4 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer',
                            !isAnnual ? 'bg-white dark:bg-[#21262D] text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-900'
                        ]"
                    >
                        Monthly Billing
                    </button>
                    <button
                        type="button"
                        @click="isAnnual = true"
                        :class="[
                            'px-4 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer flex items-center gap-1.5',
                            isAnnual ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-900'
                        ]"
                    >
                        <span>Annual Billing</span>
                        <span v-if="annualDiscountBadge || settingsForm.pricing_annual_discount_badge" class="text-[10px] bg-teal-800 text-teal-200 px-1.5 py-0.2 rounded font-black uppercase">
                            {{ annualDiscountBadge || settingsForm.pricing_annual_discount_badge }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Pricing Plans Cards Grid (Exact Landing Page Replica with Management Controls) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch pt-2">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="relative rounded-3xl p-6 transition-all duration-300 flex flex-col justify-between border"
                    :class="[
                        plan.is_featured
                            ? 'bg-white dark:bg-[#161B22] border-2 border-teal-500 dark:border-teal-500 shadow-xl lg:-translate-y-2'
                            : 'bg-white dark:bg-[#161B22] border-slate-200/80 dark:border-[#21262D] shadow-sm',
                        !plan.is_active ? 'opacity-60 grayscale-[40%]' : ''
                    ]"
                >
                    <!-- Top Badge -->
                    <div
                        v-if="plan.badge_text"
                        class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-teal-600 text-white text-[10px] font-black tracking-wider uppercase py-1 px-3.5 rounded-full shadow-md flex items-center gap-1"
                    >
                        <span>{{ plan.badge_text }}</span>
                    </div>

                    <!-- Inactive Ribbon -->
                    <div
                        v-if="!plan.is_active"
                        class="absolute top-3 right-3 bg-rose-500 text-white text-[9px] font-black uppercase px-2 py-0.5 rounded-md shadow-xs"
                    >
                        Hidden from Landing Page
                    </div>

                    <div>
                        <!-- Plan Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span>{{ plan.name }}</span>
                                    <span v-if="plan.is_featured" class="text-teal-600 dark:text-teal-400">
                                        <Sparkles class="w-4 h-4" />
                                    </span>
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 min-h-[32px] leading-relaxed">
                                    {{ plan.tagline }}
                                </p>
                            </div>
                        </div>

                        <!-- Price Tag -->
                        <div class="mt-4 pb-4 border-b border-slate-100 dark:border-[#21262D]">
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-black text-slate-900 dark:text-white font-mono">
                                    {{ plan.currency }}{{ isAnnual ? Math.round(plan.annual_price / 12) : plan.monthly_price }}
                                </span>
                                <span class="text-xs text-slate-400 font-medium">/ doctor / month</span>
                            </div>
                            <p class="text-[11px] text-teal-600 dark:text-teal-400 font-bold mt-1">
                                {{ isAnnual ? plan.annual_discount_text : 'Billed monthly' }}
                            </p>
                        </div>

                        <!-- Features List -->
                        <div class="py-4 space-y-2.5">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Included Features</p>
                            <div
                                v-for="(feat, idx) in plan.features"
                                :key="idx"
                                class="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300"
                            >
                                <Check class="w-4 h-4 text-teal-600 dark:text-teal-400 shrink-0 mt-0.5" />
                                <span class="leading-tight">{{ feat }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons & Admin Controls -->
                    <div class="pt-4 border-t border-slate-100 dark:border-[#21262D] space-y-3">
                        <div
                            class="w-full text-center py-2.5 px-4 rounded-xl font-bold text-xs shadow-xs"
                            :class="[
                                plan.is_featured
                                    ? 'bg-teal-600 text-white'
                                    : 'bg-slate-100 dark:bg-[#21262D] text-slate-800 dark:text-slate-200 border border-slate-200/80 dark:border-[#30363D]'
                            ]"
                        >
                            {{ plan.button_text }}
                        </div>

                        <!-- Super Admin Edit / Toggle / Delete Toolbar -->
                        <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-[#21262D]">
                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    @click="toggleStatus(plan)"
                                    class="p-1.5 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors cursor-pointer"
                                    :class="plan.is_active ? 'text-teal-600 hover:bg-teal-50 dark:hover:bg-teal-950/40' : 'text-slate-400 hover:bg-slate-100'"
                                    :title="plan.is_active ? 'Click to Hide from Landing Page' : 'Click to Publish on Landing Page'"
                                >
                                    <Eye v-if="plan.is_active" class="w-3.5 h-3.5" />
                                    <EyeOff v-else class="w-3.5 h-3.5" />
                                    <span class="text-[11px]">{{ plan.is_active ? 'Published' : 'Hidden' }}</span>
                                </button>
                            </div>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="openEditModal(plan)"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1 transition-colors cursor-pointer"
                                >
                                    <Edit3 class="w-3.5 h-3.5" />
                                    <span>Edit</span>
                                </button>
                                <button
                                    type="button"
                                    @click="deletePlan(plan)"
                                    class="p-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-500 transition-colors cursor-pointer"
                                    title="Delete Plan"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State if no plans -->
            <div v-if="!plans.length" class="py-16 text-center bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200 dark:border-[#21262D] space-y-3">
                <CreditCard class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto" />
                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">No Pricing Plans Created</h3>
                <p class="text-xs text-slate-400">Click below to add your first subscription plan for doctors.</p>
                <button
                    type="button"
                    @click="openCreateModal"
                    class="px-4 py-2 bg-teal-600 text-white rounded-xl text-xs font-bold"
                >
                    + Add Plan
                </button>
            </div>
        </div>

        <!-- Create / Edit Plan Modal -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
        >
            <div class="relative w-full max-w-2xl bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200 dark:border-[#30363D] shadow-2xl p-6 space-y-5 my-8 max-h-[90vh] overflow-y-auto custom-scrollbar">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-teal-500/10 text-teal-600 flex items-center justify-center">
                            <CreditCard class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            {{ isEditing ? `Edit Plan: ${form.name}` : 'Create New Subscription Plan' }}
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="isModalOpen = false"
                        class="text-slate-400 hover:text-slate-600 cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <!-- Row 1: Name & Slug -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Plan Name *
                            </label>
                            <input
                                v-model="form.name"
                                required
                                placeholder="e.g. Solo Physician"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-bold"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Slug / System ID
                            </label>
                            <input
                                v-model="form.slug"
                                placeholder="solo / pro / enterprise"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Row 2: Tagline / Description -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Tagline / Description
                        </label>
                        <input
                            v-model="form.tagline"
                            placeholder="e.g. Essential digital prescription pad for independent private practitioners."
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                        />
                    </div>

                    <!-- Row 3: Pricing & Currency -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Currency Symbol
                            </label>
                            <select
                                v-model="form.currency"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-mono font-bold"
                            >
                                <option value="$">$ (USD)</option>
                                <option value="$">$ (USD)</option>
                                <option value="₹">₹ (INR)</option>
                                <option value="€">€ (EUR)</option>
                                <option value="£">£ (GBP)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Monthly Price ({{ form.currency }})
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                v-model.number="form.monthly_price"
                                required
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-mono font-bold"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Annual Price ({{ form.currency }})
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                v-model.number="form.annual_price"
                                required
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-mono font-bold"
                            />
                        </div>
                    </div>

                    <!-- Row 4: Annual Discount Text & Badge -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Annual Subtitle / Savings Note
                            </label>
                            <input
                                v-model="form.annual_discount_text"
                                placeholder="e.g. Billed annually ($468/yr) or Save 25%"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Popular / Highlight Badge (Optional)
                            </label>
                            <input
                                v-model="form.badge_text"
                                placeholder="e.g. ★ MOST POPULAR FOR CLINICS"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-semibold"
                            />
                        </div>
                    </div>

                    <!-- Row 5: Button Text & Link -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Button Call-To-Action Text
                            </label>
                            <input
                                v-model="form.button_text"
                                placeholder="Start 14-Day Free Trial"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-semibold"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Button Link URL
                            </label>
                            <input
                                v-model="form.button_link"
                                placeholder="/register or #contact"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-mono"
                            />
                        </div>
                    </div>

                    <!-- Row 6: Features Manager -->
                    <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-[#21262D]">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                            Included Plan Features ({{ form.features.length }})
                        </label>
                        
                        <!-- Feature items list -->
                        <div class="space-y-1.5 max-h-48 overflow-y-auto custom-scrollbar p-1">
                            <div
                                v-for="(feat, idx) in form.features"
                                :key="idx"
                                class="flex items-center justify-between gap-2 p-2 bg-slate-50 dark:bg-[#21262D] rounded-xl text-xs border border-slate-200/60 dark:border-[#30363D]"
                            >
                                <div class="flex items-center gap-2 flex-1">
                                    <Check class="w-3.5 h-3.5 text-teal-600 shrink-0" />
                                    <input
                                        v-model="form.features[idx]"
                                        class="w-full bg-transparent border-none p-0 text-xs text-slate-900 dark:text-white focus:ring-0"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="removeFeature(idx)"
                                    class="text-slate-400 hover:text-rose-500 p-1 cursor-pointer"
                                >
                                    <X class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Add new feature input -->
                        <div class="flex items-center gap-2 pt-1">
                            <input
                                v-model="newFeatureInput"
                                @keyup.enter.prevent="addFeature"
                                placeholder="Type a feature (e.g. Dedicated WhatsApp Support) & press Enter..."
                                class="flex-1 px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                            />
                            <button
                                type="button"
                                @click="addFeature"
                                class="px-3 py-2 bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shrink-0 cursor-pointer"
                            >
                                + Add
                            </button>
                        </div>
                    </div>

                    <!-- Row 7: Toggles -->
                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-100 dark:border-[#21262D]">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.is_featured"
                                class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                            />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Highlight as Featured Card</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.is_active"
                                class="rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                            />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Published (Visible on Landing Page)</span>
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-[#21262D]">
                        <button
                            type="button"
                            @click="isModalOpen = false"
                            class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 rounded-xl"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors cursor-pointer disabled:opacity-50"
                        >
                            <Save class="w-4 h-4" />
                            <span>{{ form.processing ? 'Saving...' : (isEditing ? 'Update Plan' : 'Create Plan') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
