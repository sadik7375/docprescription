<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    CreditCard,
    Save,
    CheckCircle2,
    Zap,
    Building2,
    Globe,
    ShieldCheck,
    Check
} from 'lucide-vue-next';

const props = defineProps({
    settings: Object,
    transactions: Object,
});

const form = useForm({
    stripe_enabled: !!props.settings.stripe_enabled,
    stripe_publishable_key: props.settings.stripe_publishable_key || '',
    stripe_secret_key: props.settings.stripe_secret_key || '',

    paypal_enabled: !!props.settings.paypal_enabled,
    paypal_client_id: props.settings.paypal_client_id || '',
    paypal_secret: props.settings.paypal_secret || '',
    paypal_mode: props.settings.paypal_mode || 'sandbox',

    razorpay_enabled: !!props.settings.razorpay_enabled,
    razorpay_key_id: props.settings.razorpay_key_id || '',
    razorpay_key_secret: props.settings.razorpay_key_secret || '',

    bank_transfer_enabled: !!props.settings.bank_transfer_enabled,
    bank_transfer_instructions: props.settings.bank_transfer_instructions || '',
});

const submitSettings = () => {
    form.post(route('admin.payment-settings.update'), {
        preserveScroll: true,
    });
};

const approveTx = (tx) => {
    if (confirm(`Approve wire transfer for ${tx.user?.name} (${tx.plan_name})?`)) {
        router.post(route('admin.payment-settings.approve', tx.id), {}, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Payment Gateways Configuration - Super Admin" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-8 pb-16">
            <!-- Header -->
            <div class="bg-white dark:bg-[#161B22] p-5 rounded-2xl border border-slate-200 dark:border-[#21262D] shadow-xs flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <CreditCard class="w-5 h-5 text-teal-600" />
                        <span>Payment Gateways & SaaS Invoices</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Configure international payment gateways (Stripe, PayPal, Razorpay) with sandbox/live credentials for CodeCanyon deployment.
                    </p>
                </div>
            </div>

            <!-- Flash Success Message -->
            <div v-if="$page.props.flash?.success" class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>{{ $page.props.flash.success }}</span>
            </div>

            <form @submit.prevent="submitSettings" class="space-y-6">
                <!-- Gateway 1: Stripe -->
                <div class="bg-white dark:bg-[#161B22] p-6 rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#21262D]">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                                <CreditCard class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Stripe Checkout</h3>
                                <p class="text-xs text-slate-500">Worldwide Credit & Debit card processing.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.stripe_enabled" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Enable Stripe</span>
                        </label>
                    </div>

                    <div v-if="form.stripe_enabled" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Stripe Publishable Key (pk_test_... or pk_live_...)
                            </label>
                            <input
                                v-model="form.stripe_publishable_key"
                                placeholder="pk_test_..."
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Stripe Secret Key (sk_test_... or sk_live_...)
                            </label>
                            <input
                                type="password"
                                v-model="form.stripe_secret_key"
                                placeholder="sk_test_..."
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Gateway 2: PayPal -->
                <div class="bg-white dark:bg-[#161B22] p-6 rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#21262D]">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                                <Globe class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">PayPal REST API (v2)</h3>
                                <p class="text-xs text-slate-500">PayPal Wallets and international card checkout.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.paypal_enabled" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Enable PayPal</span>
                        </label>
                    </div>

                    <div v-if="form.paypal_enabled" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Environment Mode
                            </label>
                            <select
                                v-model="form.paypal_mode"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-bold text-slate-900 dark:text-white"
                            >
                                <option value="sandbox">Sandbox (Testing)</option>
                                <option value="live">Live (Production)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                PayPal Client ID
                            </label>
                            <input
                                v-model="form.paypal_client_id"
                                placeholder="Client ID"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                PayPal Client Secret
                            </label>
                            <input
                                type="password"
                                v-model="form.paypal_secret"
                                placeholder="Client Secret"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Gateway 3: Razorpay -->
                <div class="bg-white dark:bg-[#161B22] p-6 rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#21262D]">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <Zap class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Razorpay Standard</h3>
                                <p class="text-xs text-slate-500">Cards, UPI, Netbanking & Wallets.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.razorpay_enabled" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Enable Razorpay</span>
                        </label>
                    </div>

                    <div v-if="form.razorpay_enabled" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Razorpay Key ID (rzp_test_... or rzp_live_...)
                            </label>
                            <input
                                v-model="form.razorpay_key_id"
                                placeholder="rzp_test_..."
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Razorpay Key Secret
                            </label>
                            <input
                                type="password"
                                v-model="form.razorpay_key_secret"
                                placeholder="Key Secret"
                                class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl font-mono text-slate-900 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Gateway 4: Bank Wire Transfer (Offline) -->
                <div class="bg-white dark:bg-[#161B22] p-6 rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-sm space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-[#21262D]">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <Building2 class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Direct Bank Wire Transfer</h3>
                                <p class="text-xs text-slate-500">Allows doctors to submit wire reference numbers for manual admin verification.</p>
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" v-model="form.bank_transfer_enabled" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Enable Bank Wire</span>
                        </label>
                    </div>

                    <div v-if="form.bank_transfer_enabled">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Bank Wire Instructions & SWIFT Details
                        </label>
                        <textarea
                            v-model="form.bank_transfer_instructions"
                            rows="4"
                            placeholder="Please wire funds to Bank: XXX, Account: XXX, SWIFT: XXX..."
                            class="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white"
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold rounded-xl shadow-md cursor-pointer disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>Save Payment Settings</span>
                    </button>
                </div>
            </form>

            <!-- All Subscription Invoices & Transactions -->
            <div class="bg-white dark:bg-[#161B22] p-6 rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">All Doctor Subscription Transactions</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-[#21262D] text-slate-500 font-bold uppercase tracking-wider">
                            <tr>
                                <th class="p-3">Doctor</th>
                                <th class="p-3">Plan</th>
                                <th class="p-3">Billing</th>
                                <th class="p-3">Amount</th>
                                <th class="p-3">Gateway</th>
                                <th class="p-3">Trx ID</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr v-for="tx in transactions.data" :key="tx.id">
                                <td class="p-3 font-bold text-slate-900 dark:text-white">
                                    {{ tx.user?.name }}
                                    <div class="text-[10px] text-slate-400">{{ tx.user?.email }}</div>
                                </td>
                                <td class="p-3 font-bold">{{ tx.plan_name }}</td>
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
                                <td class="p-3">
                                    <button
                                        v-if="tx.payment_status === 'pending'"
                                        type="button"
                                        @click="approveTx(tx)"
                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold cursor-pointer"
                                    >
                                        Approve & Activate
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>