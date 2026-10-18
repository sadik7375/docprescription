<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    Mail,
    Phone,
    User,
    Check,
    Trash2,
    Search,
    MessageSquare,
    Clock,
    X,
    ExternalLink,
    Send,
    Filter
} from 'lucide-vue-next';

const props = defineProps({
    messages: Object,
    filters: Object,
    counts: Object,
});

const search = ref(props.filters.search || '');
const activeStatus = ref(props.filters.status || 'all');
const activeMessage = ref(null);
const isViewModalOpen = ref(false);

const updateForm = useForm({
    status: 'read',
    admin_reply: '',
});

const handleFilter = (status) => {
    activeStatus.value = status;
    router.get(route('admin.contacts.index'), {
        status: status,
        search: search.value,
    }, { preserveState: true, replace: true });
};

const handleSearch = () => {
    router.get(route('admin.contacts.index'), {
        status: activeStatus.value,
        search: search.value,
    }, { preserveState: true, replace: true });
};

const openViewModal = (msg) => {
    activeMessage.value = msg;
    updateForm.status = msg.status === 'unread' ? 'read' : msg.status;
    updateForm.admin_reply = msg.admin_reply || '';
    isViewModalOpen.value = true;
};

const saveStatusAndReply = () => {
    if (!activeMessage.value) return;
    updateForm.post(route('admin.contacts.update-status', activeMessage.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isViewModalOpen.value = false;
        },
    });
};

const deleteMessage = (id) => {
    if (!confirm('Are you sure you want to permanently delete this contact inquiry?')) return;
    router.delete(route('admin.contacts.destroy', id), {
        preserveScroll: true,
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'unread':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-900 font-bold';
        case 'read':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800';
        case 'replied':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800 font-bold';
        default:
            return 'bg-slate-100 text-slate-800';
    }
};
</script>

<template>
    <Head title="Landing Page Inquiries - Super Admin" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Landing Page Inquiries & Leads
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Manage contact form submissions, enterprise demo inquiries, and public inquiries from the landing page.
                    </p>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] space-y-1">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Submissions</span>
                    <p class="text-2xl font-black text-slate-900 dark:text-white">{{ counts.total }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] space-y-1">
                    <span class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-wider">Unread Inquiries</span>
                    <p class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ counts.unread }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-[#161B22] border border-slate-200/80 dark:border-[#21262D] space-y-1">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Replied / Contacted</span>
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ counts.replied }}</p>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-[#161B22] rounded-3xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <!-- Toolbar -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-[#21262D] flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto">
                        <button
                            type="button"
                            v-for="st in ['all', 'unread', 'read', 'replied']"
                            :key="st"
                            @click="handleFilter(st)"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition capitalize cursor-pointer"
                            :class="activeStatus === st ? 'bg-teal-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 hover:bg-slate-200'"
                        >
                            {{ st }}
                        </button>
                    </div>

                    <div class="w-full sm:w-64 relative">
                        <input
                            type="text"
                            v-model="search"
                            @keyup.enter="handleSearch"
                            placeholder="Search name, email, query..."
                            class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-xs text-slate-900 dark:text-white focus:border-teal-500 focus:outline-none"
                        />
                        <Search class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!messages.data || messages.data.length === 0" class="py-16 text-center text-slate-400 text-xs">
                    No contact inquiries found matching the selected filter.
                </div>

                <!-- Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-[#1C2128] text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100 dark:border-[#21262D]">
                            <tr>
                                <th class="py-3.5 px-4 sm:px-6">Visitor</th>
                                <th class="py-3.5 px-4">Subject & Message</th>
                                <th class="py-3.5 px-4">Type</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Date</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-[#21262D]">
                            <tr
                                v-for="msg in messages.data"
                                :key="msg.id"
                                class="hover:bg-slate-50/70 dark:hover:bg-[#1C2128]/50 transition"
                            >
                                <td class="py-4 px-4 sm:px-6">
                                    <p class="font-bold text-slate-900 dark:text-white text-xs">{{ msg.name }}</p>
                                    <p class="text-[11px] text-teal-600 font-mono">{{ msg.email }}</p>
                                    <p v-if="msg.phone" class="text-[10px] text-slate-400">{{ msg.phone }}</p>
                                </td>
                                <td class="py-4 px-4 max-w-sm">
                                    <p class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ msg.subject || 'Inquiry' }}</p>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ msg.message }}</p>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300">
                                        {{ msg.inquiry_type.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase border" :class="getStatusBadge(msg.status)">
                                        {{ msg.status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-[11px] text-slate-500">
                                    {{ msg.created_at }}
                                </td>
                                <td class="py-4 px-4 text-right space-x-2">
                                    <button
                                        type="button"
                                        @click="openViewModal(msg)"
                                        class="px-3 py-1.5 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold hover:bg-teal-100 transition cursor-pointer"
                                    >
                                        View & Manage
                                    </button>
                                    <button
                                        type="button"
                                        @click="deleteMessage(msg.id)"
                                        class="p-1.5 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer"
                                        title="Delete Message"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- View Inquiry Modal -->
        <div v-if="isViewModalOpen && activeMessage" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
            <div class="bg-white dark:bg-[#161B22] w-full max-w-xl rounded-3xl border border-slate-200 dark:border-[#21262D] shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Contact Message Details</h3>
                    <button type="button" @click="isViewModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div class="p-5 sm:p-6 space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-[#1C2128]">
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400">Visitor Name</span>
                            <p class="font-bold text-slate-900 dark:text-white text-sm">{{ activeMessage.name }}</p>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400">Email Address</span>
                            <a :href="'mailto:' + activeMessage.email" class="font-bold text-teal-600 block text-sm hover:underline">
                                {{ activeMessage.email }}
                            </a>
                        </div>
                        <div v-if="activeMessage.phone">
                            <span class="text-[10px] font-bold uppercase text-slate-400">Phone Number</span>
                            <a :href="'tel:' + activeMessage.phone" class="font-bold text-slate-900 dark:text-white block hover:underline">
                                {{ activeMessage.phone }}
                            </a>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase text-slate-400">Date Received</span>
                            <p class="font-bold text-slate-700 dark:text-slate-300">{{ activeMessage.created_at }}</p>
                        </div>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400">Subject</span>
                        <p class="font-bold text-slate-900 dark:text-white mt-0.5 text-sm">{{ activeMessage.subject }}</p>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400">Message Content</span>
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-[#1C2128] text-slate-800 dark:text-slate-200 mt-1 whitespace-pre-line leading-relaxed">
                            {{ activeMessage.message }}
                        </div>
                    </div>

                    <!-- Update Status Form -->
                    <form @submit.prevent="saveStatusAndReply" class="pt-3 border-t border-slate-100 dark:border-[#21262D] space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Update Status</label>
                                <select
                                    v-model="updateForm.status"
                                    class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs font-bold"
                                >
                                    <option value="unread">Unread</option>
                                    <option value="read">Read</option>
                                    <option value="replied">Replied / Handled</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <a
                                    :href="'mailto:' + activeMessage.email + '?subject=' + encodeURIComponent('Re: ' + activeMessage.subject)"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 text-xs font-bold border border-teal-200 dark:border-teal-800"
                                >
                                    <Mail class="w-3.5 h-3.5" />
                                    <span>Compose Direct Email</span>
                                </a>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Internal Admin Notes / Reply Summary</label>
                            <textarea
                                v-model="updateForm.admin_reply"
                                rows="2"
                                placeholder="Record notes or resolution details for team reference..."
                                class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-[#1C2128] border border-slate-200 dark:border-[#30363D] text-slate-900 dark:text-white text-xs"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button
                                type="button"
                                @click="isViewModalOpen = false"
                                class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-[#21262D] text-slate-700 dark:text-slate-300 text-xs font-bold"
                            >
                                Close
                            </button>
                            <button
                                type="submit"
                                :disabled="updateForm.processing"
                                class="px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-md transition"
                            >
                                {{ updateForm.processing ? 'Saving...' : 'Save Updates' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>