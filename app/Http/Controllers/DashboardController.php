<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Safely format diagnoses array/objects into a clean string.
     */
    private function formatDiagnoses(mixed $diagnoses): string
    {
        if (empty($diagnoses) || !is_array($diagnoses)) {
            return 'General Health Consultation';
        }

        $names = [];
        foreach ($diagnoses as $diag) {
            if (is_array($diag)) {
                $names[] = $diag['diagnosis'] ?? ($diag['name'] ?? null);
            } elseif (is_string($diag)) {
                $names[] = $diag;
            }
        }

        $names = array_filter($names);
        if (empty($names)) {
            return 'General Health Consultation';
        }

        return implode(', ', array_slice($names, 0, 2));
    }

    /**
     * Display the Doctor Practice & Prescription Dashboard.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Dedicated Patient Portal Experience
        if ($user->isPatient()) {
            $patient = $user->patient ?: Patient::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->orWhere('phone', $user->phone)
                ->first();

            $patientId = $patient?->id;

            $prescriptions = $patientId
                ? Prescription::with(['doctor', 'doctor.doctorProfile', 'chamber', 'items'])
                    ->where('patient_id', $patientId)
                    ->latest('prescription_date')
                    ->latest('id')
                    ->get()
                    ->map(function ($rx) {
                        return [
                            'id' => $rx->id,
                            'prescription_code' => $rx->prescription_code,
                            'prescription_date' => $rx->prescription_date?->format('M d, Y') ?? $rx->created_at->format('M d, Y'),
                            'doctor_name' => $rx->doctor?->name ?: 'Consulting Physician',
                            'doctor_title' => $rx->doctor?->doctorProfile?->title ?: 'Dr.',
                            'doctor_specialty' => $rx->doctor?->doctorProfile?->specialty ?: 'General Physician',
                            'doctor_degrees' => $rx->doctor?->doctorProfile?->degrees ?: [],
                            'chamber_name' => $rx->chamber?->name ?: 'Main Chamber',
                            'chamber_address' => $rx->chamber?->address,
                            'diagnoses' => $this->formatDiagnoses($rx->diagnoses),
                            'medicines' => $rx->items->map(function ($it) {
                                return [
                                    'name' => $it->name,
                                    'type' => $it->type,
                                    'strength' => $it->strength,
                                    'dose' => $it->dose,
                                    'duration' => $it->duration,
                                    'advice' => $it->advice,
                                ];
                            }),
                            'medicines_count' => $rx->items->count(),
                            'print_url' => route('prescriptions.print', $rx->id),
                        ];
                    })
                : collect();

            $appointments = $patientId
                ? Appointment::with(['doctor', 'doctor.doctorProfile', 'chamber'])
                    ->where('patient_id', $patientId)
                    ->latest('appointment_date')
                    ->latest('serial_no')
                    ->get()
                    ->map(function ($appt) {
                        $apptDate = Carbon::parse($appt->appointment_date);
                        return [
                            'id' => $appt->id,
                            'serial_no' => sprintf('%02d', $appt->serial_no),
                            'token_code' => $appt->token_code ?: sprintf('TK-%02d', $appt->serial_no),
                            'appointment_date' => $apptDate->format('M d, Y'),
                            'appointment_time' => $appt->appointment_time ? Carbon::parse($appt->appointment_time)->format('h:i A') : null,
                            'is_today' => $apptDate->isToday(),
                            'is_upcoming' => $apptDate->isFuture() || $apptDate->isToday(),
                            'doctor_name' => $appt->doctor?->name ?: 'Doctor',
                            'doctor_specialty' => $appt->doctor?->doctorProfile?->specialty ?: 'Physician',
                            'chamber_name' => $appt->chamber?->name ?: 'Chamber',
                            'chamber_address' => $appt->chamber?->address,
                            'chamber_phone' => $appt->chamber?->phone,
                            'status' => $appt->status,
                            'status_label' => ucfirst(str_replace('_', ' ', $appt->status)),
                            'vitals' => $appt->vitals,
                            'problem' => $appt->problem ?: 'General Consultation',
                        ];
                    })
                : collect();

            $upcomingAppointment = $appointments->firstWhere('is_upcoming', true);

            return Inertia::render('PatientPortal/Dashboard', [
                'patient' => [
                    'id' => $patient?->id,
                    'patient_code' => $patient?->patient_code ?: 'N/A',
                    'name' => $patient?->name ?: $user->name,
                    'email' => $patient?->email ?: $user->email,
                    'phone' => $patient?->phone ?: $user->phone,
                    'age' => $patient?->age,
                    'gender' => $patient?->gender ? ucfirst($patient->gender) : 'N/A',
                    'blood_group' => $patient?->blood_group ?: 'N/A',
                    'allergies' => $patient?->allergies ?: 'None known',
                    'medical_history' => $patient?->medical_history ?: 'None recorded',
                    'emergency_contact_name' => $patient?->emergency_contact_name,
                    'emergency_contact_phone' => $patient?->emergency_contact_phone,
                ],
                'prescriptions' => $prescriptions,
                'appointments' => $appointments,
                'upcomingAppointment' => $upcomingAppointment,
            ]);
        }

        $doctorId = $user->getDoctorId();
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // 1. KPI Metrics
        $todayPrescriptions = Prescription::where('doctor_id', $doctorId)
            ->whereDate('prescription_date', $today)
            ->count();

        $yesterdayPrescriptions = Prescription::where('doctor_id', $doctorId)
            ->whereDate('prescription_date', Carbon::yesterday())
            ->count();

        $prescriptionsChange = $yesterdayPrescriptions > 0
            ? round((($todayPrescriptions - $yesterdayPrescriptions) / $yesterdayPrescriptions) * 100, 1)
            : ($todayPrescriptions > 0 ? 100 : 0);

        $totalPrescriptions = Prescription::where('doctor_id', $doctorId)->count();
        $monthPrescriptions = Prescription::where('doctor_id', $doctorId)
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $totalPatients = Patient::where('doctor_id', $doctorId)->count();
        $newPatientsMonth = Patient::where('doctor_id', $doctorId)
            ->where('created_at', '>=', $thisMonth)
            ->count();

        $todayAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->count();

        $waitingAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->whereIn('status', ['waiting', 'scheduled', 'confirmed', 'in_progress'])
            ->count();

        // 2. Prescription Volume Trend (Last 7 Months)
        $monthlyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            $count = Prescription::where('doctor_id', $doctorId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $monthlyTrend[] = [
                'month' => $monthStart->format('M'),
                'count' => $count,
            ];
        }

        // 3. Top Diagnoses (Clinical Frequency)
        $allRx = Prescription::where('doctor_id', $doctorId)
            ->whereNotNull('diagnoses')
            ->get(['diagnoses']);

        $diagCounts = [];
        foreach ($allRx as $rx) {
            if (is_array($rx->diagnoses)) {
                foreach ($rx->diagnoses as $diag) {
                    $name = is_array($diag) ? ($diag['diagnosis'] ?? ($diag['name'] ?? null)) : (is_string($diag) ? $diag : null);
                    if ($name) {
                        $diagCounts[$name] = ($diagCounts[$name] ?? 0) + 1;
                    }
                }
            }
        }

        arsort($diagCounts);
        $topRaw = array_slice($diagCounts, 0, 5, true);

        $topDiagnoses = [];
        $maxDiag = !empty($topRaw) ? max($topRaw) : 1;
        foreach ($topRaw as $name => $count) {
            $topDiagnoses[] = [
                'name' => (string)$name,
                'count' => $count,
                'percentage' => round(($count / max(1, $totalPrescriptions)) * 100, 1),
                'barWidth' => min(100, round(($count / $maxDiag) * 100)) . '%',
            ];
        }

        if (empty($topDiagnoses)) {
            $topDiagnoses = [
                ['name' => 'Essential Hypertension', 'count' => 0, 'percentage' => 0, 'barWidth' => '85%'],
                ['name' => 'Type 2 Diabetes Mellitus', 'count' => 0, 'percentage' => 0, 'barWidth' => '65%'],
                ['name' => 'Acute Bronchitis / URTI', 'count' => 0, 'percentage' => 0, 'barWidth' => '50%'],
                ['name' => 'Acid Peptic Disorder (APD)', 'count' => 0, 'percentage' => 0, 'barWidth' => '40%'],
                ['name' => 'Migraine & Tension Headache', 'count' => 0, 'percentage' => 0, 'barWidth' => '25%'],
            ];
        }

        // 4. Live Patient Waiting Queue for Today
        $liveQueue = Appointment::with('patient')
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->orderBy('serial_no')
            ->take(10)
            ->get()
            ->map(function ($appt) {
                return [
                    'id' => $appt->id,
                    'serial_no' => sprintf('%02d', $appt->serial_no),
                    'token_code' => $appt->token_code ?: sprintf('TK-%02d', $appt->serial_no),
                    'patient_id' => $appt->patient_id,
                    'patient_name' => $appt->patient?->name ?: 'Walk-in Patient',
                    'patient_age' => $appt->patient?->age ? ($appt->patient->age . 'y') : 'N/A',
                    'patient_gender' => $appt->patient?->gender ? ucfirst($appt->patient->gender) : '',
                    'vitals' => $appt->vitals,
                    'bp' => $appt->vitals['bp'] ?? ($appt->vitals['blood_pressure'] ?? null),
                    'problem' => $appt->problem ?: 'Routine Clinical Consultation',
                    'status' => $appt->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $appt->status)),
                ];
            });

        // 5. Recent Prescriptions
        $recentPrescriptions = Prescription::with(['patient', 'items'])
            ->where('doctor_id', $doctorId)
            ->orderByDesc('created_at')
            ->take(6)
            ->get()
            ->map(function ($rx) {
                return [
                    'id' => $rx->id,
                    'prescription_code' => $rx->prescription_code,
                    'patient_id' => $rx->patient_id,
                    'patient_name' => $rx->patient?->name ?: 'Walk-in Patient',
                    'patient_age' => $rx->patient?->age ? ($rx->patient->age . 'y') : 'N/A',
                    'patient_gender' => $rx->patient?->gender ? ucfirst($rx->patient->gender) : '',
                    'diagnoses' => $this->formatDiagnoses($rx->diagnoses),
                    'medicines_count' => $rx->items->count(),
                    'date_formatted' => $rx->created_at->diffForHumans(),
                    'status' => $rx->status,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'today_prescriptions' => $todayPrescriptions,
                'prescriptions_change' => $prescriptionsChange,
                'total_prescriptions' => $totalPrescriptions,
                'month_prescriptions' => $monthPrescriptions,
                'total_patients' => $totalPatients,
                'new_patients_month' => $newPatientsMonth,
                'today_appointments' => $todayAppointments,
                'waiting_appointments' => $waitingAppointments,
            ],
            'monthlyTrend' => $monthlyTrend,
            'topDiagnoses' => $topDiagnoses,
            'liveQueue' => $liveQueue,
            'recentPrescriptions' => $recentPrescriptions,
        ]);
    }
}
