<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    LifeBuoy,
    Plus,
    Mail,
    Phone,
    MessageCircle,
    Clock,
    MapPin,
    AlertCircle,
    CheckCircle2,
    Clock3,
    Send,
    Paperclip,
    FileText,
    ExternalLink,
    ChevronRight,
    User,
    ShieldCheck,
    HelpCircle,
    X,
    Filter
} from 'lucide-vue-next';

const props = defineProps({
    tickets: Object,
    supportContacts: Object,
});

// Modal state for creating a ticket
const isCreateModalOpen = ref(false);
const isViewModalOpen = ref(false);
const activeTicket = ref(null);

const createForm = useForm({
    category: 'general',
    priority: 'medium',
    subject: '',
    message: '',
    attachment: null,
});

const replyForm = useForm({
    message: '',
    attachment: null,
});

const openCreateModal = () => {
    createForm.reset();
    isCreateModalOpen.value = true;
};

const submitTicket = () => {
    createForm.post(route('support.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isCreateModalOpen.value = false;
            createForm.reset();
        },
    });
};

const openViewModal = (ticket) => {
    activeTicket.value = ticket;
    replyForm.reset();
    isViewModalOpen.value = true;
};

const submitReply = () => {
    if (!activeTicket.value) return;
    replyForm.post(route('support.reply', activeTicket.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
            // Re-sync active ticket from updated tickets prop
            const updated = props.tickets.data.find(t => t.id === activeTicket.value.id);
            if (updated) {
                activeTicket.value = updated;
            }
        },
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'open':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800';
        case 'in_progress':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800';
        case 'resolved':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800';
        case 'closed':
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700';
        default:
            return 'bg-slate-100 text-slate-800 border-slate-200';
    }
};

const getPriorityBadge = (priority) => {
    switch (priority) {
        case 'urgent':
            return 'text-rose-600 bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-900 font-bold';
        case 'high':
            return 'text-orange-600 bg-orange-50 dark:bg-orange-950/40 border-orange-200 dark:border-orange-900 font-bold';
        case 'medium':
            return 'text-amber-600 bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900 font-semibold';
        default:
            return 'text-slate-600 bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700';
    }
};

const getCategoryLabel = (cat) => {
    const map = {
        prescription_engine: 'Prescription Pad / Rx Builder',
        ai_assistant: 'AI Clinical Assistant',
        billing_subscription: 'Billing & Subscription Plan',
        account_chamber: 'Account & Chamber Setup',
        feature_request: 'Feature Request & Feedback',
        general: 'General Technical Query',
    };
    return map[cat] || cat;
};
</script>

<template>
    <Head title="Doctor Support & Helpdesk" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Header Banner -->
            <div class="bg-white dark:bg-[#161B22] p-6 sm:p-8 rounded-3xl border border-slate-200/80 dark:border-[#21262D] shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20">
                            <LifeBuoy class="w-5 h-5" />
                        </div>
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                            Doctor Support & Helpdesk
                        </h1>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 max-w-2xl font-normal leading-relaxed">
                        Need assistance with prescription building, AI clinical copilot, or chamber configuration? Contact our executive technical support team directly.
                    </p>
                </div>

                <button
                    type="button"
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md shadow-teal-600/20 transition cursor-pointer shrink-0"
                >
                    <Plus class="w-4 h-4" />
                    <span>Open New Support Ticket</span>
                </button>
            </div>

            <!-- Official Support Contact Channels (Configured by Super Admin) -->
            <div class="bg-gradient-to-br from-teal-900 via-slate-900 to-[#0A0D36] text-white p-6 sm:p-8 rounded-3xl shadow-xl relative overflow-hidden space-y-6">
                <div class="relative z-10 flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="w-5 h-5 text-teal-400" />
                        <h3 class="text-sm sm:text-base font-extrabold uppercase tracking-wider text-teal-200">
                            Direct Support & Helpline Channels
                        </h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-teal-500/20 border border-teal-400/30 text-teal-300 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                        Priority Assistance
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative z-10">
                    <!-- Email -->
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10 space-y-1.5 backdrop-blur-xs">
                        <div class="flex items-center gap-2 text-teal-300 text-xs font-bold">
                            <Mail class="w-4 h-4" />
                            <span>Official Support Email</span>
                        </div>
                        <a :href="'mailto:' + supportContacts.email" class="text-sm font-black text-white hover:text-teal-300 transition block truncate">
                            {{ supportContacts.email }}
                        </a>
                        <p class="text-[11px] text-slate-300">24/7 Ticket & Inquiry Inbox</p>
                    </div>

                    <!-- Phone -->
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10 space-y-1.5 backdrop-blur-xs">
                        <div class="flex items-center gap-2 text-teal-300 text-xs font-bold">
                            <Phone class="w-4 h-4" />
                            <span>Helpline & Hotline</span>
                        </div>
                        <a :href="'tel:' + supportContacts.phone" class="text-sm font-black text-white hover:text-teal-300 transition block truncate">
                            {{ supportContacts.phone }}
                        </a>
                        <p class="text-[11px] text-slate-300">Direct Doctor Assistance</p>
                    </div>

                    <!-- WhatsApp -->
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10 space-y-1.5 backdrop-blur-xs">
                        <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold">
                            <MessageCircle class="w-4 h-4" />
                            <span>WhatsApp Live Chat</span>
                        </div>
                        <a
                            v-if="supportContacts.whatsapp"
                            :href="'https://wa.me/' + supportContacts.whatsapp.replace(/[^0-9]/g, '')"
                            target="_blank"
                            class="text-sm font-black text-emerald-300 hover:underline flex items-center gap-1 block truncate"
                        >
                            <span>{{ supportContacts.whatsapp }}</span>
                            <ExternalLink class="w-3 h-3" />
                        </a>
                        <span v-else class="text-xs text-slate-400">Available on phone</span>
                        <p class="text-[11px] text-slate-300">Fast Technical Response</p>
                    </div>

                    <!-- Working Hours -->
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10 space-y-1.5 backdrop-blur-xs">
                        <div class="flex items-center gap-2 text-teal-300 text-xs font-bold">
                            <Clock class="w-4 h-4" />
                            <span>Support Hours</span>
                        </div>
                        <p class="text-xs font-bold text-white leading-snug">
                            {{ supportContacts.hours }}
                        </p>
                        <p class="text-[11px] text-slate-300 truncate" v-if="supportContacts.address">
                            {{ supportContacts.address }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Support Tickets List Card -->
            <div class="bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Your Support Tickets</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Track real-time resolution progress and conversation threads</p>
                    </div>

                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300">
                        {{ tickets.total || 0 }} Total Tickets
                    </span>
                </div>

                <!-- Empty State -->
                <div v-if="!tickets.data || tickets.data.length === 0" class="py-16 text-center space-y-3">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-[#21262D] text-slate-400 flex items-center justify-center mx-auto">
                        <HelpCircle class="w-7 h-7" />
                    </div>
                    <div class="space-y-1 max-w-sm mx-auto">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">No Support Tickets Yet</h3>
                        <p class="text-xs text-slate-500">
                            Have questions or facing an issue? Click "Open New Support Ticket" to get in touch with our technical team.
                        </p>
                    </div>
                </div>

                <!-- Table View -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-[#1C2128] text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 dark:border-[#21262D]">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-6">Ticket #</th>
                                <th class="py-3.5 px-4">Category</th>
                                <th class="py-3.5 px-4">Subject</th>
                                <th class="py-3.5 px-4">Priority</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Last Activity</th>
                                <th class="py-3.5 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr
                                v-for="ticket in tickets.data"
                                :key="ticket.id"
                                class="hover:bg-slate-50/70 dark:hover:bg-[#1C2128]/50 transition cursor-pointer"
                                @click="openViewModal(ticket)"
                            >
                                <td class="py-4 px-4 sm:px-6 font-mono font-bold text-teal-700 dark:text-teal-400">
                                    {{ ticket.ticket_number }}
                                </td>
                                <td class="py-4 px-4 text-slate-700 dark:text-slate-300 font-medium">
                                    {{ getCategoryLabel(ticket.category) }}
                                </td>
                                <td class="py-4 px-4 max-w-xs truncate font-bold text-slate-900 dark:text-white">
                                    {{ ticket.subject }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-0.5 rounded-md text-[11px] uppercase border" :class="getPriorityBadge(ticket.priority)">
                                        {{ ticket.priority }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase border" :class="getStatusBadge(ticket.status)">
                                        {{ ticket.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-slate-500 text-[11px]">
                                    {{ ticket.last_reply_at }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-[#21262D] hover:bg-teal-50 dark:hover:bg-teal-950/40 hover:text-teal-600 text-slate-700 dark:text-slate-200 font-bold text-xs transition"
                                    >
                                        <span>View Thread</span>
                                        <ChevronRight class="w-3.5 h-3.5" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ===================================================================== -->
        <!-- CREATE SUPPORT TICKET MODAL                                           -->
        <!-- ===================================================================== -->
        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] w-full max-w-xl rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-xs">
                            <Plus class="w-4 h-4" />
                        </div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Create New Support Ticket</h3>
                    </div>
                    <button type="button" @click="isCreateModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitTicket" class="p-5 sm:p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Category</label>
                            <select
                                v-model="createForm.category"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-semibold focus:border-teal-500 focus:outline-none"
                            >
                                <option value="prescription_engine">Prescription Pad / Rx Builder</option>
                                <option value="ai_assistant">AI Clinical Assistant</option>
                                <option value="billing_subscription">Billing & Subscription Plan</option>
                                <option value="account_chamber">Account & Chamber Setup</option>
                                <option value="feature_request">Feature Request & Feedback</option>
                                <option value="general">General Technical Query</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Priority</label>
                            <select
                                v-model="createForm.priority"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-semibold focus:border-teal-500 focus:outline-none"
                            >
                                <option value="low">Low - General Question</option>
                                <option value="medium">Medium - Standard Inquiry</option>
                                <option value="high">High - Feature Malfunction</option>
                                <option value="urgent">Urgent - Clinical Workflow Blocked</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Subject</label>
                        <input
                            type="text"
                            v-model="createForm.subject"
                            required
                            placeholder="Brief summary of your query or issue"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs focus:border-teal-500 focus:outline-none"
                        />
                        <p v-if="createForm.errors.subject" class="text-xs text-rose-500 mt-1">{{ createForm.errors.subject }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Message / Details</label>
                        <textarea
                            v-model="createForm.message"
                            rows="4"
                            required
                            placeholder="Describe your issue in detail. Include any error messages or steps to reproduce..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs focus:border-teal-500 focus:outline-none"
                        ></textarea>
                        <p v-if="createForm.errors.message" class="text-xs text-rose-500 mt-1">{{ createForm.errors.message }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Attachment (Optional screenshot / PDF)</label>
                        <input
                            type="file"
                            @input="createForm.attachment = $event.target.files[0]"
                            class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 dark:file:bg-[#21262D] dark:file:text-teal-300"
                        />
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="isCreateModalOpen = false"
                            class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 text-xs font-bold hover:bg-slate-200 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="inline-flex items-center gap-1.5 px-6 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-bold shadow-md transition cursor-pointer"
                        >
                            <Send class="w-3.5 h-3.5" />
                            <span>{{ createForm.processing ? 'Submitting...' : 'Submit Ticket' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- VIEW TICKET CONVERSATION THREAD MODAL                                 -->
        <!-- ===================================================================== -->
        <div v-if="isViewModalOpen && activeTicket" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] w-full max-w-2xl max-h-[90vh] flex flex-col rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <!-- Header -->
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-[#21262D] flex items-start justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono font-bold text-xs text-teal-600 dark:text-teal-400">
                                {{ activeTicket.ticket_number }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase border" :class="getStatusBadge(activeTicket.status)">
                                {{ activeTicket.status.replace('_', ' ') }}
                            </span>
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] uppercase border" :class="getPriorityBadge(activeTicket.priority)">
                                {{ activeTicket.priority }}
                            </span>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-snug">
                            {{ activeTicket.subject }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            Category: <strong class="text-slate-700 dark:text-slate-300">{{ getCategoryLabel(activeTicket.category) }}</strong> • Opened {{ activeTicket.created_at }}
                        </p>
                    </div>
                    <button type="button" @click="isViewModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Body (Scrollable Messages) -->
                <div class="p-5 sm:p-6 flex-1 overflow-y-auto space-y-4 bg-slate-50/50 dark:bg-[#0D1117]/50">
                    <!-- Original Message -->
                    <div class="p-4 rounded-2xl bg-white dark:bg-[#1C2128] border border-slate-200/80 dark:border-[#30363D] space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span class="font-bold text-slate-900 dark:text-white">Your Initial Query</span>
                            <span>{{ activeTicket.created_at }}</span>
                        </div>
                        <p class="text-xs text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed font-normal">
                            {{ activeTicket.message }}
                        </p>
                        <div v-if="activeTicket.attachment_path" class="pt-2 border-t border-slate-100 dark:border-[#21262D]">
                            <a :href="activeTicket.attachment_path" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-teal-600 hover:underline">
                                <Paperclip class="w-3.5 h-3.5" />
                                <span>View Attached File</span>
                            </a>
                        </div>
                    </div>

                    <!-- Replies -->
                    <div
                        v-for="reply in activeTicket.replies"
                        :key="reply.id"
                        class="p-4 rounded-2xl border space-y-2"
                        :class="reply.is_admin ? 'bg-teal-50/70 dark:bg-teal-950/30 border-teal-200 dark:border-teal-900/60 ml-4' : 'bg-white dark:bg-[#1C2128] border-slate-200/80 dark:border-[#30363D] mr-4'"
                    >
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold" :class="reply.is_admin ? 'text-teal-800 dark:text-teal-300' : 'text-slate-900 dark:text-white'">
                                    {{ reply.is_admin ? 'Super Admin Support Agent' : reply.user_name }}
                                </span>
                                <span v-if="reply.is_admin" class="px-2 py-0.2 rounded text-[10px] font-black uppercase bg-teal-600 text-white">
                                    Official
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400">{{ reply.created_at }}</span>
                        </div>
                        <p class="text-xs text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                            {{ reply.message }}
                        </p>
                        <div v-if="reply.attachment_path" class="pt-2">
                            <a :href="reply.attachment_path" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-teal-600 hover:underline">
                                <Paperclip class="w-3.5 h-3.5" />
                                <span>View Attachment</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reply Footer Form -->
                <form @submit.prevent="submitReply" class="p-4 sm:p-5 border-t border-slate-100 dark:border-[#21262D] bg-white dark:bg-[#161B22] space-y-3">
                    <div>
                        <textarea
                            v-model="replyForm.message"
                            rows="2"
                            required
                            placeholder="Type a follow-up reply or question..."
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs focus:border-teal-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <input
                            type="file"
                            @input="replyForm.attachment = $event.target.files[0]"
                            class="text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-100 dark:file:bg-[#21262D] file:text-slate-700 dark:file:text-slate-300"
                        />

                        <button
                            type="submit"
                            :disabled="replyForm.processing"
                            class="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-bold shadow-md transition cursor-pointer"
                        >
                            <Send class="w-3.5 h-3.5" />
                            <span>{{ replyForm.processing ? 'Posting...' : 'Post Reply' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>