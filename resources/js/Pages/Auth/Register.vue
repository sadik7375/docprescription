<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Stethoscope, User, Mail, Phone, Lock, Eye, EyeOff, UserPlus, Sparkles } from '@lucide/vue';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: 'doctor',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Doctor Registration - Doc Helper AI" />

        <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200/80 dark:border-slate-700/80">
            <!-- Header -->
            <div class="mb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/20 text-teal-600 dark:text-teal-400 text-xs font-semibold tracking-wide uppercase mb-3">
                    <Stethoscope class="w-3.5 h-3.5" /> Doctor Account Setup
                </div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    Create Doctor Account <Sparkles class="w-5 h-5 text-teal-500" />
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Register your medical practice to manage chambers, patients, and prescriptions.
                </p>
            </div>

            <!-- Register Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Name -->
                <div>
                    <InputLabel for="name" value="Doctor Full Name" class="font-medium text-slate-700 dark:text-slate-300" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <User class="w-4 h-4" />
                        </div>
                        <TextInput
                            id="name"
                            type="text"
                            class="pl-10 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-teal-500 focus:border-teal-500 text-sm"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Dr. Daren Hirthe"
                            autocomplete="name"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.name" />
                </div>


                <!-- Email Address -->
                <div>
                    <InputLabel for="email" value="Email Address" class="font-medium text-slate-700 dark:text-slate-300" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <Mail class="w-4 h-4" />
                        </div>
                        <TextInput
                            id="email"
                            type="email"
                            class="pl-10 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-teal-500 focus:border-teal-500 text-sm"
                            v-model="form.email"
                            required
                            placeholder="doctor@example.com"
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <!-- Phone Number -->
                <div>
                    <InputLabel for="phone" value="Phone Number (Optional)" class="font-medium text-slate-700 dark:text-slate-300" />
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <Phone class="w-4 h-4" />
                        </div>
                        <TextInput
                            id="phone"
                            type="tel"
                            class="pl-10 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-teal-500 focus:border-teal-500 text-sm"
                            v-model="form.phone"
                            placeholder="e.g. +1 (555) 234-5678"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.phone" />
                </div>

                <!-- Password & Confirm Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <InputLabel for="password" value="Password" class="font-medium text-slate-700 dark:text-slate-300" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <Lock class="w-4 h-4" />
                            </div>
                            <TextInput
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                class="pl-10 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-teal-500 focus:border-teal-500 text-sm"
                                v-model="form.password"
                                required
                                placeholder="••••••••"
                                autocomplete="new-password"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="font-medium text-slate-700 dark:text-slate-300" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <Lock class="w-4 h-4" />
                            </div>
                            <TextInput
                                id="password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                class="pl-10 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-teal-500 focus:border-teal-500 text-sm"
                                v-model="form.password_confirmation"
                                required
                                placeholder="••••••••"
                                autocomplete="new-password"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-teal-600 to-indigo-600 hover:from-teal-700 hover:to-indigo-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-teal-500/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50 cursor-pointer"
                        :disabled="form.processing"
                    >
                        <UserPlus v-if="!form.processing" class="w-4 h-4" />
                        <span v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Creating Doctor Account...' : 'Register Doctor Account' }}</span>
                    </button>
                </div>
            </form>

            <!-- Footer Login Link -->
            <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700/80 text-center text-xs text-slate-500 dark:text-slate-400">
                Already registered?
                <Link
                    :href="route('login')"
                    class="font-bold text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 ms-1 underline decoration-2 underline-offset-4"
                >
                    Sign In to Dashboard
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
