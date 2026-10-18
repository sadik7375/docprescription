<script setup>
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, Lock, Eye, EyeOff, LogIn, KeyRound, Sparkles } from '@lucide/vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in - Doc Helper AI" />

        <div class="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200/80 dark:border-slate-700/80">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    Welcome back <Sparkles class="w-5 h-5 text-amber-500 animate-spin-slow" />
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Sign in to access your doctor dashboard or patient portal.
                </p>
            </div>

            <!-- Status Message -->
            <div v-if="status" class="mb-5 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-sm font-medium text-emerald-700 dark:text-emerald-300">
                {{ status }}
            </div>

            <!-- Login Form -->
            <form @submit.prevent="submit" class="space-y-4">
                <!-- Email Field -->
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
                            autofocus
                            placeholder="doctor@example.com"
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.email" />
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between">
                        <InputLabel for="password" value="Password" class="font-medium text-slate-700 dark:text-slate-300" />
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs font-semibold text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300"
                        >
                            Forgot Password?
                        </Link>
                    </div>

                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <Lock class="w-4 h-4" />
                        </div>
                        <TextInput
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="pl-10 pr-10 w-full rounded-xl border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-white focus:ring-teal-500 focus:border-teal-500 text-sm"
                            v-model="form.password"
                            required
                            placeholder="••••••••"
                            autocomplete="current-password"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <Eye v-if="!showPassword" class="w-4 h-4" />
                            <EyeOff v-else class="w-4 h-4" />
                        </button>
                    </div>
                    <InputError class="mt-1.5" :message="form.errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center cursor-pointer">
                        <Checkbox name="remember" v-model:checked="form.remember" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                        <span class="ms-2 text-xs font-medium text-slate-600 dark:text-slate-400">Keep me signed in</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-teal-600 to-indigo-600 hover:from-teal-700 hover:to-indigo-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-teal-500/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50 cursor-pointer"
                        :disabled="form.processing"
                    >
                        <LogIn v-if="!form.processing" class="w-4 h-4" />
                        <span v-else class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Signing in...' : 'Sign In to Dashboard' }}</span>
                    </button>
                </div>
            </form>

            <!-- Footer Signup Link -->
            <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-700/80 text-center text-xs text-slate-500 dark:text-slate-400">
                Don't have an account yet?
                <Link
                    :href="route('register')"
                    class="font-bold text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300 ms-1 underline decoration-2 underline-offset-4"
                >
                    Create Free Account
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
