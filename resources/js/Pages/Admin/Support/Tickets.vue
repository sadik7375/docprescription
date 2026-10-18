<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    LifeBuoy,
    Search,
    Paperclip,
    Send,
    User,
    CheckCircle2,
    Clock,
    AlertCircle,
    X,
    MessageSquare,
    ExternalLink
} from 'lucide-vue-next';

const props = defineProps({
    tickets: Object,
    filters: Object,
    counts: Object,
});

const search = ref(props.filters.search || '');
const activeStatus = ref(props.filters.status || 'all');
const activePriority = ref(props.filters.priority || 'all');
const activeTicket = ref(null);
const isDrawerOpen = ref(false);

const replyForm = useForm({
    message: '',
    status: 'in_progress',
    attachment: null,
});

const handleFilter = () => {
    router.get(route('admin.tickets.index'), {
        status: activeStatus.value,
        priority: activePriority.value,
        search: search.value,
    }, { preserveState: true, replace: true });
};

const openDrawer = (ticket) => {
    activeTicket.value = ticket;
    replyForm.status = ticket.status === 'open' ? 'in_progress' : ticket.status;
    replyForm.reset('message', 'attachment');
    isDrawerOpen.value = true;
};

const submitAdminReply = () => {
    if (!activeTicket.value) return;
    replyForm.post(route('admin.tickets.reply', activeTicket.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset('message', 'attachment');
            const updated = props.tickets.data.find(t => t.id === activeTicket.value.id);
            if (updated) {
                activeTicket.value = updated;
            }
        },
    });
};

const quickUpdateStatus = (status) => {
    if (!activeTicket.value) return;
    router.post(route('admin.tickets.update-status', activeTicket.value.id), {
        status: status,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            if (activeTicket.value) {
                activeTicket.value.status = status;
            }
        }
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
            return 'bg-slate-100 text-slate-800';
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
</script>

<template>
    <Head title="Doctor Support Tickets - Super Admin" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Doctor Support Tickets & Helpdesk
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Manage technical inquiries, prescription issues, and clinical support tickets from registered doctors.
                    </p>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
                <div class="p-4 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">All Tickets</span>
                    <p class="text-xl font-black text-slate-900 dark:text-white">{{ counts.all }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] space-y-1">
                    <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Open</span>
                    <p class="text-xl font-black text-blue-600">{{ counts.open }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] space-y-1">
                    <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">In Progress</span>
                    <p class="text-xl font-black text-amber-600">{{ counts.in_progress }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] space-y-1">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Resolved</span>
                    <p class="text-xl font-black text-emerald-600">{{ counts.resolved }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Closed</span>
                    <p class="text-xl font-black text-slate-600 dark:text-slate-300">{{ counts.closed }}</p>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <!-- Toolbar -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-[#21262D] flex flex-col md:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2 w-full md:w-auto">
                        <select
                            v-model="activeStatus"
                            @change="handleFilter"
                            class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-xs font-bold text-slate-800 dark:text-slate-200"
                        >
                            <option value="all">All Statuses</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="closed">Closed</option>
                        </select>

                        <select
                            v-model="activePriority"
                            @change="handleFilter"
                            class="px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-xs font-bold text-slate-800 dark:text-slate-200"
                        >
                            <option value="all">All Priorities</option>
                            <option value="urgent">Urgent</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>

                    <div class="w-full md:w-64 relative">
                        <input
                            type="text"
                            v-model="search"
                            @keyup.enter="handleFilter"
                            placeholder="Search ticket #, doctor, subject..."
                            class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:outline-none"
                        />
                        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!tickets.data || tickets.data.length === 0" class="py-16 text-center text-slate-400 text-xs">
                    No doctor support tickets found matching current filters.
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-[#1C2128] text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 dark:border-[#21262D]">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-6">Ticket #</th>
                                <th class="py-3.5 px-4">Doctor / Chamber</th>
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
                                @click="openDrawer(ticket)"
                            >
                                <td class="py-4 px-4 sm:px-6 font-mono font-bold text-teal-600 dark:text-teal-400">
                                    {{ ticket.ticket_number }}
                                </td>
                                <td class="py-4 px-4">
                                    <p class="font-bold text-slate-900 dark:text-white">{{ ticket.user?.name }}</p>
                                    <p class="text-[11px] text-slate-500">{{ ticket.user?.email }}</p>
                                </td>
                                <td class="py-4 px-4 max-w-xs truncate font-bold text-slate-900 dark:text-white">
                                    {{ ticket.subject }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] uppercase border" :class="getPriorityBadge(ticket.priority)">
                                        {{ ticket.priority }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase border" :class="getStatusBadge(ticket.status)">
                                        {{ ticket.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-slate-500 text-[11px]">
                                    {{ ticket.last_reply_at }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <button
                                        type="button"
                                        class="px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold hover:bg-teal-100 transition"
                                    >
                                        Reply & Manage
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Ticket Conversation Thread Modal -->
        <div v-if="isDrawerOpen && activeTicket" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] w-full max-w-3xl max-h-[92vh] flex flex-col rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
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
                            From: <strong class="text-slate-700 dark:text-slate-300">{{ activeTicket.user?.name }}</strong> ({{ activeTicket.user?.email }}) • Plan: <span class="capitalize font-bold text-teal-600">{{ activeTicket.user?.plan?.replace('_', ' ') }}</span>
                        </p>
                    </div>
                    <button type="button" @click="isDrawerOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Messages Thread -->
                <div class="p-5 sm:p-6 flex-1 overflow-y-auto space-y-4 bg-slate-50/50 dark:bg-[#0D1117]/50">
                    <!-- Original Message -->
                    <div class="p-4 rounded-2xl bg-white dark:bg-[#1C2128] border border-slate-200/80 dark:border-[#30363D] space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span class="font-bold text-slate-900 dark:text-white">Doctor's Original Ticket</span>
                            <span>{{ activeTicket.created_at }}</span>
                        </div>
                        <p class="text-xs text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                            {{ activeTicket.message }}
                        </p>
                        <div v-if="activeTicket.attachment_path" class="pt-2 border-t border-slate-100 dark:border-[#21262D]">
                            <a :href="activeTicket.attachment_path" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-teal-600 hover:underline">
                                <Paperclip class="w-3.5 h-3.5" />
                                <span>View Attachment</span>
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
                                    {{ reply.is_admin ? 'Super Admin (You)' : reply.user_name }}
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

                <!-- Admin Reply & Status Bar -->
                <form @submit.prevent="submitAdminReply" class="p-5 border-t border-slate-100 dark:border-[#21262D] bg-white dark:bg-[#161B22] space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Set Ticket Status:</span>
                            <select
                                v-model="replyForm.status"
                                class="px-3 py-1 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-xs font-bold"
                            >
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button
                                type="button"
                                @click="quickUpdateStatus('resolved')"
                                class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 font-bold text-xs hover:bg-emerald-100 transition"
                            >
                                Mark Resolved ✓
                            </button>
                        </div>
                    </div>

                    <div>
                        <textarea
                            v-model="replyForm.message"
                            rows="2"
                            required
                            placeholder="Type admin reply message to doctor..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs focus:border-teal-500 focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <input
                            type="file"
                            @input="replyForm.attachment = $event.target.files[0]"
                            class="text-[11px] text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-slate-100 dark:file:bg-[#21262D]"
                        />

                        <button
                            type="submit"
                            :disabled="replyForm.processing"
                            class="inline-flex items-center gap-1.5 px-6 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-bold shadow-md transition cursor-pointer"
                        >
                            <Send class="w-3.5 h-3.5" />
                            <span>{{ replyForm.processing ? 'Sending...' : 'Send Admin Reply' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>