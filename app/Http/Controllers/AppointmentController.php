<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments and live queue.
     */
    public function index(Request $request): Response
    {
        if (!Auth::user()->hasPermission('appointments.manage')) {
            abort(403, 'You do not have permission to manage appointments.');
        }

        $doctorId = Auth::user()->getDoctorId();
        $date = $request->input('date', date('Y-m-d'));
        $chamberId = $request->input('chamber_id', session('active_chamber_id'));
        $status = $request->input('status');
        $search = $request->input('search');

        $chambers = Chamber::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->get();

        // If no chamber specified, select active or first
        if (!$chamberId && $chambers->isNotEmpty()) {
            $chamberId = $chambers->first()->id;
        }

        $query = Appointment::where('doctor_id', $doctorId)
            ->with(['patient', 'chamber'])
            ->whereDate('appointment_date', $date);

        if ($chamberId) {
            $query->where('chamber_id', $chamberId);
        }

        if ($status) {
            if ($status === 'waiting') {
                $query->whereIn('status', ['in_queue', 'scheduled']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%");
            });
        }

        $appointments = $query->orderBy('serial_no')->get();

        // Queue Statistics
        $baseStatsQuery = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date);

        if ($chamberId) {
            $baseStatsQuery->where('chamber_id', $chamberId);
        }

        $stats = [
            'total' => (clone $baseStatsQuery)->count(),
            'waiting' => (clone $baseStatsQuery)->whereIn('status', ['in_queue', 'scheduled'])->count(),
            'in_consultation' => (clone $baseStatsQuery)->where('status', 'in_consultation')->count(),
            'completed' => (clone $baseStatsQuery)->where('status', 'completed')->count(),
        ];

        // Patients list for instant autocomplete
        $allPatients = Patient::where('doctor_id', $doctorId)->orderBy('name')->get(['id', 'patient_code', 'name', 'phone', 'gender', 'age', 'blood_group']);

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
            'chambers' => $chambers,
            'patients' => $allPatients,
            'selectedDate' => $date,
            'selectedChamberId' => (int) $chamberId,
            'selectedStatus' => $status,
            'search' => $search,
            'stats' => $stats,
        ]);
    }


    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request): RedirectResponse
    {
        if (!Auth::user()->hasPermission('appointments.manage')) {
            abort(403, 'You do not have permission to manage appointments.');
        }

        return redirect()->route('appointments.index', array_merge($request->query(), ['book' => '1']));
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment): RedirectResponse
    {
        return redirect()->route('appointments.index');
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment): RedirectResponse
    {
        return redirect()->route('appointments.index');
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctorId = Auth::user()->getDoctorId();
        if ($appointment->doctor_id !== $doctorId) {
            abort(403);
        }

        $validated = $request->validate([
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'nullable|string|max:50',
            'type' => 'nullable|in:new_consultation,follow_up,report_review,emergency',
            'fee_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'nullable|in:paid,unpaid,partially_paid',
            'problem' => 'nullable|string|max:500',
            'doctor_notes' => 'nullable|string|max:500',
        ]);

        $appointment->update(array_filter($validated, fn($v) => !is_null($v)));

        return redirect()->back()->with('success', 'Appointment updated successfully!');
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!Auth::user()->hasPermission('appointments.manage')) {
            abort(403, 'You do not have permission to create appointments.');
        }

        $validated = $request->validate([
            'chamber_id' => 'required|exists:chambers,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'nullable|string|max:50',
            'type' => 'required|in:new_consultation,follow_up,report_review,emergency',
            'fee_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'payable_amount' => 'nullable|numeric|min:0',
            'problem' => 'nullable|string|max:500',
            'doctor_notes' => 'nullable|string|max:500',

            // Either existing patient ID or inline new patient
            'patient_id' => 'nullable|exists:patients,id',
            'new_patient_name' => 'required_without:patient_id|nullable|string|max:255',
            'new_patient_phone' => 'nullable|string|max:30',
            'new_patient_gender' => 'nullable|in:male,female,other',
            'new_patient_age' => 'nullable|integer|min:0|max:150',
            'new_patient_blood_group' => 'nullable|string|max:10',

            // Vitals
            'vitals' => 'nullable|array',
            'vitals.bp' => 'nullable|string|max:20',
            'vitals.weight' => 'nullable|string|max:20',
            'vitals.pulse' => 'nullable|string|max:20',
            'vitals.temperature' => 'nullable|string|max:20',
            'vitals.spo2' => 'nullable|string|max:20',
        ]);

        $doctorId = Auth::user()->getDoctorId();

        DB::transaction(function () use ($validated, $doctorId) {
            $patientId = $validated['patient_id'] ?? null;

            // If new patient, create on the fly
            if (!$patientId) {
                $newPatient = Patient::create([
                    'doctor_id' => $doctorId,
                    'name' => $validated['new_patient_name'],
                    'phone' => $validated['new_patient_phone'] ?? null,
                    'gender' => $validated['new_patient_gender'] ?? 'male',
                    'age' => $validated['new_patient_age'] ?? null,
                    'blood_group' => $validated['new_patient_blood_group'] ?? null,
                    'is_active' => true,
                ]);
                $patientId = $newPatient->id;
            }

            $feeAmount = floatval($validated['fee_amount'] ?? 0);
            $discountType = $validated['discount_type'] ?? 'fixed';
            $discountValue = floatval($validated['discount_value'] ?? 0);

            if ($discountType === 'percentage') {
                $discountCalculated = ($feeAmount * $discountValue) / 100;
            } else {
                $discountCalculated = $discountValue;
            }
            $payableAmount = max(0, $feeAmount - $discountCalculated);

            $paymentStatus = $request->input('payment_status', 'unpaid');
            if (!in_array($paymentStatus, ['paid', 'unpaid', 'partially_paid'])) {
                $paymentStatus = 'unpaid';
            }

            // Create appointment
            $appointment = Appointment::create([
                'doctor_id' => $doctorId,
                'chamber_id' => $validated['chamber_id'],
                'patient_id' => $patientId,
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'] ?? null,
                'type' => $validated['type'],
                'status' => 'in_queue',
                'fee_amount' => $feeAmount,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'payable_amount' => $payableAmount,
                'payment_status' => $paymentStatus,
                'vitals' => $validated['vitals'] ?? null,
                'problem' => $validated['problem'] ?? null,
                'doctor_notes' => $validated['doctor_notes'] ?? null,
            ]);

            // Track patient in chamber pivot
            $patient = Patient::find($patientId);
            if ($patient) {
                $patient->chambers()->syncWithoutDetaching([
                    $validated['chamber_id'] => [
                        'visit_count' => 1,
                        'last_visited_at' => now(),
                    ]
                ]);
            }
        });

        return redirect()->back()->with('success', 'Appointment booked and token generated successfully!');
    }

    /**
     * Quick update appointment status.
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctorId = Auth::user()->getDoctorId();
        if ($appointment->doctor_id !== $doctorId) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_queue,in_consultation,completed,canceled,no_show',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', "Appointment status updated to {$validated['status']}");
    }

    /**
     * Quick toggle appointment payment status between Paid and Unpaid.
     */
    public function togglePayment(Appointment $appointment): RedirectResponse
    {
        $doctorId = Auth::user()->getDoctorId();
        if ($appointment->doctor_id !== $doctorId) {
            abort(403);
        }

        $newStatus = $appointment->payment_status === 'paid' ? 'unpaid' : 'paid';
        $appointment->update(['payment_status' => $newStatus]);

        return redirect()->back()->with('success', "Payment status updated to " . strtoupper($newStatus));
    }

    /**
     * Quick update patient vitals recorded at queue.
     */
    public function updateVitals(Request $request, Appointment $appointment): RedirectResponse
    {
        $doctorId = Auth::user()->getDoctorId();
        if ($appointment->doctor_id !== $doctorId) {
            abort(403);
        }

        $validated = $request->validate([
            'vitals' => 'nullable|array',
            'vitals.bp' => 'nullable|string|max:20',
            'vitals.weight' => 'nullable|string|max:20',
            'vitals.pulse' => 'nullable|string|max:20',
            'vitals.temperature' => 'nullable|string|max:20',
            'problem' => 'nullable|string|max:500',
        ]);

        $appointment->update($validated);

        return redirect()->back()->with('success', 'Patient vitals updated successfully!');
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $doctorId = Auth::user()->getDoctorId();
        if ($appointment->doctor_id !== $doctorId) {
            abort(403);
        }

        $appointment->delete();

        return redirect()->back()->with('success', 'Appointment canceled and removed from queue.');
    }
}
