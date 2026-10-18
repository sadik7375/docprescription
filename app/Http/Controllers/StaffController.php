<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    /**
     * All available system feature permissions.
     */
    public const AVAILABLE_PERMISSIONS = [
        [
            'key' => 'appointments.manage',
            'label' => 'Appointments & Live Queue',
            'description' => 'Book, reschedule, update appointment vitals, and manage patient queue.',
            'category' => 'reception',
        ],
        [
            'key' => 'patients.manage',
            'label' => 'Patient Records & Directory',
            'description' => 'Register new patients, view profiles, and update medical history.',
            'category' => 'reception',
        ],
        [
            'key' => 'prescriptions.view',
            'label' => 'View & Print Prescriptions',
            'description' => 'Access prescription list, view patient details, and print physical Rx pads.',
            'category' => 'clinical',
        ],
        [
            'key' => 'prescriptions.create',
            'label' => 'Draft Prescriptions (Rx)',
            'description' => 'Allow clinical assistant or nurse to prepare draft prescriptions for doctor.',
            'category' => 'clinical',
        ],
        [
            'key' => 'chambers.view',
            'label' => 'Chambers & Schedule Overview',
            'description' => 'View chamber locations, visiting hours, and daily patient limits.',
            'category' => 'management',
        ],
        [
            'key' => 'drugs.manage',
            'label' => 'Drug Database & Lookups',
            'description' => 'Search, browse, and manage medication database, doses, and strength rules.',
            'category' => 'clinical',
        ],
        [
            'key' => 'reports.view',
            'label' => 'Daily Reports & Queue Analytics',
            'description' => 'View appointment totals, consultation volume, and collection reports.',
            'category' => 'analytics',
        ],
        [
            'key' => 'ai.assistant',
            'label' => 'AI Clinical Assistant (Copilot)',
            'description' => 'Use AI Clinical Assistant to analyze differential diagnoses and summarize patient case files.',
            'category' => 'clinical',
        ],
    ];

    /**
     * Display a listing of doctor's staff members.
     */
    public function index(Request $request): Response
    {
        $doctor = Auth::user();
        $doctorId = $doctor->id;

        $search = $request->input('search');
        $chamberFilter = $request->input('chamber_id');

        $chambers = Chamber::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $chambersMap = $chambers->keyBy('id');

        $staffProfiles = StaffProfile::with(['user'])
            ->where('doctor_id', $doctorId)
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                })->orWhere('designation', 'like', "%{$search}%");
            })
            ->when($chamberFilter, function ($q) use ($chamberFilter) {
                $q->where(function ($sub) use ($chamberFilter) {
                    $sub->where('chamber_id', $chamberFilter)
                        ->orWhereJsonContains('chamber_ids', (int) $chamberFilter)
                        ->orWhereJsonContains('chamber_ids', (string) $chamberFilter)
                        ->orWhere(function ($allQ) {
                            $allQ->whereNull('chamber_id')->whereNull('chamber_ids');
                        });
                });
            })
            ->latest()
            ->get()
            ->map(function ($profile) use ($chambersMap) {
                $assignedIds = $profile->assignedChamberIds();
                
                // Format assigned chamber names
                $assignedNames = [];
                if (!empty($assignedIds)) {
                    foreach ($assignedIds as $cid) {
                        if (isset($chambersMap[$cid])) {
                            $assignedNames[] = $chambersMap[$cid]->name;
                        }
                    }
                }

                return [
                    'id' => $profile->id,
                    'user_id' => $profile->user_id,
                    'name' => $profile->user?->name,
                    'email' => $profile->user?->email,
                    'phone' => $profile->user?->phone,
                    'avatar' => $profile->user?->avatar,
                    'designation' => $profile->designation,
                    'chamber_id' => $profile->chamber_id,
                    'chamber_ids' => $assignedIds,
                    'chamber_names' => !empty($assignedNames) ? $assignedNames : ['All Chambers'],
                    'is_all_chambers' => empty($assignedIds),
                    'permissions' => $profile->permissions ?? [],
                    'is_active' => (bool) $profile->is_active,
                    'created_at' => $profile->created_at?->format('M d, Y'),
                ];
            });

        $limitCheck = \App\Services\SubscriptionLimitService::canCreateStaff($doctor);

        return Inertia::render('Staff/Index', [
            'staffList' => $staffProfiles,
            'chambers' => $chambers,
            'availablePermissions' => self::AVAILABLE_PERMISSIONS,
            'limitCheck' => $limitCheck,
            'filters' => [
                'search' => $search,
                'chamber_id' => $chamberFilter,
            ],
        ]);
    }

    /**
     * Store a newly created staff account.
     */
    public function store(Request $request): RedirectResponse
    {
        $doctor = Auth::user();

        $limitCheck = \App\Services\SubscriptionLimitService::canCreateStaff($doctor);
        if (!$limitCheck['allowed']) {
            return back()->with('error', $limitCheck['reason']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'nullable|string|max:20',
            'password' => ['required', Rules\Password::defaults()],
            'designation' => 'required|string|max:100',
            'chamber_ids' => 'nullable|array',
            'chamber_ids.*' => 'exists:chambers,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $chamberIds = !empty($validated['chamber_ids']) ? array_values(array_map('intval', $validated['chamber_ids'])) : null;
        $primaryChamberId = !empty($chamberIds) ? $chamberIds[0] : null;

        DB::transaction(function () use ($validated, $doctor, $chamberIds, $primaryChamberId) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => 'staff',
                'password' => Hash::make($validated['password']),
                'is_active' => $validated['is_active'] ?? true,
                'subscription_plan' => $doctor->subscription_plan,
                'subscription_status' => $doctor->subscription_status,
            ]);

            StaffProfile::create([
                'user_id' => $user->id,
                'doctor_id' => $doctor->id,
                'chamber_id' => $primaryChamberId,
                'chamber_ids' => $chamberIds,
                'designation' => $validated['designation'],
                'permissions' => $validated['permissions'] ?? [],
                'is_active' => $validated['is_active'] ?? true,
            ]);
        });

        return back()->with('success', 'Staff account created successfully with assigned permissions.');
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, StaffProfile $staff): RedirectResponse
    {
        if ($staff->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $user = $staff->user;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => ['nullable', Rules\Password::defaults()],
            'designation' => 'required|string|max:100',
            'chamber_ids' => 'nullable|array',
            'chamber_ids.*' => 'exists:chambers,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'is_active' => 'boolean',
        ]);

        $chamberIds = !empty($validated['chamber_ids']) ? array_values(array_map('intval', $validated['chamber_ids'])) : null;
        $primaryChamberId = !empty($chamberIds) ? $chamberIds[0] : null;

        DB::transaction(function () use ($validated, $staff, $user, $chamberIds, $primaryChamberId) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'is_active' => $validated['is_active'] ?? $staff->is_active,
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            $staff->update([
                'chamber_id' => $primaryChamberId,
                'chamber_ids' => $chamberIds,
                'designation' => $validated['designation'],
                'permissions' => $validated['permissions'] ?? [],
                'is_active' => $validated['is_active'] ?? $staff->is_active,
            ]);
        });

        return back()->with('success', 'Staff details and permissions updated successfully.');
    }

    /**
     * Toggle active / suspended status of a staff member.
     */
    public function toggleStatus(StaffProfile $staff): RedirectResponse
    {
        if ($staff->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $newStatus = !$staff->is_active;

        DB::transaction(function () use ($staff, $newStatus) {
            $staff->update(['is_active' => $newStatus]);
            $staff->user()->update(['is_active' => $newStatus]);
        });

        $statusLabel = $newStatus ? 'activated' : 'suspended';
        return back()->with('success', "Staff account {$staff->user?->name} has been {$statusLabel}.");
    }

    /**
     * Delete the specified staff member.
     */
    public function destroy(StaffProfile $staff): RedirectResponse
    {
        if ($staff->doctor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $staffName = $staff->user?->name;

        DB::transaction(function () use ($staff) {
            $user = $staff->user;
            $staff->delete();
            if ($user) {
                $user->delete();
            }
        });

        return back()->with('success', "Staff member '{$staffName}' removed successfully.");
    }
}