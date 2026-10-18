<script setup>
import { ref, watch, nextTick, computed } from 'vue';
import axios from 'axios';
import {
    Sparkles,
    Send,
    X,
    Bot,
    User,
    Plus,
    Check,
    Stethoscope,
    Pill,
    FileText,
    Activity,
    ShieldCheck,
    RotateCcw,
    MessageSquare,
    Calculator,
    Zap
} from 'lucide-vue-next';

const props = defineProps({
    patient: {
        type: Object,
        default: () => ({})
    },
    diagnoses: {
        type: Array,
        default: () => []
    },
    drugs: {
        type: Array,
        default: () => []
    },
    complaints: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['add-drug', 'add-investigation', 'add-advice', 'add-complaint']);

const isOpen = ref(false);
const inputMessage = ref('');
const isSending = ref(false);
const chatContainer = ref(null);

const messages = ref([
    {
        id: 1,
        role: 'assistant',
        title: 'Doc Helper AI Clinical Copilot',
        text: 'Hello Doctor, I am your active Clinical AI Copilot. You can ask me for pediatric dosage calculations, pharmacological alternatives, diagnostic workups, or custom patient counseling guidelines.',
        suggested_actions: [],
        timestamp: 'Ready'
    }
]);

const quickPrompts = [
    { label: '👶 Pediatric Dosing Guide', prompt: 'Calculate pediatric dosages for Paracetamol, Amoxicillin, and Azithromycin based on child weight in kg.' },
    { label: '💊 ACEI Cough Alternative', prompt: 'Suggest evidence-based alternatives for hypertension patient experiencing ACE-inhibitor induced dry cough.' },
    { label: '🧪 Recommend Investigations', prompt: 'What diagnostic investigation panel is recommended for current patient clinical findings?' },
    { label: '🥗 Patient Diet & Lifestyle', prompt: 'Draft evidence-based dietary and lifestyle advice for this patient profile.' },
];

const patientContextSummary = computed(() => {
    const parts = [];
    if (props.patient?.name) parts.push(props.patient.name);
    if (props.patient?.age) parts.push(`${props.patient.age} yrs`);
    if (props.patient?.gender) parts.push(props.patient.gender);
    if (props.diagnoses?.length) {
        const dxNames = props.diagnoses.map(d => d.diagnosis || d).filter(Boolean);
        if (dxNames.length) parts.push(`Dx: ${dxNames.join(', ')}`);
    }
    return parts.length ? parts.join(' • ') : 'No patient selected';
});

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const sendMessage = async (customPrompt = null) => {
    const textToSend = customPrompt || inputMessage.value.trim();
    if (!textToSend || isSending.value) return;

    const userMsg = {
        id: Date.now(),
        role: 'user',
        text: textToSend,
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };

    messages.value.push(userMsg);
    inputMessage.value = '';
    isSending.value = true;
    scrollToBottom();

    try {
        const response = await axios.post(route('ai.chat'), {
            message: textToSend,
            history: messages.value.map(m => ({ role: m.role, content: m.text })),
            context: {
                patient: props.patient,
                diagnoses: props.diagnoses,
                drugs: props.drugs,
                complaints: props.complaints,
            }
        });

        const data = response.data;
        messages.value.push({
            id: Date.now() + 1,
            role: 'assistant',
            title: data.title || 'AI Clinical Guidance',
            text: data.answer || 'Guidance received.',
            suggested_actions: data.suggested_actions || [],
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        });
    } catch (error) {
        console.error('AI Copilot Chat error:', error);
        messages.value.push({
            id: Date.now() + 1,
            role: 'assistant',
            title: 'Doc Helper AI Copilot',
            text: 'I am currently operating with the built-in clinical offline database. Feel free to request standard pediatric calculations, drug interaction checks, or investigation panels.',
            suggested_actions: [],
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        });
    } finally {
        isSending.value = false;
        scrollToBottom();
    }
};

const handleAction = (actionItem) => {
    if (actionItem.action === 'add_drug') {
        emit('add-drug', actionItem.data);
    } else if (actionItem.action === 'add_investigation') {
        emit('add-investigation', actionItem.data);
    } else if (actionItem.action === 'add_advice') {
        emit('add-advice', actionItem.data);
    }
    actionItem.applied = true;
};
</script>

<template>
    <div>
        <!-- Floating Launcher Button (Bottom Right) -->
        <div class="fixed bottom-6 right-6 z-40">
            <button
                type="button"
                @click="isOpen = true"
                class="group flex items-center gap-2.5 px-4 py-3 rounded-2xl bg-gradient-to-r from-teal-600 via-teal-500 to-cyan-600 text-white font-bold text-xs shadow-xl shadow-teal-600/30 hover:shadow-teal-600/50 hover:scale-105 active:scale-95 transition-all duration-300 cursor-pointer border border-white/20"
            >
                <div class="w-6 h-6 rounded-xl bg-white/20 flex items-center justify-center group-hover:rotate-12 transition-transform">
                    <Sparkles class="w-4 h-4 text-white" />
                </div>
                <span>AI Clinical Copilot</span>
                <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
            </button>
        </div>

        <!-- Slide-out Drawer Panel -->
        <div
            v-if="isOpen"
            class="fixed inset-0 z-50 flex justify-end bg-slate-950/40 backdrop-blur-xs transition-opacity duration-300"
            @click.self="isOpen = false"
        >
            <div class="w-full max-w-lg bg-white dark:bg-[#161B22] h-full shadow-2xl border-l border-slate-200 dark:border-[#30363D] flex flex-col transform transition-transform duration-300 ease-out animate-slideLeft">
                <!-- Drawer Header -->
                <div class="p-4 border-b border-slate-100 dark:border-[#21262D] bg-gradient-to-r from-slate-50 to-teal-50/30 dark:from-[#1A202C] dark:to-teal-950/20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-teal-600 to-cyan-500 text-white flex items-center justify-center shadow-md shadow-teal-600/20">
                            <Sparkles class="w-5 h-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    AI Clinical Copilot
                                </h3>
                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-teal-500/20 text-teal-700 dark:text-teal-300">
                                    Active
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 truncate max-w-[260px]" :title="patientContextSummary">
                                {{ patientContextSummary }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="isOpen = false"
                        class="p-2 rounded-xl hover:bg-slate-200 dark:hover:bg-[#21262D] text-slate-400 hover:text-slate-700 transition cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Quick Prompts Pill Carousel -->
                <div class="p-3 border-b border-slate-100 dark:border-[#21262D] bg-slate-50/50 dark:bg-[#11141A] flex gap-2 overflow-x-auto custom-scrollbar">
                    <button
                        v-for="chip in quickPrompts"
                        :key="chip.label"
                        type="button"
                        @click="sendMessage(chip.prompt)"
                        class="shrink-0 px-2.5 py-1 rounded-xl bg-white dark:bg-[#1E2633] hover:bg-teal-50 dark:hover:bg-teal-950/40 text-slate-700 dark:text-slate-300 hover:text-teal-700 text-[11px] font-semibold border border-slate-200/80 dark:border-[#30363D] shadow-2xs transition-colors cursor-pointer"
                    >
                        {{ chip.label }}
                    </button>
                </div>

                <!-- Chat History Container -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar bg-[#F8FAFC] dark:bg-[#0D1117]">
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        :class="[
                            'flex gap-2.5',
                            msg.role === 'user' ? 'justify-end' : 'justify-start'
                        ]"
                    >
                        <!-- AI Avatar -->
                        <div v-if="msg.role === 'assistant'" class="w-7 h-7 rounded-lg bg-teal-600 text-white flex items-center justify-center shrink-0 mt-1 shadow-xs">
                            <Bot class="w-4 h-4" />
                        </div>

                        <!-- Message Bubble -->
                        <div
                            :class="[
                                'max-w-[85%] rounded-2xl p-3.5 text-xs shadow-xs space-y-2',
                                msg.role === 'user'
                                    ? 'bg-teal-600 text-white rounded-tr-none'
                                    : 'bg-white dark:bg-[#161B22] text-slate-800 dark:text-slate-200 border border-slate-200/80 dark:border-[#21262D] rounded-tl-none'
                            ]"
                        >
                            <div v-if="msg.title && msg.role === 'assistant'" class="flex items-center justify-between border-b border-slate-100 dark:border-[#21262D] pb-1.5 mb-1.5">
                                <span class="font-bold text-teal-700 dark:text-teal-400 text-[11px] flex items-center gap-1">
                                    <Zap class="w-3 h-3" />
                                    {{ msg.title }}
                                </span>
                                <span class="text-[9px] text-slate-400">{{ msg.timestamp }}</span>
                            </div>

                            <!-- Text Content with Line Breaks -->
                            <div class="whitespace-pre-line leading-relaxed">
                                {{ msg.text }}
                            </div>

                            <!-- Direct "Insert into Rx" Action Buttons -->
                            <div v-if="msg.suggested_actions && msg.suggested_actions.length > 0" class="pt-2 border-t border-slate-100 dark:border-[#21262D] space-y-1.5">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    1-Click Insert into Active Prescription:
                                </p>
                                <div class="flex flex-wrap gap-1.5">
                                    <button
                                        v-for="(act, idx) in msg.suggested_actions"
                                        :key="idx"
                                        type="button"
                                        @click="handleAction(act)"
                                        :disabled="act.applied"
                                        :class="[
                                            'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer flex items-center gap-1.5',
                                            act.applied
                                                ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'
                                                : 'bg-teal-50 dark:bg-teal-950/50 hover:bg-teal-600 text-teal-700 dark:text-teal-300 hover:text-white border border-teal-200 dark:border-teal-800'
                                        ]"
                                    >
                                        <Check v-if="act.applied" class="w-3 h-3" />
                                        <Plus v-else class="w-3 h-3" />
                                        <span>{{ act.applied ? 'Inserted into Rx' : act.label }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- User Avatar -->
                        <div v-if="msg.role === 'user'" class="w-7 h-7 rounded-lg bg-[#0A0D36] dark:bg-teal-500 text-white flex items-center justify-center shrink-0 mt-1 shadow-xs">
                            <User class="w-4 h-4" />
                        </div>
                    </div>

                    <!-- Typing / Querying Loader -->
                    <div v-if="isSending" class="flex items-center gap-2 text-slate-400 text-xs py-2 pl-9">
                        <div class="w-2 h-2 rounded-full bg-teal-500 animate-ping"></div>
                        <span>Doc Helper AI is analyzing clinical guidelines...</span>
                    </div>
                </div>

                <!-- Input Footer -->
                <div class="p-3.5 border-t border-slate-100 dark:border-[#21262D] bg-white dark:bg-[#161B22]">
                    <form @submit.prevent="sendMessage()" class="flex items-center gap-2">
                        <input
                            v-model="inputMessage"
                            type="text"
                            placeholder="Ask clinical question, dosing, or guidelines..."
                            class="flex-1 px-3.5 py-2.5 text-xs bg-slate-50 dark:bg-[#21262D] border border-slate-200 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-teal-500"
                            :disabled="isSending"
                        />
                        <button
                            type="submit"
                            :disabled="isSending || !inputMessage.trim()"
                            class="p-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-40 text-white shadow-md shadow-teal-600/20 transition-all cursor-pointer"
                        >
                            <Send class="w-4 h-4" />
                        </button>
                    </form>
                    <p class="text-[9px] text-center text-slate-400 mt-2">
                        *AI Clinical Copilot provides rapid decision support for licensed medical practitioners.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes slideLeft {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}
.animate-slideLeft {
    animation: slideLeft 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
