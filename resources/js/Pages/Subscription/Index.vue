<script setup>
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    CreditCard,
    Sparkles,
    Check,
    CheckCircle2,
    Clock,
    ShieldCheck,
    Building2,
    Users,
    Zap,
    ExternalLink,
    AlertCircle,
    ArrowRight,
    Lock,
    X,
    Globe
} from 'lucide-vue-next';

const props = defineProps({
    summary: Object,
    plans: Array,
    paymentConfig: Object,
    transactions: Array,
    annualDiscountBadge: {
        type: String,
        default: 'SAVE 25%',
    },
});

const isAnnual = ref(true);
const selectedPlan = ref(null);
const isCheckoutModalOpen = ref(false);
const activePaymentGateway = ref('stripe');
const isProcessing = ref(false);
const errorMessage = ref('');

const manualForm = useForm({
    plan_slug: '',
    billing_cycle: 'monthly',
    transaction_id: '',
    notes: '',
});

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const planParam = urlParams.get('plan');
    const billingParam = urlParams.get('billing') || urlParams.get('billing_cycle');

    if (billingParam === 'monthly') {
        isAnnual.value = false;
    } else if (billingParam === 'yearly' || billingParam === 'annual') {
        isAnnual.value = true;
    }

    if (planParam && Array.isArray(props.plans)) {
        const targetPlan = props.plans.find(p => 
            p.slug === planParam || 
            p.name?.toLowerCase().includes(planParam.toLowerCase())
        );

        if (targetPlan) {
            openCheckout(targetPlan);
        }
    }
});

const openCheckout = (plan) => {
    selectedPlan.value = plan;
    errorMessage.value = '';
    
    // Choose default available gateway
    if (props.paymentConfig?.stripe?.enabled) {
        activePaymentGateway.value = 'stripe';
    } else if (props.paymentConfig?.paypal?.enabled) {
        activePaymentGateway.value = 'paypal';
    } else if (props.paymentConfig?.razorpay?.enabled) {
        activePaymentGateway.value = 'razorpay';
    } else {
        activePaymentGateway.value = 'bank_transfer';
    }
    
    manualForm.plan_slug = plan.slug;
    manualForm.billing_cycle = isAnnual.value ? 'yearly' : 'monthly';
    isCheckoutModalOpen.value = true;
};

const getCalculatedPrice = (plan) => {
    if (!plan) return 0;
    return isAnnual.value ? Number(plan.annual_price) : Number(plan.monthly_price);
};

const triggerStripeCheckout = async () => {
    isProcessing.value = true;
    errorMessage.value = '';
    
    try {
        const response = await axios.post(route('subscription.stripe.checkout'), {
            plan_slug: selectedPlan.value.slug,
            billing_cycle: isAnnual.value ? 'yearly' : 'monthly',
        });
        
        if (response.data.url) {
            window.location.href = response.data.url;
        } else {
            errorMessage.value = 'Could not generate Stripe Checkout URL.';
            isProcessing.value = false;
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Failed to initiate Stripe Checkout.';
        isProcessing.value = false;
    }
};

const triggerPayPalCheckout = async () => {
    isProcessing.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('subscription.paypal.checkout'), {
            plan_slug: selectedPlan.value.slug,
            billing_cycle: isAnnual.value ? 'yearly' : 'monthly',
        });

        if (response.data.approve_url) {
            window.location.href = response.data.approve_url;
        } else {
            errorMessage.value = 'Could not retrieve PayPal approval URL.';
            isProcessing.value = false;
        }
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Failed to initialize PayPal order.';
        isProcessing.value = false;
    }
};

const triggerRazorpayCheckout = async () => {
    isProcessing.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('subscription.razorpay.order'), {
            plan_slug: selectedPlan.value.slug,
            billing_cycle: isAnnual.value ? 'yearly' : 'monthly',
        });

        if (!window.Razorpay) {
            const script = document.createElement('script');
            script.src = 'https://checkout.razorpay.com/v1/checkout.js';
            script.async = true;
            document.body.appendChild(script);
            await new Promise((resolve) => { script.onload = resolve; });
        }

        const options = {
            key: props.paymentConfig.razorpay.key_id,
            amount: response.data.order.amount,
            currency: response.data.order.currency,
            name: 'Doc Helper AI',
            description: `${selectedPlan.value.name} Subscription`,
            order_id: response.data.order.id,
            prefill: {
                name: response.data.user.name,
                email: response.data.user.email,
                contact: response.data.user.phone || '',
            },
            theme: {
                color: '#0d9488',
            },
            handler: function (razorpayResponse) {
                router.post(route('subscription.razorpay.verify'), {
                    razorpay_order_id: razorpayResponse.razorpay_order_id,
                    razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                    razorpay_signature: razorpayResponse.razorpay_signature,
                    plan_slug: selectedPlan.value.slug,
                    billing_cycle: isAnnual.value ? 'yearly' : 'monthly',
                }, {
                    onSuccess: () => {
                        isCheckoutModalOpen.value = false;
                        isProcessing.value = false;
                    }
                });
            },
            modal: {
                ondismiss: function() {
                    isProcessing.value = false;
                }
            }
        };

        const rzp = new window.Razorpay(options);
        rzp.open();
    } catch (err) {
        errorMessage.value = err.response?.data?.message || 'Failed to initialize Razorpay payment.';
        isProcessing.value = false;
    }
};

const submitManualPayment = () => {
    manualForm.plan_slug = selectedPlan.value.slug;
    manualForm.billing_cycle = isAnnual.value ? 'yearly' : 'monthly';
    manualForm.post(route('subscription.manual-payment'), {
        preserveScroll: true,
        onSuccess: () => {
            isCheckoutModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Billing & Subscription Plans" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-8 pb-20">
            <!-- Header Banner -->
            <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-r from-teal-700 via-teal-600 to-cyan-700 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10 max-w-3xl space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-xs text-xs font-bold uppercase tracking-wider">
                        <Sparkles class="w-3.5 h-3.5" />
                        <span>SaaS Practice Subscription</span>
                    </div>
                    <h1 class="text-2xl sm:text-4xl font-black tracking-tight">
                        Subscription Plans & Limits
                    </h1>
                    <p class="text-sm text-teal-100 leading-relaxed">
                        Upgrade chambers, staff capacities, and unlock automated monthly or annual billing.
                    </p>
                </div>
            </div>

            <!-- Current Plan Status Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Status & Days -->
                <div class="p-6 rounded-3xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Current Plan</span>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-black uppercase tracking-wider"
                            :class="[
                                summary.status === 'active' ? 'bg-emerald-100 text-emerald-800' :
                                summary.status === 'trialing' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'
                            ]"
                        >
                            {{ summary.status }}
                        </span>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white capitalize">
                            {{ summary.plan.replace('_', ' ') }}
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            <template v-if="summary.status === 'trialing'">
                                Free Trial active with <strong class="text-amber-600 font-mono">{{ summary.days_remaining }} days</strong> left.
                            </template>
                            <template v-else-if="summary.status === 'active'">
                                Active subscription valid for <strong class="text-emerald-600 font-mono">{{ summary.days_remaining }} days</strong>.
                            </template>
                            <template v-else>
                                Subscription expired. Please renew to continue.
                            </template>
                        </p>
                    </div>
                </div>

                <!-- Chamber Usage -->
                <div class="p-6 rounded-3xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] shadow-sm space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <Building2 class="w-4 h-4 text-teal-600" />
                            Chamber Capacity
                        </span>
                        <span class="font-bold font-mono text-slate-700 dark:text-slate-300">
                            {{ summary.usage.chambers.current }} / {{ summary.usage.chambers.is_unlimited ? 'Unlimited' : summary.usage.chambers.max }}
                        </span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 dark:bg-[#21262D] rounded-full overflow-hidden">
                        <div
                            class="h-full bg-teal-500 rounded-full transition-all"
                            :style="{ width: summary.usage.chambers.is_unlimited ? '25%' : `${Math.min(100, (summary.usage.chambers.current / summary.usage.chambers.max) * 100)}%` }"
                        ></div>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        {{ summary.usage.chambers.can_create ? 'You can add more chambers.' : 'Chamber limit reached. Upgrade to Pro for unlimited chambers.' }}
                    </p>
                </div>

                <!-- Staff Usage -->
                <div class="p-6 rounded-3xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] shadow-sm space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <Users class="w-4 h-4 text-indigo-600" />
                            Staff Accounts
                        </span>
                        <span class="font-bold font-mono text-slate-700 dark:text-slate-300">
                            {{ summary.usage.staff.current }} / {{ summary.usage.staff.is_unlimited ? 'Unlimited' : summary.usage.staff.max }}
                        </span>
                    </div>
                    <div class="w-full h-2 bg-slate-100 dark:bg-[#21262D] rounded-full overflow-hidden">
                        <div
                            class="h-full bg-indigo-500 rounded-full transition-all"
                            :style="{ width: summary.usage.staff.is_unlimited ? '25%' : `${summary.usage.staff.max > 0 ? (summary.usage.staff.current / summary.usage.staff.max) * 100 : 0}%` }"
                        ></div>
                    </div>
                    <p class="text-[11px] text-slate-500">
                        {{ summary.usage.staff.can_create ? 'You can create more staff members.' : (summary.usage.staff.max === 0 ? 'Staff accounts locked on Solo plan.' : 'Staff limit reached for Pro plan.') }}
                    </p>
                </div>
            </div>

            <!-- Billing Toggle -->
            <div class="flex flex-col items-center justify-center space-y-3 pt-4">
                <div class="inline-flex items-center bg-slate-100 dark:bg-[#161B22] p-1.5 rounded-2xl border border-slate-200 dark:border-[#21262D]">
                    <button
                        type="button"
                        @click="isAnnual = false"
                        :class="[
                            'px-5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
                            !isAnnual ? 'bg-white dark:bg-[#21262D] text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-900'
                        ]"
                    >
                        Monthly Billing
                    </button>
                    <button
                        type="button"
                        @click="isAnnual = true"
                        :class="[
                            'px-5 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer flex items-center gap-2',
                            isAnnual ? 'bg-teal-600 text-white shadow-xs' : 'text-slate-500 hover:text-slate-900'
                        ]"
                    >
                        <span>Annual Billing</span>
                        <span class="text-[10px] bg-teal-800 text-teal-100 px-2 py-0.5 rounded-md font-black uppercase">
                            SAVE 25%
                        </span>
                    </button>
                </div>
            </div>

            <!-- Pricing Plans Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch pt-2">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    class="relative rounded-3xl p-8 flex flex-col justify-between transition-all duration-300 border"
                    :class="[
                        plan.is_featured
                            ? 'bg-white dark:bg-[#161B22] border-2 border-teal-500 shadow-xl lg:-translate-y-2'
                            : 'bg-white dark:bg-[#161B22] border-slate-200 dark:border-[#21262D] shadow-sm'
                    ]"
                >
                    <!-- Featured Badge -->
                    <div
                        v-if="plan.badge_text"
                        class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-teal-600 text-white text-[10px] font-black tracking-wider uppercase py-1 px-4 rounded-full shadow-md"
                    >
                        {{ plan.badge_text }}
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ plan.name }}</h3>
                            <Sparkles v-if="plan.is_featured" class="w-5 h-5 text-teal-600" />
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 min-h-[32px] leading-relaxed">
                            {{ plan.tagline }}
                        </p>

                        <!-- Price -->
                        <div class="pt-2 pb-4 border-b border-slate-100 dark:border-[#21262D]">
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-slate-900 dark:text-white font-mono">
                                    {{ plan.currency }}{{ isAnnual ? Math.round(plan.annual_price / 12) : plan.monthly_price }}
                                </span>
                                <span class="text-xs text-slate-400 font-medium">/ doctor / month</span>
                            </div>
                            <p class="text-xs text-teal-600 dark:text-teal-400 font-bold mt-1">
                                {{ isAnnual ? plan.annual_discount_text : 'Billed monthly' }}
                            </p>
                        </div>

                        <!-- Features -->
                        <ul class="space-y-3 pt-2 text-xs text-slate-700 dark:text-slate-300">
                            <li
                                v-for="(feat, idx) in plan.features"
                                :key="idx"
                                class="flex items-start gap-2.5"
                            >
                                <Check class="w-4 h-4 text-teal-600 dark:text-teal-400 shrink-0 mt-0.5" />
                                <span>{{ feat }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-6 border-t border-slate-100 dark:border-[#21262D]">
                        <button
                            type="button"
                            @click="openCheckout(plan)"
                            class="w-full py-3.5 px-4 rounded-xl font-bold text-xs shadow-md transition-all cursor-pointer flex items-center justify-center gap-2"
                            :class="[
                                plan.is_featured
                                    ? 'bg-teal-600 hover:bg-teal-700 text-white'
                                    : 'bg-slate-100 dark:bg-[#21262D] hover:bg-slate-200 text-slate-900 dark:text-white'
                            ]"
                        >
                            <span>{{ summary.plan === plan.slug && summary.is_active ? 'Current Active Plan' : 'Select ' + plan.name }}</span>
                            <ArrowRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transaction History Table -->
            <div v-if="transactions && transactions.length" class="bg-white dark:bg-[#161B22] p-6 rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Billing & Payment History</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-[#21262D] text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Plan</th>
                                <th class="p-3">Billing</th>
                                <th class="p-3">Amount</th>
                                <th class="p-3">Gateway</th>
                                <th class="p-3">Transaction ID</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr v-for="tx in transactions" :key="tx.id">
                                <td class="p-3 font-bold text-slate-900 dark:text-white">{{ tx.plan_name }}</td>
                                <td class="p-3 capitalize">{{ tx.billing_cycle }}</td>
                                <td class="p-3 font-mono font-bold">{{ tx.amount }} {{ tx.currency }}</td>
                                <td class="p-3 capitalize">{{ tx.gateway.replace('_', ' ') }}</td>
                                <td class="p-3 font-mono text-[11px]">{{ tx.transaction_id || 'N/A' }}</td>
                                <td class="p-3">
                                    <span
                                        class="px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                        :class="tx.payment_status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                    >
                                        {{ tx.payment_status }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-500">{{ new Date(tx.created_at).toLocaleDateString() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Checkout / Payment Modal -->
        <div
            v-if="isCheckoutModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
        >
            <div class="relative w-full max-w-lg bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200 dark:border-[#30363D] shadow-2xl p-6 space-y-5 my-8">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                            Checkout: {{ selectedPlan?.name }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ isAnnual ? 'Annual Billing' : 'Monthly Billing' }} • Total: <strong class="text-teal-600 font-mono">{{ selectedPlan?.currency }}{{ getCalculatedPrice(selectedPlan) }}</strong>
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="isCheckoutModalOpen = false"
                        class="text-slate-400 hover:text-slate-600 cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="p-3 rounded-xl bg-rose-50 text-rose-800 text-xs font-semibold flex items-center gap-2">
                    <AlertCircle class="w-4 h-4 shrink-0" />
                    <span>{{ errorMessage }}</span>
                </div>

                <!-- Gateway Selection Tabs -->
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Choose International Payment Gateway
                    </label>

                    <div class="grid grid-cols-2 gap-2.5">
                        <button
                            v-if="paymentConfig?.stripe?.enabled"
                            type="button"
                            @click="activePaymentGateway = 'stripe'"
                            :class="[
                                'p-3 rounded-xl border text-xs font-bold flex items-center gap-2 transition cursor-pointer',
                                activePaymentGateway === 'stripe' ? 'border-teal-500 bg-teal-50/50 text-teal-900 dark:bg-teal-950/30 dark:text-teal-200 shadow-xs' : 'border-slate-200 hover:bg-slate-50'
                            ]"
                        >
                            <CreditCard class="w-4 h-4 text-teal-600" />
                            <span>Stripe (Cards)</span>
                        </button>

                        <button
                            v-if="paymentConfig?.paypal?.enabled"
                            type="button"
                            @click="activePaymentGateway = 'paypal'"
                            :class="[
                                'p-3 rounded-xl border text-xs font-bold flex items-center gap-2 transition cursor-pointer',
                                activePaymentGateway === 'paypal' ? 'border-teal-500 bg-teal-50/50 text-teal-900 dark:bg-teal-950/30 dark:text-teal-200 shadow-xs' : 'border-slate-200 hover:bg-slate-50'
                            ]"
                        >
                            <Globe class="w-4 h-4 text-sky-600" />
                            <span>PayPal</span>
                        </button>

                        <button
                            v-if="paymentConfig?.razorpay?.enabled"
                            type="button"
                            @click="activePaymentGateway = 'razorpay'"
                            :class="[
                                'p-3 rounded-xl border text-xs font-bold flex items-center gap-2 transition cursor-pointer',
                                activePaymentGateway === 'razorpay' ? 'border-teal-500 bg-teal-50/50 text-teal-900 dark:bg-teal-950/30 dark:text-teal-200 shadow-xs' : 'border-slate-200 hover:bg-slate-50'
                            ]"
                        >
                            <Zap class="w-4 h-4 text-blue-600" />
                            <span>Razorpay</span>
                        </button>

                        <button
                            v-if="paymentConfig?.bank_transfer?.enabled"
                            type="button"
                            @click="activePaymentGateway = 'bank_transfer'"
                            :class="[
                                'p-3 rounded-xl border text-xs font-bold flex items-center gap-2 transition cursor-pointer',
                                activePaymentGateway === 'bank_transfer' ? 'border-teal-500 bg-teal-50/50 text-teal-900 dark:bg-teal-950/30 dark:text-teal-200 shadow-xs' : 'border-slate-200 hover:bg-slate-50'
                            ]"
                        >
                            <Building2 class="w-4 h-4 text-emerald-600" />
                            <span>Bank Wire Transfer</span>
                        </button>
                    </div>
                </div>

                <!-- Stripe Checkout Action -->
                <div v-if="activePaymentGateway === 'stripe'" class="p-4 rounded-2xl bg-slate-50 dark:bg-[#21262D] space-y-3">
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        You will be redirected to the secure Stripe Checkout session to pay with Visa, Mastercard, AMEX, or Discover.
                    </p>
                    <button
                        type="button"
                        @click="triggerStripeCheckout"
                        :disabled="isProcessing"
                        class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer shadow-md"
                    >
                        <Lock class="w-3.5 h-3.5" />
                        <span>{{ isProcessing ? 'Redirecting to Stripe...' : `Pay ${selectedPlan?.currency}${getCalculatedPrice(selectedPlan)} with Stripe` }}</span>
                    </button>
                </div>

                <!-- PayPal Checkout Action -->
                <div v-if="activePaymentGateway === 'paypal'" class="p-4 rounded-2xl bg-slate-50 dark:bg-[#21262D] space-y-3">
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Pay securely using your PayPal Account balance or PayPal Guest Checkout cards.
                    </p>
                    <button
                        type="button"
                        @click="triggerPayPalCheckout"
                        :disabled="isProcessing"
                        class="w-full py-3 bg-[#0070BA] hover:bg-[#003087] text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer shadow-md"
                    >
                        <Globe class="w-3.5 h-3.5" />
                        <span>{{ isProcessing ? 'Connecting to PayPal...' : `Pay ${selectedPlan?.currency}${getCalculatedPrice(selectedPlan)} with PayPal` }}</span>
                    </button>
                </div>

                <!-- Razorpay Checkout Action -->
                <div v-if="activePaymentGateway === 'razorpay'" class="p-4 rounded-2xl bg-slate-50 dark:bg-[#21262D] space-y-3">
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Complete payment via Razorpay popup using UPI, International Cards, or Netbanking.
                    </p>
                    <button
                        type="button"
                        @click="triggerRazorpayCheckout"
                        :disabled="isProcessing"
                        class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer shadow-md"
                    >
                        <Zap class="w-3.5 h-3.5" />
                        <span>{{ isProcessing ? 'Launching Razorpay...' : `Pay ${selectedPlan?.currency}${getCalculatedPrice(selectedPlan)} with Razorpay` }}</span>
                    </button>
                </div>

                <!-- Bank Wire Transfer Submission -->
                <div v-if="activePaymentGateway === 'bank_transfer'" class="p-4 rounded-2xl bg-slate-50 dark:bg-[#21262D] space-y-3">
                    <div class="p-3 rounded-xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] text-xs text-slate-700 dark:text-slate-300">
                        <strong>Bank Wire Instructions:</strong>
                        <p class="mt-1 whitespace-pre-line">{{ paymentConfig?.bank_transfer?.instructions }}</p>
                    </div>

                    <form @submit.prevent="submitManualPayment" class="space-y-3 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Bank Wire Reference / Transaction ID *
                            </label>
                            <input
                                v-model="manualForm.transaction_id"
                                required
                                placeholder="e.g. WT-9847291 or Bank Ref Number"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white font-mono"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Sender Account / Notes
                            </label>
                            <input
                                v-model="manualForm.notes"
                                placeholder="e.g. Transferred from Citibank Account #XXXX"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="manualForm.processing"
                            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl cursor-pointer shadow-md disabled:opacity-50"
                        >
                            <span>{{ manualForm.processing ? 'Submitting...' : 'Submit Wire Reference for Approval' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>