<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AdminDoctorController extends Controller
{
    /**
     * Display a listing of doctors with trial & subscription management.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $plan = $request->input('plan');
        $status = $request->input('status');

        $query = User::where('role', 'doctor')
            ->with(['doctorProfile', 'defaultChamber'])
            ->withCount(['chambers', 'prescriptions']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('doctorProfile', function ($dq) use ($search) {
                      $dq->where('specialty', 'like', "%{$search}%")
                         ->orWhere('bmdc_reg_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($plan && $plan !== 'all') {
            $query->where('subscription_plan', $plan);
        }

        if ($status && $status !== 'all') {
            if ($status === 'suspended') {
                $query->where(function ($q) {
                    $q->where('subscription_status', 'suspended')->orWhere('is_active', false);
                });
            } else {
                $query->where('subscription_status', $status);
            }
        }

        $doctors = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::where('role', 'doctor')->count(),
            'trialing' => User::where('role', 'doctor')->where('subscription_status', 'trialing')->count(),
            'active' => User::where('role', 'doctor')->where('subscription_status', 'active')->count(),
            'expired' => User::where('role', 'doctor')->where('subscription_status', 'expired')->count(),
        ];

        return Inertia::render('Admin/Doctors/Index', [
            'doctors' => $doctors,
            'filters' => [
                'search' => $search,
                'plan' => $plan,
                'status' => $status,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Store a newly created doctor by Super Admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:20|unique:'.User::class,
            'password' => ['required', Rules\Password::defaults()],
            'title' => 'nullable|string|max:50',
            'specialty' => 'nullable|string|max:255',
            'bmdc_reg_no' => 'nullable|string|max:100',
            'subscription_plan' => 'required|string|in:free_trial,solo,pro,enterprise,lifetime',
            'subscription_status' => 'required|string|in:trialing,active,expired,suspended,cancelled',
            'trial_days' => 'nullable|integer|min:0|max:365',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $trialEndsAt = null;
        if ($validated['subscription_status'] === 'trialing') {
            $days = $validated['trial_days'] ?? 14;
            $trialEndsAt = now()->addDays($days);
        }

        $subscriptionEndsAt = null;
        if ($validated['subscription_status'] === 'active' && $validated['subscription_plan'] !== 'lifetime') {
            $subscriptionEndsAt = now()->addYear();
        }

        $doctor = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => 'doctor',
            'password' => Hash::make($validated['password']),
            'is_active' => true,
            'subscription_plan' => $validated['subscription_plan'],
            'subscription_status' => $validated['subscription_status'],
            'trial_ends_at' => $trialEndsAt,
            'subscription_ends_at' => $subscriptionEndsAt,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        DoctorProfile::create([
            'user_id' => $doctor->id,
            'title' => $validated['title'] ?? 'Dr.',
            'specialty' => $validated['specialty'] ?? 'General Physician',
            'bmdc_reg_no' => $validated['bmdc_reg_no'] ?? null,
            'degrees' => ['MBBS'],
        ]);

        return redirect()->back()->with('success', "Doctor account created successfully for {$doctor->name}.");
    }

    /**
     * Update doctor's subscription, trial duration, plan and account status.
     */
    public function updateSubscription(Request $request, User $doctor): RedirectResponse
    {
        $validated = $request->validate([
            'subscription_plan' => 'required|string|in:free_trial,solo,pro,enterprise,lifetime',
            'subscription_status' => 'required|string|in:trialing,active,expired,suspended,cancelled',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'extend_days' => 'nullable|integer|min:1|max:365',
            'is_active' => 'nullable|boolean',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $trialEndsAt = $doctor->trial_ends_at;
        if (!empty($validated['extend_days'])) {
            $baseDate = ($trialEndsAt && $trialEndsAt->isFuture()) ? $trialEndsAt : now();
            $trialEndsAt = Carbon::parse($baseDate)->addDays((int) $validated['extend_days']);
        } elseif (isset($validated['trial_ends_at'])) {
            $trialEndsAt = $validated['trial_ends_at'] ? Carbon::parse($validated['trial_ends_at']) : null;
        }

        $subscriptionEndsAt = isset($validated['subscription_ends_at']) 
            ? ($validated['subscription_ends_at'] ? Carbon::parse($validated['subscription_ends_at']) : null)
            : $doctor->subscription_ends_at;

        $doctor->update([
            'subscription_plan' => $validated['subscription_plan'],
            'subscription_status' => $validated['subscription_status'],
            'trial_ends_at' => $trialEndsAt,
            'subscription_ends_at' => $subscriptionEndsAt,
            'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : $doctor->is_active,
            'admin_notes' => $validated['admin_notes'] ?? $doctor->admin_notes,
        ]);

        return redirect()->back()->with('success', "Subscription & Trial updated for {$doctor->name}.");
    }

    /**
     * Toggle doctor active status (Activate / Suspend).
     */
    public function toggleStatus(User $doctor): RedirectResponse
    {
        $newStatus = !$doctor->is_active;
        $doctor->update([
            'is_active' => $newStatus,
            'subscription_status' => $newStatus ? ($doctor->subscription_status === 'suspended' ? 'trialing' : $doctor->subscription_status) : 'suspended',
        ]);

        $label = $newStatus ? 'activated' : 'suspended';
        return redirect()->back()->with('success', "Doctor account has been {$label}.");
    }

    /**
     * Impersonate doctor (Log in as doctor for administrative assistance).
     */
    public function impersonate(User $doctor): RedirectResponse
    {
        session(['admin_impersonator_id' => Auth::id()]);
        Auth::login($doctor);

        return redirect()->route('dashboard')->with('success', "Now logged in as {$doctor->name}.");
    }

    /**
     * Stop impersonating and return to Super Admin.
     */
    public function stopImpersonating(): RedirectResponse
    {
        $adminId = session('admin_impersonator_id');
        if ($adminId) {
            $admin = User::find($adminId);
            if ($admin && $admin->isSuperAdmin()) {
                session()->forget('admin_impersonator_id');
                Auth::login($admin);
                return redirect()->route('admin.dashboard')->with('success', 'Returned to Super Admin console.');
            }
        }

        return redirect()->route('dashboard');
    }

    /**
     * Delete doctor account.
     */
    public function destroy(User $doctor): RedirectResponse
    {
        if ($doctor->id === Auth::id()) {
            return redirect()->back()->with('error', 'Cannot delete your own admin account.');
        }

        $name = $doctor->name;
        $doctor->delete();

        return redirect()->back()->with('success', "Doctor account {$name} has been deleted.");
    }
}
