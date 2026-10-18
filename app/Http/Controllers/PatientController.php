<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request): Response
    {
        if (!Auth::user()->hasPermission('patients.manage')) {
            abort(403, 'You do not have permission to view patients.');
        }

        $doctorId = Auth::user()->getDoctorId();
        $search = $request->input('search');
        $gender = $request->input('gender');
        $bloodGroup = $request->input('blood_group');
        $chamberId = $request->input('chamber_id');
        $hasAccount = $request->input('has_account');

        $query = Patient::where('doctor_id', $doctorId)->with(['user', 'chambers']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($gender) {
            $query->where('gender', $gender);
        }

        if ($bloodGroup) {
            $query->where('blood_group', $bloodGroup);
        }

        if ($hasAccount === 'yes') {
            $query->whereNotNull('user_id');
        } elseif ($hasAccount === 'no') {
            $query->whereNull('user_id');
        }

        if ($chamberId) {
            $query->whereHas('chambers', function ($q) use ($chamberId) {
                $q->where('chambers.id', $chamberId);
            });
        }

        $patients = $query->latest()->paginate(15)->withQueryString();

        // Summary Statistics
        $stats = [
            'total' => Patient::count(),
            'male' => Patient::where('gender', 'male')->count(),
            'female' => Patient::where('gender', 'female')->count(),
            'portal_users' => Patient::whereNotNull('user_id')->count(),
        ];

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters' => [
                'search' => $search,
                'gender' => $gender,
                'blood_group' => $bloodGroup,
                'has_account' => $hasAccount,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create(): Response
    {
        return Inertia::render('Patients/Create');
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'gender' => 'required|in:male,female,other',
            'age' => 'nullable|integer|min:0|max:150',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'marital_status' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'allergies' => 'nullable|string|max:1000',
            'medical_history' => 'nullable|string|max:2000',
            'note' => 'nullable|string|max:1000',

            // Account toggle
            'create_account' => 'boolean',
            'account_email' => 'required_if:create_account,true|nullable|email|max:255|unique:users,email',
            'account_password' => 'required_if:create_account,true|nullable|string|min:6|confirmed',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $userId = null;

            // Conditionally create portal account if toggled
            if (!empty($validated['create_account']) && !empty($validated['account_email'])) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['account_email'],
                    'phone' => $validated['phone'] ?? null,
                    'password' => Hash::make($validated['account_password']),
                    'role' => 'patient',
                    'is_active' => true,
                ]);
                $userId = $user->id;
            }

            $patient = Patient::create([
                'doctor_id' => Auth::user()->getDoctorId(),
                'user_id' => $userId,
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? ($validated['account_email'] ?? null),
                'gender' => $validated['gender'],
                'age' => $validated['age'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'marital_status' => $validated['marital_status'] ?? null,
                'address' => $validated['address'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'medical_history' => $validated['medical_history'] ?? null,
                'note' => $validated['note'] ?? null,
                'is_active' => true,
            ]);

            // Auto-attach to current active chamber
            $activeChamberId = session('active_chamber_id');
            if ($activeChamberId) {
                $patient->chambers()->syncWithoutDetaching([
                    $activeChamberId => [
                        'visit_count' => 1,
                        'last_visited_at' => now(),
                    ]
                ]);
            }
        });

        return redirect()->route('patients.index')->with('success', 'Patient registered successfully!');
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient): Response
    {
        $patient->load([
            'user',
            'doctor',
            'chambers',
            'prescriptions' => function ($q) {
                $q->with(['items', 'chamber'])->latest('prescription_date')->latest('id');
            },
            'appointments' => function ($q) {
                $q->with('chamber')->latest('appointment_date')->latest('serial_no');
            }
        ]);

        return Inertia::render('Patients/Show', [
            'patient' => $patient,
        ]);
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient): Response
    {
        $patient->load('user');

        return Inertia::render('Patients/Edit', [
            'patient' => $patient,
        ]);
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'gender' => 'required|in:male,female,other',
            'age' => 'nullable|integer|min:0|max:150',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string|max:10',
            'marital_status' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'allergies' => 'nullable|string|max:1000',
            'medical_history' => 'nullable|string|max:2000',
            'note' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($patient, $validated) {
            $patient->update($validated);

            // Sync user details if portal account exists
            if ($patient->user) {
                $patient->user->update([
                    'name' => $validated['name'],
                    'phone' => $validated['phone'] ?? $patient->user->phone,
                    'is_active' => $validated['is_active'] ?? true,
                ]);
            }
        });

        return redirect()->route('patients.show', $patient->id)->with('success', 'Patient updated successfully!');
    }

    /**
     * Generate a Portal Login Account for an existing patient.
     */
    public function createAccount(Request $request, Patient $patient): RedirectResponse
    {
        if ($patient->user_id) {
            return redirect()->back()->with('error', 'This patient already has a portal login account.');
        }

        $validated = $request->validate([
            'account_email' => 'required|email|max:255|unique:users,email',
            'account_password' => 'required|string|min:6|confirmed',
        ]);

        DB::transaction(function () use ($patient, $validated) {
            $user = User::create([
                'name' => $patient->name,
                'email' => $validated['account_email'],
                'phone' => $patient->phone,
                'password' => Hash::make($validated['account_password']),
                'role' => 'patient',
                'is_active' => true,
            ]);

            $patient->update([
                'user_id' => $user->id,
                'email' => $patient->email ?? $validated['account_email'],
            ]);
        });

        return redirect()->back()->with('success', 'Portal account created successfully for this patient!');
    }

    /**
     * Remove the specified patient from storage.
     */
    public function destroy(Patient $patient): RedirectResponse
    {
        DB::transaction(function () use ($patient) {
            if ($patient->user) {
                $patient->user->delete();
            }
            $patient->delete();
        });

        return redirect()->route('patients.index')->with('success', 'Patient deleted successfully!');
    }
}
