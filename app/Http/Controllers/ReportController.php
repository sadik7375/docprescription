<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Parse date range filter into start and end Carbon instances.
     */
    private function parseDateRange(Request $request): array
    {
        $range = $request->input('date_range', 'this_month');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');

        switch ($range) {
            case 'today':
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
                break;
            case 'yesterday':
                $start = Carbon::yesterday()->startOfDay();
                $end = Carbon::yesterday()->endOfDay();
                break;
            case 'last_7_days':
                $start = Carbon::today()->subDays(6)->startOfDay();
                $end = Carbon::today()->endOfDay();
                break;
            case 'last_30_days':
                $start = Carbon::today()->subDays(29)->startOfDay();
                $end = Carbon::today()->endOfDay();
                break;
            case 'this_year':
                $start = Carbon::now()->startOfYear();
                $end = Carbon::now()->endOfYear();
                break;
            case 'custom':
                if ($customStart && $customEnd) {
                    $start = Carbon::parse($customStart)->startOfDay();
                    $end = Carbon::parse($customEnd)->endOfDay();
                } else {
                    $start = Carbon::now()->startOfMonth();
                    $end = Carbon::now()->endOfMonth();
                }
                break;
            case 'this_month':
            default:
                $range = 'this_month';
                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();
                break;
        }

        return [$start, $end, $range];
    }

    /**
     * Display Practice Reports & AI Analytics Dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $doctorId = $user->getDoctorId();
        $chamberId = $request->input('chamber_id');

        [$startDate, $endDate, $dateRange] = $this->parseDateRange($request);

        // Fetch Chambers for filter dropdown
        $chambers = Chamber::where('doctor_id', $doctorId)
            ->select('id', 'name', 'address', 'is_default')
            ->get();

        // 1. Appointments Query
        $appointmentQuery = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if (!empty($chamberId)) {
            $appointmentQuery->where('chamber_id', $chamberId);
        }

        $appointments = $appointmentQuery->with(['patient', 'chamber'])->latest('appointment_date')->get();

        // 2. Prescriptions Query
        $prescriptionQuery = Prescription::where('doctor_id', $doctorId)
            ->whereBetween('prescription_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if (!empty($chamberId)) {
            $prescriptionQuery->where('chamber_id', $chamberId);
        }

        $prescriptions = $prescriptionQuery->with(['patient', 'chamber', 'items'])->latest('prescription_date')->get();

        // 3. Appointments & Patient KPIs
        $totalAppointments = $appointments->count();
        $completedAppointments = $appointments->where('status', 'completed')->count();
        $scheduledAppointments = $appointments->whereIn('status', ['scheduled', 'confirmed'])->count();
        $cancelledAppointments = $appointments->where('status', 'cancelled')->count();

        // 4. Financial & Income KPIs
        $totalBilled = (float) $appointments->sum('payable_amount');
        $totalCollected = (float) $appointments->where('payment_status', 'paid')->sum('payable_amount');
        $totalPending = (float) $appointments->where('payment_status', 'unpaid')->sum('payable_amount');
        $collectionRate = $totalBilled > 0 ? round(($totalCollected / $totalBilled) * 100, 1) : 0;
        $avgFeePerPatient = $completedAppointments > 0 ? round($totalCollected / $completedAppointments, 2) : 0;

        // Daily Income Breakdown for Chart
        $dailyIncome = $appointments->where('payment_status', 'paid')
            ->groupBy(function ($app) {
                return Carbon::parse($app->appointment_date)->format('M d');
            })
            ->map(function ($group) {
                return (float) $group->sum('payable_amount');
            });

        // 5. Patient Demographics & Retention
        $uniquePatientIds = $appointments->pluck('patient_id')->merge($prescriptions->pluck('patient_id'))->unique()->filter();
        $totalPatientsVisited = $uniquePatientIds->count();

        $newPatientsCount = Patient::where('doctor_id', $doctorId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $returningPatientsCount = max(0, $totalPatientsVisited - $newPatientsCount);

        // Gender distribution of visited patients
        $visitedPatients = Patient::whereIn('id', $uniquePatientIds)->get();
        $genderBreakdown = [
            'male' => $visitedPatients->where('gender', 'male')->count(),
            'female' => $visitedPatients->where('gender', 'female')->count(),
            'other' => $visitedPatients->whereNotIn('gender', ['male', 'female'])->count(),
        ];

        // 6. Running / Top Prescribed Medications (Active / Ongoing Medications)
        $prescriptionIds = $prescriptions->pluck('id');
        $topMedications = PrescriptionItem::whereIn('prescription_id', $prescriptionIds)
            ->select(
                'medicine_name',
                'generic_name',
                'dosage_form',
                'strength',
                'dosage',
                'duration',
                DB::raw('COUNT(*) as total_prescribed')
            )
            ->groupBy('medicine_name', 'generic_name', 'dosage_form', 'strength', 'dosage', 'duration')
            ->orderByDesc('total_prescribed')
            ->get()
            ->groupBy('medicine_name')
            ->map(function ($items, $medicineName) use ($prescriptions) {
                $totalCount = $items->sum('total_prescribed');
                $topItem = $items->first();
                $totalRxCount = max(1, $prescriptions->count());
                $percentage = round(($totalCount / $totalRxCount) * 100, 1);

                return [
                    'medicine_name' => $medicineName,
                    'generic_name' => $topItem->generic_name ?? 'N/A',
                    'dosage_form' => $topItem->dosage_form ?? 'Tablet',
                    'strength' => $topItem->strength ?? '',
                    'common_dosage' => $topItem->dosage ?? '1+0+1',
                    'common_duration' => $topItem->duration ?? '7 days',
                    'total_prescribed' => $totalCount,
                    'prescription_percentage' => $percentage,
                ];
            })
            ->sortByDesc('total_prescribed')
            ->values()
            ->take(25);

        // 7. Top Diagnoses & Clinical Conditions
        $diagnosesList = [];
        foreach ($prescriptions as $rx) {
            if (!empty($rx->diagnoses) && is_array($rx->diagnoses)) {
                foreach ($rx->diagnoses as $diag) {
                    $name = is_array($diag) ? ($diag['diagnosis'] ?? ($diag['name'] ?? null)) : (string) $diag;
                    if ($name) {
                        $name = trim($name);
                        $diagnosesList[$name] = ($diagnosesList[$name] ?? 0) + 1;
                    }
                }
            }
        }
        arsort($diagnosesList);
        $topDiagnoses = collect($diagnosesList)->take(10)->map(function ($count, $name) use ($prescriptions) {
            $totalRx = max(1, $prescriptions->count());
            return [
                'diagnosis' => $name,
                'count' => $count,
                'percentage' => round(($count / $totalRx) * 100, 1),
            ];
        })->values();

        // 8. Generate AI Practice & Clinical Intelligence Summary
        $aiSummary = $this->generateAiPracticeSummary([
            'date_range_label' => ucfirst(str_replace('_', ' ', $dateRange)),
            'start_date' => $startDate->format('M d, Y'),
            'end_date' => $endDate->format('M d, Y'),
            'total_appointments' => $totalAppointments,
            'completed_appointments' => $completedAppointments,
            'total_prescriptions' => $prescriptions->count(),
            'total_income' => $totalCollected,
            'total_billed' => $totalBilled,
            'collection_rate' => $collectionRate,
            'total_patients' => $totalPatientsVisited,
            'new_patients' => $newPatientsCount,
            'returning_patients' => $returningPatientsCount,
            'top_medicines' => $topMedications->take(5)->pluck('medicine_name')->toArray(),
            'top_diagnoses' => $topDiagnoses->take(5)->pluck('diagnosis')->toArray(),
        ]);

        return Inertia::render('Reports/Index', [
            'filters' => [
                'date_range' => $dateRange,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'chamber_id' => $chamberId ? (int) $chamberId : null,
            ],
            'chambers' => $chambers,
            'kpis' => [
                'total_income' => $totalCollected,
                'total_billed' => $totalBilled,
                'total_pending' => $totalPending,
                'collection_rate' => $collectionRate,
                'avg_fee' => $avgFeePerPatient,
                'total_appointments' => $totalAppointments,
                'completed_appointments' => $completedAppointments,
                'scheduled_appointments' => $scheduledAppointments,
                'cancelled_appointments' => $cancelledAppointments,
                'total_prescriptions' => $prescriptions->count(),
                'total_patients' => $totalPatientsVisited,
                'new_patients' => $newPatientsCount,
                'returning_patients' => $returningPatientsCount,
                'gender_breakdown' => $genderBreakdown,
            ],
            'daily_income' => $dailyIncome,
            'top_medications' => $topMedications,
            'top_diagnoses' => $topDiagnoses,
            'appointments_log' => $appointments->take(50)->map(function ($app) {
                return [
                    'id' => $app->id,
                    'serial_no' => $app->serial_no,
                    'token_code' => $app->token_code,
                    'date' => Carbon::parse($app->appointment_date)->format('M d, Y'),
                    'time' => $app->appointment_time ? Carbon::parse($app->appointment_time)->format('h:i A') : 'N/A',
                    'patient_name' => $app->patient->name ?? 'Walk-in Patient',
                    'patient_phone' => $app->patient->phone ?? 'N/A',
                    'patient_age_gender' => ($app->patient->age ? $app->patient->age . 'y / ' : '') . ($app->patient->gender ? ucfirst($app->patient->gender) : 'N/A'),
                    'chamber_name' => $app->chamber->name ?? 'Default Chamber',
                    'type' => ucfirst(str_replace('_', ' ', $app->type ?? 'new')),
                    'status' => $app->status,
                    'payable_amount' => (float) $app->payable_amount,
                    'payment_status' => $app->payment_status,
                ];
            }),
            'ai_summary' => $aiSummary,
        ]);
    }

    /**
     * AI Clinical & Practice Analytics Synthesizer.
     */
    private function generateAiPracticeSummary(array $data): array
    {
        // Handle clean empty state if no appointments or prescriptions exist in this period
        if (($data['total_patients'] ?? 0) === 0 && ($data['total_appointments'] ?? 0) === 0 && ($data['total_prescriptions'] ?? 0) === 0) {
            return [
                'headline' => "Practice Performance Report ({$data['start_date']} - {$data['end_date']})",
                'status' => "No Activity Recorded",
                'summary' => "No patient consultations or prescription records were logged during this selected date range ({$data['date_range_label']}). As you schedule appointments and issue prescriptions, AI will synthesize your revenue efficiency, patient retention, and diagnostic trends here.",
                'clinical_insight' => "No diagnostic or prescription data available in this timeframe.",
                'recommendations' => [
                    "Schedule upcoming patient appointments from the Appointments or Chamber module.",
                    "Create prescriptions to unlock automated diagnostic clustering and medication analytics.",
                ],
                'generated_at' => now()->format('M d, Y h:i A'),
            ];
        }

        $aiEnabled = SystemSetting::get('ai_enabled', true);
        $provider = SystemSetting::get('ai_provider', 'gemini');

        // 1. Try Live LLM (Gemini / OpenAI) if configured
        if ($aiEnabled && $provider !== 'offline') {
            if ($provider === 'gemini') {
                $llmReport = $this->queryGeminiReportSummary($data);
                if ($llmReport !== null) {
                    return $llmReport;
                }
            } elseif ($provider === 'openai') {
                $llmReport = $this->queryOpenAiReportSummary($data);
                if ($llmReport !== null) {
                    return $llmReport;
                }
            }
        }

        // 2. Intelligent Data-Driven Rule Synthesizer (Offline & 100% Reliable)
        $topMedStr = !empty($data['top_medicines']) ? implode(', ', $data['top_medicines']) : 'Routine Therapeutics';
        $topDiagStr = !empty($data['top_diagnoses']) ? implode(', ', $data['top_diagnoses']) : 'General Consultations';

        $retentionRate = $data['total_patients'] > 0
            ? round(($data['returning_patients'] / $data['total_patients']) * 100, 1)
            : 0;

        $headline = "Practice Performance Report ({$data['start_date']} - {$data['end_date']})";
        
        $status = ($data['completed_appointments'] > 10 || $data['total_prescriptions'] > 10) 
            ? "High-Velocity & Active Practice" 
            : "Steady Operational Volume";

        $summary = "During this {$data['date_range_label']} period, your practice served {$data['total_patients']} unique patients ({$data['new_patients']} new registrations and {$data['returning_patients']} returning follow-ups). A total of {$data['completed_appointments']} consultations were completed and {$data['total_prescriptions']} prescriptions were issued, generating " . number_format($data['total_income'], 2) . " in realized revenue with a {$data['collection_rate']}% collection efficiency.";

        $clinicalInsight = "The most frequent clinical presentations in this window were led by {$topDiagStr}. Correspondingly, your primary running pharmacotherapy cluster was driven by {$topMedStr}.";

        $actionRecommendations = [
            "Patient Retention: Your follow-up return rate stands at {$retentionRate}%. Setting automated SMS reminders for scheduled follow-up dates can improve patient adherence.",
            "Medicine Inventory: The highest prescribed running drug is " . ($data['top_medicines'][0] ?? 'primary medication') . ". Ensure adequate supply and verify therapeutic responses during subsequent follow-ups.",
            "Revenue Optimization: Realized collection rate is {$data['collection_rate']}%. Resolving pending payments can add up to " . number_format($data['total_billed'] - $data['total_income'], 2) . " to chamber revenue.",
        ];

        return [
            'headline' => $headline,
            'status' => $status,
            'summary' => $summary,
            'clinical_insight' => $clinicalInsight,
            'recommendations' => $actionRecommendations,
            'generated_at' => now()->format('M d, Y h:i A'),
        ];
    }

    /**
     * Query Google Gemini for Comprehensive Practice & Revenue Analytics.
     */
    private function queryGeminiReportSummary(array $data): ?array
    {
        try {
            $apiKey = SystemSetting::get('gemini_api_key', env('GEMINI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
            $prompt = "You are an expert clinical practice management consultant and healthcare executive. Analyze the doctor's practice metrics for period {$data['start_date']} to {$data['end_date']}. Return strictly a JSON object: { \"headline\": string, \"status\": string, \"summary\": string, \"clinical_insight\": string, \"recommendations\": [string, string, string] }. Data: " . json_encode($data);

            $response = Http::timeout(10)->post($endpoint, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.2,
                ],
            ]);

            if ($response->successful()) {
                $resData = $response->json();
                $text = $resData['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $parsed = json_decode($text, true);
                    if ($parsed && isset($parsed['headline'], $parsed['summary'], $parsed['recommendations'])) {
                        $parsed['generated_at'] = now()->format('M d, Y h:i A') . ' (via Gemini AI)';
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini Report Summary Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query OpenAI for Comprehensive Practice & Revenue Analytics.
     */
    private function queryOpenAiReportSummary(array $data): ?array
    {
        try {
            $apiKey = SystemSetting::get('openai_api_key', env('OPENAI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $prompt = "You are an expert clinical practice management consultant and healthcare executive. Analyze the doctor's practice metrics for period {$data['start_date']} to {$data['end_date']}. Return strictly a JSON object: { \"headline\": string, \"status\": string, \"summary\": string, \"clinical_insight\": string, \"recommendations\": [string, string, string] }. Data: " . json_encode($data);

            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a healthcare analytics JSON assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
            ]);

            if ($response->successful()) {
                $resData = $response->json();
                $content = $resData['choices'][0]['message']['content'] ?? null;
                if ($content) {
                    $parsed = json_decode($content, true);
                    if ($parsed && isset($parsed['headline'], $parsed['summary'], $parsed['recommendations'])) {
                        $parsed['generated_at'] = now()->format('M d, Y h:i A') . ' (via OpenAI GPT-4o-mini)';
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI Report Summary Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Export Reports Data to UTF-8 CSV with Excel compatibility.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $doctorId = $user->getDoctorId();
        $type = $request->input('type', 'all');
        $chamberId = $request->input('chamber_id');

        [$startDate, $endDate, $dateRange] = $this->parseDateRange($request);

        $filename = "practice_report_{$type}_" . $startDate->format('Ymd') . "_to_" . $endDate->format('Ymd') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($doctorId, $chamberId, $startDate, $endDate, $type) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel Bengali/Unicode support
            fwrite($handle, "\xEF\xBB\xBF");

            if ($type === 'medicines') {
                // Running Medications Report
                fputcsv($handle, ['Medicine Name', 'Generic / Molecule', 'Dosage Form', 'Strength', 'Common Dosage', 'Common Duration', 'Total Times Prescribed']);

                $prescriptionIds = Prescription::where('doctor_id', $doctorId)
                    ->whereBetween('prescription_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->when($chamberId, fn($q) => $q->where('chamber_id', $chamberId))
                    ->pluck('id');

                $items = PrescriptionItem::whereIn('prescription_id', $prescriptionIds)
                    ->select('medicine_name', 'generic_name', 'dosage_form', 'strength', 'dosage', 'duration', DB::raw('COUNT(*) as total'))
                    ->groupBy('medicine_name', 'generic_name', 'dosage_form', 'strength', 'dosage', 'duration')
                    ->orderByDesc('total')
                    ->get();

                foreach ($items as $item) {
                    fputcsv($handle, [
                        $item->medicine_name,
                        $item->generic_name ?? 'N/A',
                        $item->dosage_form ?? 'Tablet',
                        $item->strength ?? '',
                        $item->dosage ?? '',
                        $item->duration ?? '',
                        $item->total,
                    ]);
                }
            } elseif ($type === 'appointments' || $type === 'income') {
                // Appointments & Income Report
                fputcsv($handle, ['Date', 'Token / Serial', 'Patient Name', 'Phone', 'Age', 'Gender', 'Chamber', 'Type', 'Status', 'Payable Fee', 'Payment Status']);

                $appointments = Appointment::where('doctor_id', $doctorId)
                    ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->when($chamberId, fn($q) => $q->where('chamber_id', $chamberId))
                    ->with(['patient', 'chamber'])
                    ->latest('appointment_date')
                    ->get();

                foreach ($appointments as $app) {
                    fputcsv($handle, [
                        Carbon::parse($app->appointment_date)->format('Y-m-d'),
                        $app->token_code ?? $app->serial_no,
                        $app->patient->name ?? 'Walk-in',
                        $app->patient->phone ?? '',
                        $app->patient->age ?? '',
                        $app->patient->gender ?? '',
                        $app->chamber->name ?? '',
                        ucfirst($app->type ?? 'new'),
                        ucfirst($app->status ?? 'scheduled'),
                        $app->payable_amount,
                        ucfirst($app->payment_status ?? 'unpaid'),
                    ]);
                }
            } else {
                // Combined Practice Summary
                fputcsv($handle, ['--- PRACTICE EXECUTIVE SUMMARY ---']);
                fputcsv($handle, ['Report Range', $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d')]);
                fputcsv($handle, ['Generated On', now()->toDateTimeString()]);
                fputcsv($handle, []);

                // Top Metrics
                $apps = Appointment::where('doctor_id', $doctorId)
                    ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->when($chamberId, fn($q) => $q->where('chamber_id', $chamberId))
                    ->get();

                $rxs = Prescription::where('doctor_id', $doctorId)
                    ->whereBetween('prescription_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->when($chamberId, fn($q) => $q->where('chamber_id', $chamberId))
                    ->get();

                fputcsv($handle, ['Metric', 'Value']);
                fputcsv($handle, ['Total Appointments', $apps->count()]);
                fputcsv($handle, ['Completed Consultations', $apps->where('status', 'completed')->count()]);
                fputcsv($handle, ['Total Prescriptions (Rx)', $rxs->count()]);
                fputcsv($handle, ['Total Billed Revenue', $apps->sum('payable_amount')]);
                fputcsv($handle, ['Total Realized Income', $apps->where('payment_status', 'paid')->sum('payable_amount')]);
                fputcsv($handle, ['Pending Collections', $apps->where('payment_status', 'unpaid')->sum('payable_amount')]);
                fputcsv($handle, []);

                // Running Medicines Breakdown
                fputcsv($handle, ['--- TOP RUNNING MEDICATIONS ---']);
                fputcsv($handle, ['Medicine Name', 'Generic Name', 'Dosage Form', 'Strength', 'Prescription Count']);

                $prescriptionIds = $rxs->pluck('id');
                $items = PrescriptionItem::whereIn('prescription_id', $prescriptionIds)
                    ->select('medicine_name', 'generic_name', 'dosage_form', 'strength', DB::raw('COUNT(*) as total'))
                    ->groupBy('medicine_name', 'generic_name', 'dosage_form', 'strength')
                    ->orderByDesc('total')
                    ->take(30)
                    ->get();

                foreach ($items as $item) {
                    fputcsv($handle, [
                        $item->medicine_name,
                        $item->generic_name ?? 'N/A',
                        $item->dosage_form ?? '',
                        $item->strength ?? '',
                        $item->total,
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}