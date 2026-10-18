<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Mail,
    Send,
    Lock,
    Eye,
    EyeOff,
    CheckCircle2,
    AlertCircle,
    Server,
    Shield,
    HelpCircle,
    Sparkles,
    Check,
    Zap,
    ExternalLink
} from 'lucide-vue-next';

const props = defineProps({
    settings: Object,
    status: String,
    error: String,
});

const page = usePage();
const showPassword = ref(false);

const form = useForm({
    mail_mailer: props.settings.mail_mailer || 'smtp',
    mail_host: props.settings.mail_host || '',
    mail_port: props.settings.mail_port || '587',
    mail_username: props.settings.mail_username || '',
    mail_password: props.settings.mail_password || '',
    mail_encryption: props.settings.mail_encryption || 'tls',
    mail_from_address: props.settings.mail_from_address || '',
    mail_from_name: props.settings.mail_from_name || 'Doc Helper AI',
});

const testForm = useForm({
    test_email: page.props.auth?.user?.email || '',
});

const submitSettings = () => {
    form.post(route('admin.mail-settings.update'), {
        preserveScroll: true,
    });
};

const sendTestMail = () => {
    testForm.post(route('admin.mail-settings.test'), {
        preserveScroll: true,
    });
};

const applyPreset = (preset) => {
    if (preset === 'gmail') {
        form.mail_mailer = 'smtp';
        form.mail_host = 'smtp.gmail.com';
        form.mail_port = '587';
        form.mail_encryption = 'tls';
    } else if (preset === 'cpanel_ssl') {
        form.mail_mailer = 'smtp';
        form.mail_port = '465';
        form.mail_encryption = 'ssl';
    } else if (preset === 'sendgrid') {
        form.mail_mailer = 'smtp';
        form.mail_host = 'smtp.sendgrid.net';
        form.mail_port = '587';
        form.mail_username = 'apikey';
        form.mail_encryption = 'tls';
    } else if (preset === 'mailgun') {
        form.mail_mailer = 'smtp';
        form.mail_host = 'smtp.mailgun.org';
        form.mail_port = '587';
        form.mail_encryption = 'tls';
    } else if (preset === 'mailtrap') {
        form.mail_mailer = 'smtp';
        form.mail_host = 'sandbox.smtp.mailtrap.io';
        form.mail_port = '2525';
        form.mail_encryption = 'tls';
    }
};
</script>

<template>
    <Head title="Super Admin - SMTP & Email Settings" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto space-y-6 pb-12">
            <!-- Header Banner -->
            <div class="p-6 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#21262D] shadow-xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20 shrink-0">
                        <Mail class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">
                                SMTP & Email Configuration
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-teal-100 dark:bg-teal-900/60 text-teal-800 dark:text-teal-200 border border-teal-200 dark:border-teal-800">
                                Super Admin
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-400 font-medium mt-0.5">
                            Configure SMTP mail server for doctor password reset emails, onboarding notices, and automated reports.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Status Flash Alerts -->
            <div
                v-if="status || $page.props.flash?.status"
                class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-xs font-bold flex items-center gap-2.5 shadow-2xs"
            >
                <CheckCircle2 class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <span>{{ status || $page.props.flash?.status }}</span>
            </div>

            <div
                v-if="error || $page.props.flash?.error"
                class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200 text-xs font-bold flex items-start gap-2.5 shadow-2xs"
            >
                <AlertCircle class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0 mt-0.5" />
                <div class="space-y-1">
                    <p class="font-black">Error Occurred:</p>
                    <p class="font-normal">{{ error || $page.props.flash?.error }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form (Left 2 cols) -->
                <div class="lg:col-span-2 space-y-6">
                    <form @submit.prevent="submitSettings" class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200 dark:border-[#21262D] p-6 shadow-xs space-y-6">
                        <!-- Provider 1-Click Autofill Pills -->
                        <div>
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                                Quick Autofill Provider Presets:
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    @click="applyPreset('gmail')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-[#21262D] hover:bg-teal-50 dark:hover:bg-teal-950/40 hover:text-teal-700 dark:hover:text-teal-300 border border-slate-200 dark:border-[#30363D] transition cursor-pointer"
                                >
                                    Gmail / Google Workspace
                                </button>
                                <button
                                    type="button"
                                    @click="applyPreset('cpanel_ssl')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-[#21262D] hover:bg-teal-50 dark:hover:bg-teal-950/40 hover:text-teal-700 dark:hover:text-teal-300 border border-slate-200 dark:border-[#30363D] transition cursor-pointer"
                                >
                                    cPanel Webmail (SSL 465)
                                </button>
                                <button
                                    type="button"
                                    @click="applyPreset('sendgrid')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-[#21262D] hover:bg-teal-50 dark:hover:bg-teal-950/40 hover:text-teal-700 dark:hover:text-teal-300 border border-slate-200 dark:border-[#30363D] transition cursor-pointer"
                                >
                                    SendGrid
                                </button>
                                <button
                                    type="button"
                                    @click="applyPreset('mailgun')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-[#21262D] hover:bg-teal-50 dark:hover:bg-teal-950/40 hover:text-teal-700 dark:hover:text-teal-300 border border-slate-200 dark:border-[#30363D] transition cursor-pointer"
                                >
                                    Mailgun
                                </button>
                                <button
                                    type="button"
                                    @click="applyPreset('mailtrap')"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-[#21262D] hover:bg-teal-50 dark:hover:bg-teal-950/40 hover:text-teal-700 dark:hover:text-teal-300 border border-slate-200 dark:border-[#30363D] transition cursor-pointer"
                                >
                                    Mailtrap (Test Sandbox)
                                </button>
                            </div>
                        </div>

                        <hr class="border-slate-100 dark:border-[#21262D]" />

                        <!-- Form Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <!-- Mail Driver -->
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Mail Driver / Mailer <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    v-model="form.mail_mailer"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                >
                                    <option value="smtp">SMTP (Recommended)</option>
                                    <option value="sendmail">Sendmail</option>
                                    <option value="log">Log (For Debugging / No Real Emails)</option>
                                </select>
                            </div>

                            <!-- Mail Encryption -->
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Mail Encryption <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    v-model="form.mail_encryption"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                >
                                    <option value="tls">TLS (Port 587)</option>
                                    <option value="ssl">SSL (Port 465)</option>
                                    <option value="starttls">STARTTLS</option>
                                    <option value="null">None (Non-SSL Port 25)</option>
                                </select>
                            </div>

                            <!-- Mail Host -->
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Mail Host / Server <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="form.mail_host"
                                    placeholder="e.g. smtp.gmail.com or mail.yourdomain.com"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    required
                                />
                            </div>

                            <!-- Mail Port -->
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Mail Port <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    v-model="form.mail_port"
                                    placeholder="587 / 465 / 25"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    required
                                />
                            </div>

                            <!-- Mail Username -->
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    SMTP Username / Email
                                </label>
                                <input
                                    type="text"
                                    v-model="form.mail_username"
                                    placeholder="your-email@gmail.com"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                />
                            </div>

                            <!-- Mail Password -->
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    SMTP Password / App Password
                                </label>
                                <div class="relative">
                                    <input
                                        :type="showPassword ? 'text' : 'password'"
                                        v-model="form.mail_password"
                                        placeholder="••••••••••••••••"
                                        class="w-full px-3.5 py-2.5 pr-10 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                                    >
                                        <EyeOff v-if="showPassword" class="w-4 h-4" />
                                        <Eye v-else class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Sender From Email -->
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Sender From Address <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    v-model="form.mail_from_address"
                                    placeholder="noreply@dochelper.ai"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    required
                                />
                            </div>

                            <!-- Sender From Name -->
                            <div>
                                <label class="block font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Sender From Name <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="form.mail_from_name"
                                    placeholder="Doc Helper AI"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                                    required
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-[#21262D]">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-600/20 transition cursor-pointer"
                            >
                                <Check class="w-4 h-4" />
                                <span>{{ form.processing ? 'Saving Settings...' : 'Save SMTP Settings' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Instant Test Email & Instructions -->
                <div class="space-y-6">
                    <!-- Send Test Email Card -->
                    <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200 dark:border-[#21262D] p-5 shadow-xs space-y-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                                <Send class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Test SMTP Delivery
                                </h3>
                                <p class="text-[11px] text-slate-500">Send an instant test email to verify connection.</p>
                            </div>
                        </div>

                        <form @submit.prevent="sendTestMail" class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                    Recipient Email Address
                                </label>
                                <input
                                    type="email"
                                    v-model="testForm.test_email"
                                    placeholder="your-personal@email.com"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-[#30363D] bg-white dark:bg-[#0D1117] text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                                    required
                                />
                            </div>

                            <button
                                type="submit"
                                :disabled="testForm.processing"
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 font-bold text-xs flex items-center justify-center gap-2 transition cursor-pointer shadow-xs"
                            >
                                <Send class="w-3.5 h-3.5" :class="{ 'animate-bounce': testForm.processing }" />
                                <span>{{ testForm.processing ? 'Sending Test Mail...' : 'Send Verification Test Email' }}</span>
                            </button>
                        </form>
                    </div>

                    <!-- Setup Guide Box -->
                    <div class="bg-slate-50 dark:bg-[#1C2128] rounded-2xl border border-slate-200 dark:border-[#30363D] p-5 text-xs space-y-3">
                        <div class="flex items-center gap-2 text-teal-800 dark:text-teal-300 font-bold">
                            <HelpCircle class="w-4 h-4" />
                            <span>Quick Setup Instructions:</span>
                        </div>

                        <div class="space-y-2 text-slate-700 dark:text-slate-300 leading-relaxed">
                            <p class="font-bold text-slate-900 dark:text-white">📧 If using Gmail SMTP:</p>
                            <ol class="list-decimal list-inside space-y-1 pl-1 text-[11px]">
                                <li>Enable 2-Step Verification in Google Account.</li>
                                <li>Create an <strong>App Password</strong> under Security.</li>
                                <li>Paste the 16-digit App Password in the Password field above.</li>
                            </ol>

                            <p class="font-bold text-slate-900 dark:text-white pt-2">🌐 If using cPanel Webmail:</p>
                            <ul class="list-disc list-inside space-y-1 pl-1 text-[11px]">
                                <li>Host: <code class="bg-white dark:bg-[#0D1117] px-1 py-0.5 rounded border border-slate-200 dark:border-[#30363D]">mail.yourdomain.com</code></li>
                                <li>Port: <code class="bg-white dark:bg-[#0D1117] px-1 py-0.5 rounded border border-slate-200 dark:border-[#30363D]">465 (SSL)</code> or <code class="bg-white dark:bg-[#0D1117] px-1 py-0.5 rounded border border-slate-200 dark:border-[#30363D]">587 (TLS)</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>