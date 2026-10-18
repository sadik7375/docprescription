<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ChevronLeft,
    Building2,
    MapPin,
    Phone,
    Clock,
    Calendar,
    DollarSign,
    Save,
    Star,
    Edit3
} from 'lucide-vue-next';

const props = defineProps({
    chamber: Object,
});

const daysList = [
    'Saturday',
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday'
];

const form = useForm({
    name: props.chamber.name || '',
    branch: props.chamber.branch || '',
    address: props.chamber.address || '',
    phone: props.chamber.phone || '',
    consultation_fee: props.chamber.consultation_fee ?? 1000,
    followup_fee: props.chamber.followup_fee ?? 600,
    visiting_days: props.chamber.visiting_days || [],
    visiting_time: props.chamber.visiting_time || '',
    room_no: props.chamber.room_no || '',
    is_default: props.chamber.is_default ?? false,
    is_active: props.chamber.is_active ?? true,
});

const toggleDay = (day) => {
    const index = form.visiting_days.indexOf(day);
    if (index > -1) {
        form.visiting_days.splice(index, 1);
    } else {
        form.visiting_days.push(day);
    }
};

const submit = () => {
    form.put(route('chambers.update', props.chamber.id));
};
</script>

<template>
    <Head :title="'Edit ' + chamber.name + ' - Doc Helper AI'" />

    <AuthenticatedLayout>
        <div class="space-y-6 max-w-4xl mx-auto">
            <!-- Form Container Card -->
            <div class="bg-white dark:bg-[#161B22] rounded-2xl border border-slate-200/80 dark:border-[#21262D] shadow-xs overflow-hidden">
                <!-- Card Header -->
                <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <Edit3 class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                            Edit Chamber: {{ chamber.name }}
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            Update practice location details, fees, and visiting days.
                        </p>
                    </div>

                    <Link
                        :href="route('chambers.index')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#0A0D36] dark:bg-slate-800 hover:bg-[#12164A] text-white text-xs font-semibold rounded-lg transition-colors cursor-pointer"
                    >
                        <ChevronLeft class="w-3.5 h-3.5" /> Back
                    </Link>
                </div>

                <form @submit.prevent="submit" class="p-5 sm:p-6 space-y-6">
                    <!-- SECTION 1: Basic Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Chamber / Hospital Name -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Chamber / Hospital Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                            <p v-if="form.errors.name" class="text-rose-500 text-[11px] mt-1">{{ form.errors.name }}</p>
                        </div>

                        <!-- Branch -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Branch / Area
                            </label>
                            <input
                                v-model="form.branch"
                                type="text"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>

                        <!-- Full Address -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Full Address (Appears on Prescription Header)
                            </label>
                            <input
                                v-model="form.address"
                                type="text"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>

                        <!-- Contact Phone / Serial Booking -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Contact / Appointment Booking Hotline
                            </label>
                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>

                        <!-- Room / Unit No -->
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Room / Unit / Floor
                            </label>
                            <input
                                v-model="form.room_no"
                                type="text"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>
                    </div>

                    <!-- SECTION 2: Schedule & Visiting Days -->
                    <div class="space-y-3 pt-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Visiting Days (Select available days)
                        </label>

                        <div class="flex items-center gap-2 flex-wrap">
                            <button
                                v-for="day in daysList"
                                :key="day"
                                type="button"
                                @click="toggleDay(day)"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all border cursor-pointer select-none"
                                :class="[
                                    form.visiting_days.includes(day)
                                        ? 'bg-[#0A0D36] dark:bg-teal-500 text-white dark:text-slate-950 border-transparent shadow-xs font-bold'
                                        : 'bg-white dark:bg-[#21262D] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-[#30363D] hover:border-slate-400'
                                ]"
                            >
                                {{ day }}
                            </button>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1 mt-3">
                                Visiting Hours
                            </label>
                            <input
                                v-model="form.visiting_time"
                                type="text"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>
                    </div>

                    <!-- SECTION 3: Fees & Default -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Consultation Fee
                            </label>
                            <input
                                v-model="form.consultation_fee"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Follow-up Fee
                            </label>
                            <input
                                v-model="form.followup_fee"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 text-xs bg-white dark:bg-[#21262D] border border-slate-300 dark:border-[#30363D] rounded-xl text-slate-900 dark:text-white focus:ring-2 focus:ring-teal-500"
                            />
                        </div>
                    </div>

                    <!-- Default Chamber Toggle -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-[#21262D]/60 border border-slate-200/80 dark:border-[#30363D] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Star class="w-4 h-4 text-amber-500" />
                            <div>
                                <label for="edit_is_default_toggle" class="text-xs font-bold text-slate-900 dark:text-white cursor-pointer select-none">
                                    Set as Primary / Default Chamber
                                </label>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    This chamber will be selected by default upon login.
                                </p>
                            </div>
                        </div>

                        <input
                            id="edit_is_default_toggle"
                            v-model="form.is_default"
                            type="checkbox"
                            class="w-4 h-4 text-teal-600 rounded-md border-slate-300 focus:ring-teal-500 cursor-pointer"
                        />
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-4 border-t border-slate-100 dark:border-[#21262D] flex items-center justify-end gap-3">
                        <Link
                            :href="route('chambers.index')"
                            class="px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#21262D] rounded-xl transition-colors cursor-pointer"
                        >
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#0A0D36] hover:bg-[#12164A] text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm disabled:opacity-50"
                        >
                            <Save class="w-4 h-4" />
                            <span>{{ form.processing ? 'Saving...' : 'Update Chamber' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
