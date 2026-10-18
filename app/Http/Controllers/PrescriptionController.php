<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\ChiefComplaint;
use App\Models\ClinicalAdvice;
use App\Models\Diagnosis;
use App\Models\Drug;
use App\Models\DrugAdvice;
use App\Models\DrugDose;
use App\Models\DrugDuration;
use App\Models\DrugStrength;
use App\Models\DrugType;
use App\Models\Investigation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of prescriptions.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isPatient()) {
            return redirect()->route('dashboard');
        }

        if (!$user->hasPermission('prescriptions.view') && !$user->hasPermission('prescriptions.create')) {
            abort(403, 'You do not have permission to view prescriptions.');
        }

        $doctorId = $user->getDoctorId();
        $search = $request->input('search');
        $chamberId = $request->input('chamber_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Prescription::where('doctor_id', $doctorId)
            ->with(['patient', 'chamber', 'items']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('prescription_code', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('patient_code', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($chamberId) {
            $query->where('chamber_id', $chamberId);
        }

        if ($dateFrom) {
            $query->whereDate('prescription_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('prescription_date', '<=', $dateTo);
        }

        $prescriptions = $query->latest('prescription_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $chambers = Chamber::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->get(['id', 'name']);

        // Stats
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'today_count' => Prescription::where('doctor_id', $doctorId)->whereDate('prescription_date', $today)->count(),
            'month_count' => Prescription::where('doctor_id', $doctorId)->where('created_at', '>=', $thisMonth)->count(),
            'total_count' => Prescription::where('doctor_id', $doctorId)->count(),
        ];

        return Inertia::render('Prescriptions/Index', [
            'prescriptions' => $prescriptions,
            'chambers' => $chambers,
            'selectedChamberId' => $chamberId ? (int) $chamberId : null,
            'filters' => [
                'search' => $search,
                'chamber_id' => $chamberId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new prescription.
     */
    public function create(Request $request): Response
    {
        $user = Auth::user();

        if (!$user->hasPermission('prescriptions.create')) {
            abort(403, 'You do not have permission to create prescriptions.');
        }

        $doctorId = $user->getDoctorId();
        $patientId = $request->input('patient_id');
        $appointmentId = $request->input('appointment_id');
        $chamberId = $request->input('chamber_id', session('active_chamber_id'));

        $selectedPatient = $patientId ? Patient::where('doctor_id', $doctorId)->find($patientId) : null;
        $selectedAppointment = $appointmentId ? Appointment::with('patient')->where('doctor_id', $doctorId)->find($appointmentId) : null;

        if ($selectedAppointment && !$selectedPatient) {
            $selectedPatient = $selectedAppointment->patient;
        }

        $chambers = Chamber::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->get();

        $patients = Patient::where('doctor_id', $doctorId)
            ->latest()
            ->take(100)
            ->get(['id', 'name', 'patient_code', 'phone', 'gender', 'age', 'blood_group', 'allergies', 'medical_history']);

        $lookups = [
            'complaints' => ChiefComplaint::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'diagnoses' => Diagnosis::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'investigations' => Investigation::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'advices' => ClinicalAdvice::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'doses' => DrugDose::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'durations' => DrugDuration::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'drug_advices' => DrugAdvice::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
        ];

        $drugs = Drug::with('variations')
            ->where(function ($q) use ($doctorId) {
                $q->where('user_id', $doctorId)->orWhereNull('user_id');
            })
            ->where('is_active', true)
            ->orderBy('trade_name')
            ->get()
            ->map(function ($drug) {
                $firstVar = $drug->variations->first();
                return [
                    'id' => $drug->id,
                    'name' => $drug->trade_name,
                    'trade_name' => $drug->trade_name,
                    'generic_name' => $drug->generic_name,
                    'type' => $firstVar?->type ?? 'Tab.',
                    'strength' => $firstVar?->strength ?? '',
                    'default_dose' => $firstVar?->dose ?? '1+0+1',
                    'default_duration' => $firstVar?->duration ?? '7 Days',
                    'default_advice' => $firstVar?->advice ?? $drug->additional_advice ?? 'After Meal',
                ];
            });

        return Inertia::render('Prescriptions/Create', [
            'chambers' => $chambers,
            'activeChamberId' => $chamberId ? (int) $chamberId : ($chambers->first()?->id ?? null),
            'patients' => $patients,
            'selectedPatient' => $selectedPatient,
            'selectedAppointment' => $selectedAppointment,
            'lookups' => $lookups,
            'drugs' => $drugs,
        ]);
    }

    /**
     * Store a newly created prescription.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->hasPermission('prescriptions.create')) {
            abort(403, 'You do not have permission to create prescriptions.');
        }

        $doctorId = $user->getDoctorId();

        $validated = $request->validate([
            'chamber_id' => 'required|exists:chambers,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'prescription_date' => 'required|date',
            'vitals' => 'nullable|array',
            'chief_complaints' => 'nullable|array',
            'on_examinations' => 'nullable|array',
            'diagnoses' => 'nullable|array',
            'investigations' => 'nullable|array',
            'clinical_advices' => 'nullable|array',
            'follow_up_date' => 'nullable|date',
            'follow_up_notes' => 'nullable|string',
            'doctor_notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,finalized',
            'items' => 'nullable|array',
            'items.*.drug_id' => 'nullable|integer',
            'items.*.type' => 'nullable|string',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.generic_name' => 'nullable|string',
            'items.*.strength' => 'nullable|string',
            'items.*.dose' => 'nullable|string',
            'items.*.duration' => 'nullable|string',
            'items.*.advice' => 'nullable|string',
            'items.*.instructions' => 'nullable|string',
        ]);

        $prescription = DB::transaction(function () use ($validated, $doctorId) {
            $rx = Prescription::create([
                'doctor_id' => $doctorId,
                'chamber_id' => $validated['chamber_id'],
                'patient_id' => $validated['patient_id'],
                'appointment_id' => $validated['appointment_id'] ?? null,
                'prescription_date' => $validated['prescription_date'],
                'vitals' => $validated['vitals'] ?? [],
                'chief_complaints' => $validated['chief_complaints'] ?? [],
                'on_examinations' => $validated['on_examinations'] ?? [],
                'diagnoses' => $validated['diagnoses'] ?? [],
                'investigations' => $validated['investigations'] ?? [],
                'clinical_advices' => $validated['clinical_advices'] ?? [],
                'follow_up_date' => $validated['follow_up_date'] ?? null,
                'follow_up_notes' => $validated['follow_up_notes'] ?? null,
                'doctor_notes' => $validated['doctor_notes'] ?? null,
                'status' => $validated['status'] ?? 'finalized',
            ]);

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $index => $item) {
                    if (empty($item['name'])) continue;
                    PrescriptionItem::create([
                        'prescription_id' => $rx->id,
                        'drug_id' => $item['drug_id'] ?? null,
                        'type' => $item['type'] ?? 'Tab.',
                        'name' => $item['name'],
                        'generic_name' => $item['generic_name'] ?? null,
                        'strength' => $item['strength'] ?? null,
                        'dose' => $item['dose'] ?? null,
                        'duration' => $item['duration'] ?? null,
                        'advice' => $item['advice'] ?? null,
                        'instructions' => $item['instructions'] ?? null,
                        'item_order' => $index + 1,
                    ]);
                }
            }

            if (!empty($validated['appointment_id'])) {
                Appointment::where('id', $validated['appointment_id'])
                    ->where('doctor_id', $doctorId)
                    ->update(['status' => 'completed']);
            }

            return $rx;
        });

        return redirect()->route('prescriptions.print', $prescription->id)
            ->with('success', 'Prescription created successfully!');
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription): Response
    {
        $this->authorizePrescriptionAccess($prescription);

        $prescription->load([
            'doctor',
            'doctor.doctorProfile',
            'chamber',
            'patient',
            'items',
        ]);

        return Inertia::render('Prescriptions/Show', [
            'prescription' => $prescription,
        ]);
    }

    /**
     * Show the form for editing the specified prescription.
     */
    public function edit(Prescription $prescription): Response
    {
        $user = Auth::user();

        if (!$user->hasPermission('prescriptions.create')) {
            abort(403, 'You do not have permission to edit prescriptions.');
        }

        $doctorId = $user->getDoctorId();
        if ($prescription->doctor_id !== $doctorId) {
            abort(403, 'Unauthorized.');
        }

        $prescription->load(['patient', 'chamber', 'items']);

        $chambers = Chamber::where('doctor_id', $doctorId)->where('is_active', true)->get();
        $patients = Patient::where('doctor_id', $doctorId)->latest()->take(100)->get();

        $lookups = [
            'complaints' => ChiefComplaint::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'diagnoses' => Diagnosis::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'investigations' => Investigation::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'advices' => ClinicalAdvice::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'doses' => DrugDose::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'durations' => DrugDuration::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
            'drug_advices' => DrugAdvice::where('user_id', $doctorId)->orWhereNull('user_id')->orderBy('name')->pluck('name'),
        ];

        $drugs = Drug::with('variations')
            ->where(function ($q) use ($doctorId) {
                $q->where('user_id', $doctorId)->orWhereNull('user_id');
            })
            ->where('is_active', true)
            ->orderBy('trade_name')
            ->get()
            ->map(function ($drug) {
                $firstVar = $drug->variations->first();
                return [
                    'id' => $drug->id,
                    'name' => $drug->trade_name,
                    'trade_name' => $drug->trade_name,
                    'generic_name' => $drug->generic_name,
                    'type' => $firstVar?->type ?? 'Tab.',
                    'strength' => $firstVar?->strength ?? '',
                    'default_dose' => $firstVar?->dose ?? '1+0+1',
                    'default_duration' => $firstVar?->duration ?? '7 Days',
                    'default_advice' => $firstVar?->advice ?? $drug->additional_advice ?? 'After Meal',
                ];
            });

        return Inertia::render('Prescriptions/Edit', [
            'prescription' => $prescription,
            'chambers' => $chambers,
            'patients' => $patients,
            'lookups' => $lookups,
            'drugs' => $drugs,
        ]);
    }

    /**
     * Update the specified prescription.
     */
    public function update(Request $request, Prescription $prescription): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->hasPermission('prescriptions.create')) {
            abort(403, 'You do not have permission to edit prescriptions.');
        }

        $doctorId = $user->getDoctorId();
        if ($prescription->doctor_id !== $doctorId) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'chamber_id' => 'required|exists:chambers,id',
            'patient_id' => 'required|exists:patients,id',
            'prescription_date' => 'required|date',
            'vitals' => 'nullable|array',
            'chief_complaints' => 'nullable|array',
            'on_examinations' => 'nullable|array',
            'diagnoses' => 'nullable|array',
            'investigations' => 'nullable|array',
            'clinical_advices' => 'nullable|array',
            'follow_up_date' => 'nullable|date',
            'follow_up_notes' => 'nullable|string',
            'doctor_notes' => 'nullable|string',
            'status' => 'nullable|string|in:draft,finalized',
            'items' => 'nullable|array',
            'items.*.drug_id' => 'nullable|integer',
            'items.*.type' => 'nullable|string',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.generic_name' => 'nullable|string',
            'items.*.strength' => 'nullable|string',
            'items.*.dose' => 'nullable|string',
            'items.*.duration' => 'nullable|string',
            'items.*.advice' => 'nullable|string',
            'items.*.instructions' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $prescription) {
            $prescription->update([
                'chamber_id' => $validated['chamber_id'],
                'patient_id' => $validated['patient_id'],
                'prescription_date' => $validated['prescription_date'],
                'vitals' => $validated['vitals'] ?? [],
                'chief_complaints' => $validated['chief_complaints'] ?? [],
                'on_examinations' => $validated['on_examinations'] ?? [],
                'diagnoses' => $validated['diagnoses'] ?? [],
                'investigations' => $validated['investigations'] ?? [],
                'clinical_advices' => $validated['clinical_advices'] ?? [],
                'follow_up_date' => $validated['follow_up_date'] ?? null,
                'follow_up_notes' => $validated['follow_up_notes'] ?? null,
                'doctor_notes' => $validated['doctor_notes'] ?? null,
                'status' => $validated['status'] ?? 'finalized',
            ]);

            $prescription->items()->delete();

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $index => $item) {
                    if (empty($item['name'])) continue;
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'drug_id' => $item['drug_id'] ?? null,
                        'type' => $item['type'] ?? 'Tab.',
                        'name' => $item['name'],
                        'generic_name' => $item['generic_name'] ?? null,
                        'strength' => $item['strength'] ?? null,
                        'dose' => $item['dose'] ?? null,
                        'duration' => $item['duration'] ?? null,
                        'advice' => $item['advice'] ?? null,
                        'instructions' => $item['instructions'] ?? null,
                        'item_order' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()->route('prescriptions.print', $prescription->id)
            ->with('success', 'Prescription updated successfully!');
    }

    /**
     * Remove the specified prescription.
     */
    public function destroy(Prescription $prescription): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isDoctor() && !$user->isSuperAdmin()) {
            abort(403, 'Only doctors can delete prescriptions.');
        }

        if ($prescription->doctor_id !== $user->id && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        DB::transaction(function () use ($prescription) {
            $prescription->items()->delete();
            $prescription->delete();
        });

        return redirect()->route('prescriptions.index')
            ->with('success', 'Prescription deleted successfully.');
    }

    /**
     * Print physical prescription pad or export PDF view.
     */
    public function print(Prescription $prescription): Response
    {
        $this->authorizePrescriptionAccess($prescription);

        $prescription->load([
            'doctor',
            'doctor.doctorProfile',
            'chamber',
            'patient',
            'items',
        ]);

        return Inertia::render('Prescriptions/Print', [
            'prescription' => $prescription,
        ]);
    }

    /**
     * Check if the authenticated user has access to view/print this prescription.
     */
    private function authorizePrescriptionAccess(Prescription $prescription): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isDoctor() && $prescription->doctor_id === $user->id) {
            return;
        }

        if ($user->isStaff() && $prescription->doctor_id === $user->getDoctorId()) {
            return;
        }

        // Patient can view their own prescription
        if ($user->isPatient()) {
            $patient = $user->patient ?: Patient::where('user_id', $user->id)->orWhere('email', $user->email)->orWhere('phone', $user->phone)->first();
            if ($patient && $prescription->patient_id === $patient->id) {
                return;
            }
        }

        abort(403, 'Unauthorized access to this prescription.');
    }
}