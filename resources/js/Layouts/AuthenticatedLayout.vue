<script setup>
import { ref, computed, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import {
    LifeBuoy,
    Phone,
    Globe,
    LayoutDashboard,
    FileText,
    Users,
    Calendar,
    Pill,
    Settings,
    Bell,
    Search,
    Stethoscope,
    PlusCircle,
    Activity,
    SlidersHorizontal,
    Moon,
    Sun,
    Menu,
    X,
    Building2,
    Sparkles,
    CheckCircle2,
    Clock,
    UserCheck,
    ChevronRight,
    ChevronDown,
    HelpCircle,
    LogOut,
    User as UserIcon,
    Shield,
    ShieldCheck,
    Crown,
    Star,
    History,
    PanelRight,
    Folder,
    UserCircle2,
    Share2,
    Check,
    Pipette,
    MessageSquare,
    Zap,
    Layers,
    CreditCard,
    HeartPulse,
    Mail,
    BarChart3,
    ArrowRight
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'User', email: 'user@example.com' });

const isDark = ref(false);
const isSidebarOpen = ref(true);
const isMobileMenuOpen = ref(false);
const isRightPanelOpen = ref(false);
const isDrugManagementOpen = ref(true);
const isClinicalLookupsOpen = ref(true);
const isChamberDropdownOpen = ref(false);
const isUserDropdownOpen = ref(false);
const searchQuery = ref('');

const activeChamber = computed(() => page.props.activeChamber);
const userChambers = computed(() => page.props.userChambers || []);

const notifications = ref([]);
const activities = ref([]);

const switchChamber = (chamber) => {
    isChamberDropdownOpen.value = false;
    router.post(route('chambers.switch', chamber.id), {}, {
        preserveScroll: true,
    });
};

const canAccess = (perm) => {
    const role = user.value?.role;
    if (role === 'super_admin' || role === 'doctor') {
        return true;
    }
    const userPerms = page.props.auth?.permissions || [];
    return userPerms.includes('*') || userPerms.includes(perm);
};

onMounted(() => {
    // Check saved preference or default to light
    const savedTheme = localStorage.getItem('app-theme');
    if (savedTheme === 'dark') {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }

    // Default sidebar open
    isSidebarOpen.value = true;
});

const toggleDarkMode = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('app-theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('app-theme', 'light');
    }
};
</script>

<template>
    <div class="min-h-screen bg-[#F7F9FB] dark:bg-[#0D1117] text-[#1C1C1C] dark:text-[#E6EDF3] flex flex-col font-sans transition-colors duration-200">
        
        <!-- Impersonation Alert Banner -->
        <div
            v-if="$page.props.isImpersonating"
            class="bg-gradient-to-r from-amber-600 via-orange-600 to-rose-600 text-white px-4 py-2 text-xs font-bold flex items-center justify-between z-50 shadow-md"
        >
            <div class="flex items-center gap-2">
                <ShieldCheck class="w-4 h-4 animate-pulse" />
                <span>Super Admin Support Mode: You are currently impersonating <strong class="underline">{{ user.name }}</strong> ({{ user.email }}).</span>
            </div>
            <Link
                :href="route('admin.stop-impersonating')"
                method="post"
                as="button"
                class="px-3 py-1 rounded-lg bg-white text-slate-950 text-xs font-black hover:bg-slate-100 transition shadow-xs cursor-pointer"
            >
                Return to Super Admin →
            </Link>
        </div>

        <!-- ========================================================================= -->
        <!-- MOBILE SLIDE-OVER SIDEBAR DRAWER (< lg screens)                          -->
        <!-- ========================================================================= -->
        <div
            v-if="isMobileMenuOpen"
            class="fixed inset-0 z-50 lg:hidden flex bg-slate-950/60 backdrop-blur-xs transition-opacity duration-300"
            @click.self="isMobileMenuOpen = false"
        >
            <aside class="w-72 max-w-[85vw] bg-white dark:bg-[#161B22] h-full shadow-2xl flex flex-col border-r border-slate-200 dark:border-[#30363D] animate-in slide-in-from-left duration-200">
                <!-- Brand User Pill -->
                <div class="h-16 px-4 flex items-center justify-between border-b border-[#F2F2F2] dark:border-[#21262D]">
                    <Link :href="route('profile.edit')" @click="isMobileMenuOpen = false" class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-[#0A0D36] dark:bg-teal-500 text-white flex items-center justify-center font-bold text-xs shrink-0">
                            {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                        </div>
                        <div class="truncate">
                            <p class="font-bold text-sm text-[#1C1C1C] dark:text-white truncate leading-tight">{{ user.name }}</p>
                            <p class="text-[10px] text-slate-400 truncate">
                                {{
                                    user.role === 'super_admin'
                                        ? 'Super Administrator'
                                        : (user.role === 'staff'
                                            ? (user.staff_profile?.designation || 'Staff / Assistant')
                                            : (user.role === 'patient'
                                                ? 'Registered Patient'
                                                : (user.doctor_profile?.specialty || 'Doctor')))
                                }}
                            </p>
                        </div>
                    </Link>

                    <button
                        type="button"
                        @click="isMobileMenuOpen = false"
                        class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D] transition cursor-pointer"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Nav Tree (Mobile) -->
                <div class="flex-1 overflow-y-auto px-3 py-3 space-y-5 custom-scrollbar text-xs">
                    
                    <!-- Super Admin Console -->
                    <div v-if="user.role === 'super_admin'" class="p-2.5 rounded-xl bg-teal-500/10 border border-teal-500/20 space-y-1.5">
                        <p class="px-1 text-[10px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 flex items-center gap-1">
                            <ShieldCheck class="w-3 h-3" />
                            <span>Super Admin</span>
                        </p>
                        <div class="space-y-0.5">
                            <Link
                                :href="route('admin.dashboard')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.dashboard*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <LayoutDashboard class="w-3.5 h-3.5" />
                                <span>SaaS Overview</span>
                            </Link>
                            <Link
                                :href="route('admin.doctors.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.doctors.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <Users class="w-3.5 h-3.5" />
                                <span>Doctors & Trials</span>
                            </Link>
                            <Link
                                :href="route('admin.ai-settings.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.ai-settings.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                                <span>AI & API Keys</span>
                            </Link>
                            <Link
                                :href="route('admin.pricing-plans.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.pricing-plans.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <CreditCard class="w-3.5 h-3.5 text-emerald-500" />
                                <span>Pricing Plans</span>
                            </Link>
                            <Link
                                :href="route('admin.payment-settings.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.payment-settings.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <Zap class="w-3.5 h-3.5 text-amber-400" />
                                <span>Payment Gateways</span>
                            </Link>
                            <Link
                                :href="route('admin.landing-content.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.landing-content.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <Globe class="w-3.5 h-3.5 text-teal-400" />
                                <span>Landing Page CMS</span>
                            </Link>
                            <Link
                                :href="route('admin.mail-settings.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.mail-settings.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <Mail class="w-3.5 h-3.5 text-sky-400" />
                                <span>SMTP & Email</span>
                            </Link>
                            <Link
                                :href="route('admin.contacts.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.contacts.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <MessageSquare class="w-3.5 h-3.5 text-indigo-400" />
                                <span>Landing Inquiries</span>
                            </Link>
                            <Link
                                :href="route('admin.tickets.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.tickets.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <LifeBuoy class="w-3.5 h-3.5 text-teal-400" />
                                <span>Doctor Tickets</span>
                            </Link>
                            <Link
                                :href="route('admin.support-settings.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.support-settings.*') ? 'bg-teal-600 text-white font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                            >
                                <Phone class="w-3.5 h-3.5 text-emerald-400" />
                                <span>Support Settings</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Patient Portal -->
                    <div v-if="user.role === 'patient'">
                        <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#9B9B9B] dark:text-[#8B949E] mb-1.5">
                            My Health Portal
                        </p>
                        <div class="space-y-0.5">
                            <Link
                                :href="route('dashboard')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all"
                                :class="[route().current('dashboard') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-[#707070] dark:text-[#8B949E] hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                            >
                                <HeartPulse class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span>Health Overview</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Practice / Main Clinical Menu (Doctors & Staff) -->
                    <div v-else-if="user.role !== 'super_admin'">
                        <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#9B9B9B] dark:text-[#8B949E] mb-1.5">
                            Practice
                        </p>
                        <div class="space-y-0.5">
                            <Link
                                :href="route('dashboard')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl transition-all"
                                :class="[route().current('dashboard') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]']"
                            >
                                <LayoutDashboard class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span>Overview</span>
                            </Link>

                            <Link
                                v-if="canAccess('prescriptions.view') || canAccess('prescriptions.create')"
                                :href="route('prescriptions.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl transition-colors"
                                :class="[route().current('prescriptions.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]']"
                            >
                                <FileText class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                <span>Prescriptions (Rx)</span>
                            </Link>

                            <Link
                                v-if="canAccess('appointments.manage')"
                                :href="route('appointments.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl transition-colors"
                                :class="[route().current('appointments.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]']"
                            >
                                <Calendar class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                <span>Appointments & Queue</span>
                            </Link>

                            <Link
                                v-if="canAccess('patients.manage')"
                                :href="route('patients.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl transition-colors"
                                :class="[route().current('patients.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]']"
                            >
                                <Users class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                <span>Patients</span>
                            </Link>

                            <Link
                                v-if="canAccess('chambers.view')"
                                :href="route('chambers.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl transition-colors"
                                :class="[route().current('chambers.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]']"
                            >
                                <Building2 class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                <span>Chambers & Visiting</span>
                            </Link>

                            <Link
                                :href="route('reports.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl transition-colors"
                                :class="[route().current('reports.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]']"
                            >
                                <BarChart3 class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                <span>Reports & Analytics</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Clinical Database & Rx Setup -->
                    <div v-if="user.role !== 'super_admin' && user.role !== 'patient' && (canAccess('lookups.manage') || canAccess('drugs.manage'))">
                        <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#9B9B9B] dark:text-[#8B949E] mb-1.5">
                            Clinical Database
                        </p>
                        <div class="space-y-1">
                            <div v-if="canAccess('lookups.manage')" class="space-y-0.5">
                                <Link
                                    :href="route('clinical-lookups.index', 'complaints')"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                >
                                    <Activity class="w-3.5 h-3.5 text-teal-600" />
                                    <span>Chief Complaints (C/C)</span>
                                </Link>
                                <Link
                                    :href="route('clinical-lookups.index', 'diagnoses')"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                >
                                    <Stethoscope class="w-3.5 h-3.5 text-purple-600" />
                                    <span>Diagnoses (Dx)</span>
                                </Link>
                                <Link
                                    :href="route('clinical-lookups.index', 'investigations')"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                >
                                    <FileText class="w-3.5 h-3.5 text-blue-600" />
                                    <span>Investigations (Ix)</span>
                                </Link>
                                <Link
                                    :href="route('clinical-lookups.index', 'advices')"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                >
                                    <MessageSquare class="w-3.5 h-3.5 text-indigo-600" />
                                    <span>General Advice</span>
                                </Link>
                            </div>

                            <div v-if="canAccess('drugs.manage')" class="space-y-0.5 pt-1">
                                <Link
                                    :href="route('drugs.index')"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                >
                                    <Layers class="w-3.5 h-3.5 text-teal-600" />
                                    <span>Drug Database</span>
                                </Link>
                                <Link
                                    :href="route('drug-lookups.index', 'doses')"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                >
                                    <Pipette class="w-3.5 h-3.5 text-purple-600" />
                                    <span>Drug Doses</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Settings & Profile -->
                    <div>
                        <p class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#9B9B9B] dark:text-[#8B949E] mb-1.5">
                            {{ user.role === 'super_admin' ? 'System & Admin' : (user.role === 'staff' ? 'Staff & Settings' : (user.role === 'patient' ? 'Patient Account' : 'Doctor & Settings')) }}
                        </p>
                        <div class="space-y-0.5">
                            <Link
                                v-if="user.role === 'doctor'"
                                :href="route('staff.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                :class="[route().current('staff.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : '']"
                            >
                                <UserCheck class="w-4 h-4 text-cyan-600" />
                                <span>Staff & Permissions</span>
                            </Link>

                            <Link
                                v-if="user.role === 'doctor'"
                                :href="route('subscription.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                :class="[route().current('subscription.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : '']"
                            >
                                <CreditCard class="w-4 h-4 text-teal-600" />
                                <span>Billing & Subscription</span>
                            </Link>

                            <Link
                                v-if="user.role !== 'super_admin'"
                                :href="route('support.index')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                :class="[route().current('support.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : '']"
                            >
                                <LifeBuoy class="w-4 h-4 text-teal-600" />
                                <span>Support & Helpdesk</span>
                            </Link>

                            <Link
                                :href="route('profile.edit')"
                                @click="isMobileMenuOpen = false"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D]"
                                :class="[route().current('profile.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : '']"
                            >
                                <UserCircle2 class="w-4 h-4 text-teal-600" />
                                <span>Profile & Credentials</span>
                            </Link>
                            <Link
                                :href="route('logout')"
                                method="post"
                                as="button"
                                @click="isMobileMenuOpen = false"
                                class="w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-bold"
                            >
                                <LogOut class="w-4 h-4" />
                                <span>Log Out</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <div class="flex flex-1 overflow-hidden">
            <!-- ===================================================================== -->
            <!-- DESKTOP LEFT SIDEBAR (>= lg screens)                                  -->
            <!-- ===================================================================== -->
            <aside
                class="hidden lg:flex flex-col border-r border-[#EFEFEF] dark:border-[#21262D] bg-[#FFFFFF] dark:bg-[#161B22] transition-all duration-300 z-30 select-none"
                :class="isSidebarOpen ? 'w-60' : 'w-18'"
            >
                <!-- Brand User Pill -->
                <div class="h-16 px-4 flex items-center justify-between border-b border-[#F2F2F2] dark:border-[#21262D]">
                    <Link :href="route('profile.edit')" class="flex items-center gap-2.5 overflow-hidden hover:opacity-80 transition-opacity">
                        <div class="w-8 h-8 rounded-xl bg-[#0A0D36] dark:bg-teal-500 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                            {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                        </div>
                        <div v-show="isSidebarOpen" class="truncate">
                            <p class="font-bold text-xs text-[#1C1C1C] dark:text-white truncate leading-tight">{{ user.name }}</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 truncate">
                                {{
                                    user.role === 'super_admin'
                                        ? 'Super Administrator'
                                        : (user.role === 'staff'
                                            ? (user.staff_profile?.designation || 'Staff / Assistant')
                                            : (user.role === 'patient'
                                                ? 'Registered Patient'
                                                : (user.doctor_profile?.specialty || 'Doctor')))
                                }}
                            </p>
                        </div>
                    </Link>
                </div>

                <!-- Nav Tree Desktop -->
                <div class="flex-1 overflow-y-auto px-3 py-3 space-y-5 custom-scrollbar text-xs">
                    
                    <!-- SECTION 0: Super Admin Console -->
                    <div v-if="user.role === 'super_admin'" class="p-2.5 rounded-xl bg-teal-500/10 border border-teal-500/20 space-y-1.5">
                        <p v-show="isSidebarOpen" class="px-1 text-[10px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 flex items-center gap-1">
                            <ShieldCheck class="w-3 h-3" />
                            <span>Super Admin</span>
                        </p>
                        <div class="space-y-0.5">
                            <Link
                                :href="route('admin.dashboard')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.dashboard*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="SaaS Overview"
                            >
                                <LayoutDashboard class="w-3.5 h-3.5" />
                                <span v-show="isSidebarOpen">SaaS Overview</span>
                            </Link>
                            <Link
                                :href="route('admin.doctors.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.doctors.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="Doctors & Trials"
                            >
                                <Users class="w-3.5 h-3.5" />
                                <span v-show="isSidebarOpen">Doctors & Trials</span>
                            </Link>
                            <Link
                                :href="route('admin.ai-settings.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.ai-settings.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="AI & API Keys"
                            >
                                <Sparkles class="w-3.5 h-3.5 text-amber-500" />
                                <span v-show="isSidebarOpen">AI & API Keys</span>
                            </Link>
                            <Link
                                :href="route('admin.landing-content.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.landing-content.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="Landing Page CMS"
                            >
                                <Globe class="w-3.5 h-3.5 text-teal-400" />
                                <span v-show="isSidebarOpen">Landing Page CMS</span>
                            </Link>
                            <Link
                                :href="route('admin.pricing-plans.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.pricing-plans.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="Pricing Plans"
                            >
                                <CreditCard class="w-3.5 h-3.5 text-emerald-500" />
                                <span v-show="isSidebarOpen">Pricing Plans</span>
                            </Link>
                            <Link
                                :href="route('admin.payment-settings.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.payment-settings.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="Payment Gateways"
                            >
                                <Zap class="w-3.5 h-3.5 text-amber-400" />
                                <span v-show="isSidebarOpen">Payment Gateways</span>
                            </Link>
                            <Link
                                :href="route('admin.mail-settings.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.mail-settings.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="SMTP & Email Settings"
                            >
                                <Mail class="w-3.5 h-3.5 text-sky-400" />
                                <span v-show="isSidebarOpen">SMTP & Email</span>
                            </Link>
                            <Link
                                :href="route('admin.contacts.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.contacts.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="Landing Page Inquiries"
                            >
                                <MessageSquare class="w-3.5 h-3.5 text-indigo-400" />
                                <span v-show="isSidebarOpen">Landing Inquiries</span>
                            </Link>
                            <Link
                                :href="route('admin.tickets.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.tickets.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="Doctor Support Tickets"
                            >
                                <LifeBuoy class="w-3.5 h-3.5 text-teal-400" />
                                <span v-show="isSidebarOpen">Doctor Tickets</span>
                            </Link>
                            <Link
                                :href="route('admin.support-settings.index')"
                                class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg transition-all"
                                :class="[route().current('admin.support-settings.*') ? 'bg-teal-600 text-white font-bold shadow-xs' : 'text-slate-700 dark:text-slate-200 hover:bg-teal-500/20']"
                                title="Support Channel Settings"
                            >
                                <Phone class="w-3.5 h-3.5 text-emerald-400" />
                                <span v-show="isSidebarOpen">Support Settings</span>
                            </Link>
                        </div>
                    </div>

                    <!-- SECTION 1A: Patient Health Portal -->
                    <div v-if="user.role === 'patient'">
                        <p v-show="isSidebarOpen" class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#9B9B9B] dark:text-[#8B949E] mb-1.5">
                            My Health Portal
                        </p>
                        <div class="space-y-0.5">
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all"
                                :class="[route().current('dashboard') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-[#707070] dark:text-[#8B949E] hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                            >
                                <HeartPulse class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span v-show="isSidebarOpen">Health Overview</span>
                            </Link>
                        </div>
                    </div>

                    <!-- SECTION 1B: Practice / Main Clinical Menu (Doctors & Staff Only) -->
                    <div v-else-if="user.role !== 'super_admin'">
                        <p v-show="isSidebarOpen" class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#9B9B9B] dark:text-[#8B949E] mb-1.5">
                            Practice
                        </p>
                        <div class="space-y-0.5">
                            <Link
                                :href="route('dashboard')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-all"
                                :class="[route().current('dashboard') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Practice Overview"
                            >
                                <LayoutDashboard class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span v-show="isSidebarOpen">Overview</span>
                            </Link>

                            <Link
                                v-if="canAccess('prescriptions.view') || canAccess('prescriptions.create')"
                                :href="route('prescriptions.index')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('prescriptions.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Prescriptions (Rx)"
                            >
                                <FileText class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                <span v-show="isSidebarOpen">Prescriptions (Rx)</span>
                            </Link>

                            <Link
                                v-if="canAccess('appointments.manage')"
                                :href="route('appointments.index')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('appointments.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Appointments & Queue"
                            >
                                <Calendar class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                <span v-show="isSidebarOpen">Appointments & Queue</span>
                            </Link>

                            <Link
                                v-if="canAccess('patients.manage')"
                                :href="route('patients.index')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('patients.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Patient Records"
                            >
                                <Users class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                <span v-show="isSidebarOpen">Patients</span>
                            </Link>

                            <Link
                                v-if="canAccess('chambers.view')"
                                :href="route('chambers.index')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('chambers.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Chambers & Visiting"
                            >
                                <Building2 class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                <span v-show="isSidebarOpen">Chambers & Visiting</span>
                            </Link>

                            <Link
                                :href="route('reports.index')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('reports.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Practice Reports & Analytics"
                            >
                                <BarChart3 class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                                <span v-show="isSidebarOpen">Reports & Analytics</span>
                            </Link>
                        </div>
                    </div>

                    <!-- SECTION 2: Clinical Database & Rx Setup -->
                    <div v-if="user.role !== 'super_admin' && user.role !== 'patient' && (canAccess('lookups.manage') || canAccess('drugs.manage'))">
                        <p v-show="isSidebarOpen" class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#9B9B9B] dark:text-[#8B949E] mb-1.5">
                            Clinical Database
                        </p>
                        <div class="space-y-1">
                            <!-- Clinical Lookups Accordion -->
                            <div v-if="canAccess('lookups.manage')">
                                <button
                                    type="button"
                                    @click="isClinicalLookupsOpen = !isClinicalLookupsOpen"
                                    class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-[#1C1C1C] dark:text-[#E6EDF3] font-semibold hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors cursor-pointer"
                                >
                                    <div class="flex items-center gap-2">
                                        <Activity class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                        <span v-show="isSidebarOpen">Clinical Lookups</span>
                                    </div>
                                    <component :is="isClinicalLookupsOpen ? ChevronDown : ChevronRight" v-show="isSidebarOpen" class="w-3.5 h-3.5 text-[#A0A0A0]" />
                                </button>

                                <div v-show="isClinicalLookupsOpen && isSidebarOpen" class="pl-3 space-y-0.5 border-l-2 border-slate-100 dark:border-slate-800 ml-3.5 mt-1">
                                    <Link
                                        :href="route('clinical-lookups.index', 'complaints')"
                                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors"
                                        :class="route().current('clinical-lookups.index', { type: 'complaints' }) ? 'font-bold text-teal-600 dark:text-teal-400 bg-teal-50/50 dark:bg-teal-950/30' : ''"
                                    >
                                        <Activity class="w-3.5 h-3.5" />
                                        <span>Chief Complaints (C/C)</span>
                                    </Link>

                                    <Link
                                        :href="route('clinical-lookups.index', 'diagnoses')"
                                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors"
                                        :class="route().current('clinical-lookups.index', { type: 'diagnoses' }) ? 'font-bold text-teal-600 dark:text-teal-400 bg-teal-50/50 dark:bg-teal-950/30' : ''"
                                    >
                                        <Stethoscope class="w-3.5 h-3.5" />
                                        <span>Diagnoses (Dx)</span>
                                    </Link>

                                    <Link
                                        :href="route('clinical-lookups.index', 'investigations')"
                                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors"
                                        :class="route().current('clinical-lookups.index', { type: 'investigations' }) ? 'font-bold text-teal-600 dark:text-teal-400 bg-teal-50/50 dark:bg-teal-950/30' : ''"
                                    >
                                        <FileText class="w-3.5 h-3.5" />
                                        <span>Investigations (Ix)</span>
                                    </Link>

                                    <Link
                                        :href="route('clinical-lookups.index', 'advices')"
                                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors"
                                        :class="route().current('clinical-lookups.index', { type: 'advices' }) ? 'font-bold text-teal-600 dark:text-teal-400 bg-teal-50/50 dark:bg-teal-950/30' : ''"
                                    >
                                        <MessageSquare class="w-3.5 h-3.5" />
                                        <span>General Advice</span>
                                    </Link>
                                </div>
                            </div>

                            <!-- Drug Management Accordion -->
                            <div v-if="canAccess('drugs.manage')">
                                <button
                                    type="button"
                                    @click="isDrugManagementOpen = !isDrugManagementOpen"
                                    class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-[#1C1C1C] dark:text-[#E6EDF3] font-semibold hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors cursor-pointer"
                                >
                                    <div class="flex items-center gap-2">
                                        <SlidersHorizontal class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                                        <span v-show="isSidebarOpen">Drug Management</span>
                                    </div>
                                    <component :is="isDrugManagementOpen ? ChevronDown : ChevronRight" v-show="isSidebarOpen" class="w-3.5 h-3.5 text-[#A0A0A0]" />
                                </button>

                                <div v-show="isDrugManagementOpen && isSidebarOpen" class="pl-3 space-y-0.5 border-l-2 border-slate-100 dark:border-slate-800 ml-3.5 mt-1">
                                    <Link
                                        :href="route('drugs.index')"
                                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors"
                                        :class="route().current('drugs.*') ? 'font-bold text-teal-600 dark:text-teal-400 bg-teal-50/50 dark:bg-teal-950/30' : ''"
                                    >
                                        <Layers class="w-3.5 h-3.5" />
                                        <span>Drug Database</span>
                                    </Link>

                                    <Link
                                        :href="route('drug-lookups.index', 'doses')"
                                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors"
                                        :class="route().current('drug-lookups.index', { type: 'doses' }) ? 'font-bold text-teal-600 dark:text-teal-400 bg-teal-50/50 dark:bg-teal-950/30' : ''"
                                    >
                                        <Pipette class="w-3.5 h-3.5" />
                                        <span>Drug Doses</span>
                                    </Link>

                                    <Link
                                        :href="route('drug-lookups.index', 'advices')"
                                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors"
                                        :class="route().current('drug-lookups.index', { type: 'advices' }) ? 'font-bold text-teal-600 dark:text-teal-400 bg-teal-50/50 dark:bg-teal-950/30' : ''"
                                    >
                                        <MessageSquare class="w-3.5 h-3.5" />
                                        <span>Drug Advice</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Settings Menu -->
                    <div>
                        <p v-show="isSidebarOpen" class="px-2 text-[10px] font-bold uppercase tracking-wider text-[#9B9B9B] dark:text-[#8B949E] mb-1.5">
                            {{ user.role === 'super_admin' ? 'System & Admin' : (user.role === 'staff' ? 'Staff & Settings' : (user.role === 'patient' ? 'Patient Account' : 'Doctor & Settings')) }}
                        </p>
                        <div class="space-y-0.5">
                            <!-- Staff & Permissions (Doctor Only) -->
                            <Link
                                v-if="user.role === 'doctor'"
                                :href="route('staff.index')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('staff.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Staff Management"
                            >
                                <UserCheck class="w-4 h-4 text-cyan-600 dark:text-cyan-400" />
                                <span v-show="isSidebarOpen">Staff & Permissions</span>
                            </Link>

                            <!-- Billing & Subscription (Doctor Only) -->
                            <Link
                                v-if="user.role === 'doctor'"
                                :href="route('subscription.index')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('subscription.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Billing & Plans"
                            >
                                <CreditCard class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span v-show="isSidebarOpen">Billing & Subscription</span>
                            </Link>

                            <!-- Support & Helpdesk -->
                            <Link
                                v-if="user.role !== 'super_admin'"
                                :href="route('support.index')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('support.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                                title="Support & Helpdesk"
                            >
                                <LifeBuoy class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span v-show="isSidebarOpen">Support & Helpdesk</span>
                            </Link>

                            <!-- Profile & Credentials -->
                            <Link
                                v-if="user.role === 'doctor'"
                                :href="route('profile.edit')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('profile.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                            >
                                <UserCircle2 class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span v-show="isSidebarOpen">Doctor Profile & Credentials</span>
                            </Link>

                            <Link
                                v-else-if="user.role === 'staff' || user.role === 'patient'"
                                :href="route('profile.edit')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg transition-colors"
                                :class="[route().current('profile.*') ? 'bg-teal-50 dark:bg-teal-950/40 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D]']"
                            >
                                <UserIcon class="w-4 h-4 text-teal-600 dark:text-teal-400" />
                                <span v-show="isSidebarOpen">My Account & Password</span>
                            </Link>

                            <Link
                                v-if="user.role === 'doctor' || user.role === 'super_admin'"
                                :href="route('profile.edit')"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-slate-700 dark:text-slate-200 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors"
                            >
                                <Settings class="w-4 h-4 text-slate-500" />
                                <span v-show="isSidebarOpen">Account Settings</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Footer Sign Out -->
                <div class="p-3 border-t border-[#F2F2F2] dark:border-[#21262D]">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="w-full flex items-center justify-start gap-2 px-2.5 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-all cursor-pointer"
                    >
                        <LogOut class="w-3.5 h-3.5" />
                        <span v-show="isSidebarOpen">Log Out</span>
                    </Link>
                </div>
            </aside>

            <!-- Main Layout Wrapper -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <!-- Top Navigation Bar (Exact ByeWind Header) -->
                <header class="h-16 border-b border-[#EFEFEF] dark:border-[#21262D] bg-[#FFFFFF] dark:bg-[#161B22] px-4 sm:px-6 flex items-center justify-between z-20">
                    <div class="flex items-center gap-3">
                        <!-- Mobile Hamburger Button (< lg screens) -->
                        <button
                            @click="isMobileMenuOpen = true"
                            class="lg:hidden p-2 rounded-xl text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#21262D] transition-colors cursor-pointer"
                            title="Open Menu"
                        >
                            <Menu class="w-5 h-5" />
                        </button>

                        <!-- Desktop Sidebar Toggle (>= lg screens) -->
                        <button
                            @click="isSidebarOpen = !isSidebarOpen"
                            class="hidden lg:flex p-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors cursor-pointer"
                            title="Toggle Sidebar"
                        >
                            <SlidersHorizontal class="w-4 h-4" />
                        </button>

                        <!-- Breadcrumbs -->
                        <div class="hidden sm:flex items-center text-xs text-[#9B9B9B] dark:text-[#8B949E]">
                            <span>{{ user.role === 'super_admin' ? 'Super Admin Console' : (user.role === 'patient' ? 'Patient Health Portal' : 'Dashboards') }}</span>
                            <span class="mx-2 text-[#D0D0D0]">/</span>
                            <span class="text-[#1C1C1C] dark:text-white font-medium">
                                {{ user.role === 'super_admin' ? (route().current('admin.doctors.*') ? 'Doctors & Trials' : (route().current('admin.ai-settings.*') ? 'AI & API Keys' : (route().current('admin.pricing-plans.*') ? 'Pricing Plans' : 'SaaS Overview'))) : (user.role === 'patient' ? 'Health Overview' : 'Default') }}
                            </span>
                        </div>

                        <!-- Chamber Switcher Pill Dropdown (Doctors & Staff Only) -->
                        <div v-if="user.role === 'doctor' || user.role === 'staff'" class="relative">
                            <button
                                type="button"
                                @click="isChamberDropdownOpen = !isChamberDropdownOpen"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#F4F5F7] dark:bg-[#21262D] hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 text-xs font-semibold transition-all cursor-pointer border border-transparent hover:border-slate-300 dark:hover:border-slate-600 shadow-2xs"
                                title="Switch Active Chamber"
                            >
                                <Building2 class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400 shrink-0" />
                                <span class="max-w-[130px] sm:max-w-[180px] truncate">{{ activeChamber?.name || 'Select Chamber' }}</span>
                                <span v-if="activeChamber?.branch" class="hidden md:inline text-[11px] text-slate-400 font-normal">({{ activeChamber.branch }})</span>
                                <ChevronDown class="w-3 h-3 text-slate-400" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-if="isChamberDropdownOpen"
                                class="absolute left-0 mt-1.5 w-64 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-2xl shadow-xl py-1.5 z-50 text-xs space-y-0.5"
                            >
                                <div class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 dark:border-[#21262D] flex items-center justify-between">
                                    <span>Active Practice Chamber</span>
                                    <span class="text-[10px] text-teal-600 dark:text-teal-400">{{ userChambers.length }} Available</span>
                                </div>

                                <div class="max-h-60 overflow-y-auto custom-scrollbar">
                                    <button
                                        v-for="c in userChambers"
                                        :key="c.id"
                                        type="button"
                                        @click="switchChamber(c)"
                                        class="w-full text-left px-3 py-2 hover:bg-slate-50 dark:hover:bg-[#21262D] flex items-center justify-between transition-colors cursor-pointer"
                                        :class="activeChamber?.id === c.id ? 'bg-teal-50/50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-700 dark:text-slate-300'"
                                    >
                                        <div class="truncate pr-2">
                                            <p class="truncate font-semibold">{{ c.name }}</p>
                                            <p class="text-[10px] text-slate-400 truncate">{{ c.branch || c.address }}</p>
                                        </div>
                                        <Check v-if="activeChamber?.id === c.id" class="w-4 h-4 text-teal-600 shrink-0" />
                                    </button>
                                </div>

                                <div class="pt-1 border-t border-slate-100 dark:border-[#21262D] px-2">
                                    <Link
                                        :href="route('chambers.index')"
                                        @click="isChamberDropdownOpen = false"
                                        class="block text-center py-1.5 text-[11px] font-bold text-teal-600 dark:text-teal-400 hover:underline"
                                    >
                                        Manage Chambers →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Header Right Section -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Search Bar -->
                        <div class="relative hidden md:block w-44 lg:w-56">
                            <Search class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-[#A0A0A0]" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search..."
                                class="w-full pl-8 pr-7 py-1.5 text-xs bg-[#F4F5F7] dark:bg-[#21262D] border-none rounded-lg text-[#1C1C1C] dark:text-white placeholder-[#A0A0A0] focus:ring-1 focus:ring-slate-400"
                            />
                        </div>

                        <!-- Theme Toggle Button -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 rounded-xl text-[#707070] dark:text-[#8B949E] hover:bg-[#F4F5F7] dark:hover:bg-[#21262D] transition-colors cursor-pointer"
                            :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                        >
                            <Sun v-if="isDark" class="w-4 h-4 text-amber-400" />
                            <Moon v-else class="w-4 h-4 text-[#707070]" />
                        </button>

                        <!-- User Profile Dropdown -->
                        <div class="relative ml-1">
                            <button
                                type="button"
                                @click="isUserDropdownOpen = !isUserDropdownOpen"
                                class="flex items-center gap-2 p-1 sm:px-2 sm:py-1 rounded-xl hover:bg-slate-100 dark:hover:bg-[#21262D] transition-colors cursor-pointer border border-transparent hover:border-slate-200 dark:hover:border-slate-700"
                            >
                                <div class="w-7 h-7 rounded-full bg-[#0A0D36] dark:bg-teal-500 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                                </div>
                                <div class="hidden md:block text-left text-xs leading-tight">
                                    <p class="font-bold text-slate-800 dark:text-slate-100 truncate max-w-[120px]">{{ user.name }}</p>
                                    <p class="text-[10px] text-slate-400 truncate">
                                        {{
                                            user.role === 'super_admin'
                                                ? 'Super Admin'
                                                : (user.role === 'staff'
                                                    ? (user.staff_profile?.designation || 'Staff / Assistant')
                                                    : (user.role === 'patient'
                                                        ? 'Patient'
                                                        : (user.doctor_profile?.specialty || 'Doctor')))
                                        }}
                                    </p>
                                </div>
                                <ChevronDown class="w-3.5 h-3.5 text-slate-400" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-if="isUserDropdownOpen"
                                class="absolute right-0 mt-1.5 w-56 bg-white dark:bg-[#161B22] border border-slate-200 dark:border-[#30363D] rounded-2xl shadow-xl py-1.5 z-50 text-xs divide-y divide-slate-100 dark:divide-[#21262D]"
                            >
                                <div class="px-3.5 py-2">
                                    <p class="font-bold text-slate-900 dark:text-white truncate">{{ user.name }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">{{ user.email }}</p>
                                    <span v-if="user.role === 'super_admin'" class="inline-block mt-1 px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                        Super Administrator
                                    </span>
                                </div>

                                <div class="py-1">
                                    <Link
                                        :href="route('profile.edit')"
                                        @click="isUserDropdownOpen = false"
                                        class="flex items-center gap-2 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-[#21262D] text-slate-700 dark:text-slate-200"
                                    >
                                        <Settings class="w-4 h-4 text-slate-400" />
                                        <span>Account Settings</span>
                                    </Link>

                                    <Link
                                        v-if="user.role === 'doctor'"
                                        :href="route('subscription.index')"
                                        @click="isUserDropdownOpen = false"
                                        class="flex items-center gap-2 px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-[#21262D] text-slate-700 dark:text-slate-200"
                                    >
                                        <CreditCard class="w-4 h-4 text-teal-600" />
                                        <span>Billing & Plan</span>
                                    </Link>
                                </div>

                                <div class="py-1">
                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="w-full flex items-center gap-2 px-3.5 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 font-bold"
                                    >
                                        <LogOut class="w-4 h-4" />
                                        <span>Log Out</span>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Main Content Area -->
                <main class="flex-1 overflow-y-auto p-4 sm:p-6 custom-scrollbar bg-[#F7F9FB] dark:bg-[#0D1117]">
                    <!-- Flash Success / Error Messages -->
                    <div v-if="$page.props.flash?.success" class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center justify-between">
                        <span>{{ $page.props.flash.success }}</span>
                    </div>

                    <div v-if="$page.props.flash?.error" class="mb-5 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-semibold flex items-center justify-between">
                        <span>{{ $page.props.flash.error }}</span>
                    </div>

                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
    height: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
}
:global(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>