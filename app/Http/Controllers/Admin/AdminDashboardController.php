<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chamber;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Display Super Admin SaaS dashboard with key practice & trial metrics.
     */
    public function index(): Response
    {
        $totalDoctors = User::where('role', 'doctor')->count();
        $trialingDoctors = User::where('role', 'doctor')->where('subscription_status', 'trialing')->count();
        $activeSubscribers = User::where('role', 'doctor')->where('subscription_status', 'active')->count();
        $expiredAccounts = User::where('role', 'doctor')->where('subscription_status', 'expired')->count();
        $suspendedAccounts = User::where('role', 'doctor')->where(function ($q) {
            $q->where('subscription_status', 'suspended')->orWhere('is_active', false);
        })->count();

        $totalPrescriptions = Prescription::count();
        $totalPatients = Patient::count();
        $totalChambers = Chamber::count();

        // Plan distribution
        $planStats = [
            'free_trial' => User::where('role', 'doctor')->where('subscription_plan', 'free_trial')->count(),
            'solo' => User::where('role', 'doctor')->where('subscription_plan', 'solo')->count(),
            'pro' => User::where('role', 'doctor')->where('subscription_plan', 'pro')->count(),
            'enterprise' => User::where('role', 'doctor')->where('subscription_plan', 'enterprise')->count(),
            'lifetime' => User::where('role', 'doctor')->where('subscription_plan', 'lifetime')->count(),
        ];

        // Recent doctors
        $recentDoctors = User::where('role', 'doctor')
            ->with(['doctorProfile'])
            ->withCount(['chambers', 'prescriptions'])
            ->latest()
            ->take(8)
            ->get();

        // Expiring trials (expiring in next 3 days or already expired)
        $expiringTrials = User::where('role', 'doctor')
            ->where('subscription_status', 'trialing')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now()->addDays(3))
            ->with(['doctorProfile'])
            ->orderBy('trial_ends_at')
            ->take(6)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_doctors' => $totalDoctors,
                'trialing_doctors' => $trialingDoctors,
                'active_subscribers' => $activeSubscribers,
                'expired_accounts' => $expiredAccounts,
                'suspended_accounts' => $suspendedAccounts,
                'total_prescriptions' => $totalPrescriptions,
                'total_patients' => $totalPatients,
                'total_chambers' => $totalChambers,
            ],
            'plan_stats' => $planStats,
            'recent_doctors' => $recentDoctors,
            'expiring_trials' => $expiringTrials,
        ]);
    }
}
