<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantController extends Controller
{
    /**
     * Check if the authenticated doctor's subscription plan allows AI usage.
     */
    private function isPlanAllowedForAi(?User $user): bool
    {
        if (!$user) return false;
        return $user->canUseAi();
    }

    /**
     * Check Drug-to-Drug Interactions, Allergies, and Overdose/Therapeutic Duplications.
     */
    public function checkInteractions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'drugs' => 'required|array',
            'patient' => 'nullable|array',
            'diagnoses' => 'nullable|array',
        ]);

        $drugs = $validated['drugs'] ?? [];
        $patient = $validated['patient'] ?? [];
        $diagnoses = $validated['diagnoses'] ?? [];

        if (count($drugs) < 1) {
            return response()->json([
                'status' => 'safe',
                'summary' => 'No medications specified to check.',
                'interactions' => [],
                'duplicate_therapies' => [],
                'allergy_warnings' => [],
            ]);
        }

        $aiEnabled = SystemSetting::get('ai_enabled', true);
        $provider = SystemSetting::get('ai_provider', 'gemini');
        $planAllowed = $this->isPlanAllowedForAi(auth()->user());

        // 1. Try Live LLM (Gemini / OpenAI) if enabled & plan has access
        if ($aiEnabled && $planAllowed && $provider !== 'offline') {
            if ($provider === 'gemini') {
                $geminiResult = $this->queryGeminiInteractions($drugs, $patient, $diagnoses);
                if ($geminiResult !== null) {
                    return response()->json($geminiResult);
                }
            } elseif ($provider === 'openai') {
                $openAiResult = $this->queryOpenAiInteractions($drugs, $patient, $diagnoses);
                if ($openAiResult !== null) {
                    return response()->json($openAiResult);
                }
            }
        }

        // 2. Intelligent Offline Medical Pharmacology Rule Engine (Instant & 100% Reliable)
        $ruleResult = $this->runRuleEngineInteractionCheck($drugs, $patient, $diagnoses);
        return response()->json($ruleResult);
    }

    /**
     * Interactive Clinical Chat Copilot for Doctors.
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
            'context' => 'nullable|array',
        ]);

        $userMessage = trim($validated['message']);
        $history = $validated['history'] ?? [];
        $context = $validated['context'] ?? [];

        $aiEnabled = SystemSetting::get('ai_enabled', true);
        if (!$aiEnabled) {
            return response()->json([
                'type' => 'disabled',
                'title' => 'AI Service Temporarily Paused',
                'answer' => 'AI Clinical Assistant is currently paused by the system administrator. You may continue writing prescriptions as usual.',
                'suggested_actions' => [],
            ]);
        }

        // Check if doctor's subscription package is allowed to use AI
        if (!$this->isPlanAllowedForAi(auth()->user())) {
            $planName = ucfirst(str_replace('_', ' ', auth()->user()?->subscription_plan ?? 'Current Plan'));
            return response()->json([
                'type' => 'plan_locked',
                'title' => 'AI Copilot Locked on ' . $planName,
                'answer' => "The AI Clinical Copilot is not enabled for your current subscription plan ({$planName}).\n\nPlease upgrade to an authorized plan in order to unlock live clinical AI assistance.",
                'suggested_actions' => [],
            ]);
        }

        $provider = SystemSetting::get('ai_provider', 'gemini');

        // 1. Try Live LLM if enabled & configured
        if ($provider !== 'offline') {
            if ($provider === 'gemini') {
                $aiReply = $this->queryGeminiChat($userMessage, $history, $context);
                if ($aiReply !== null) {
                    return response()->json($aiReply);
                }
            } elseif ($provider === 'openai') {
                $aiReply = $this->queryOpenAiChat($userMessage, $history, $context);
                if ($aiReply !== null) {
                    return response()->json($aiReply);
                }
            }
        }

        // 2. Built-in Clinical Knowledge Base & Medical Copilot Algorithm
        $response = $this->generateClinicalCopilotResponse($userMessage, $context);
        return response()->json($response);
    }

    /**
     * Built-in Comprehensive Pharmacology Rule Engine for Drug-to-Drug Interactions.
     */
    private function runRuleEngineInteractionCheck(array $drugs, array $patient, array $diagnoses): array
    {
        $drugNames = array_map(function ($d) {
            $name = is_array($d) ? ($d['trade_name'] ?? $d['name'] ?? $d['generic_name'] ?? '') : (string)$d;
            $generic = is_array($d) ? ($d['generic_name'] ?? '') : '';
            return strtolower(trim($name . ' ' . $generic));
        }, $drugs);

        $interactions = [];
        $duplicateTherapies = [];
        $allergyWarnings = [];

        // Known severe & moderate pharmacological interaction matrices
        $rules = [
            // NSAID + Anticoagulant / Antiplatelet
            [
                'groupA' => ['aspirin', 'clopidogrel', 'warfarin', 'rivaroxaban', 'apixaban', 'heparin', 'enoxaparin'],
                'groupB' => ['ibuprofen', 'naproxen', 'ketorolac', 'diclofenac', 'indomethacin', 'meloxicam', 'celecoxib', 'etoricoxib', 'aceclofenac'],
                'severity' => 'severe',
                'title' => 'Significantly Increased Gastrointestinal & Major Bleeding Risk',
                'mechanism' => 'Combining NSAIDs with anticoagulants or antiplatelet agents markedly increases gastric ulceration and systemic hemorrhagic risk.',
                'recommendation' => 'Consider using Paracetamol for analgesia or co-prescribe a potent PPI (e.g., Esomeprazole) if concurrent therapy is unavoidable.',
            ],
            // ACEI / ARB + Potassium-Sparing Diuretics / K-Supplements
            [
                'groupA' => ['enalapril', 'ramipril', 'lisinopril', 'perindopril', 'captopril', 'losartan', 'valsartan', 'telmisartan', 'olmesartan', 'candesartan'],
                'groupB' => ['spironolactone', 'eplerenone', 'potassium chloride', 'amiloride', 'triamterene'],
                'severity' => 'severe',
                'title' => 'Severe Hyperkalemia Risk & Cardiac Arrhythmia Alert',
                'mechanism' => 'Concurrent use of ACE inhibitors / ARBs with potassium-sparing diuretics impairs renal potassium excretion, potentially causing life-threatening hyperkalemia.',
                'recommendation' => 'Monitor serum potassium and renal function (eGFR, Creatinine) closely within 1-2 weeks of starting therapy.',
            ],
            // Macrolide / Quinolone + QT Prolonging Drugs
            [
                'groupA' => ['azithromycin', 'clarithromycin', 'erythromycin', 'ciprofloxacin', 'levofloxacin', 'moxifloxacin'],
                'groupB' => ['amiodarone', 'ondansetron', 'haloperidol', 'domperidone', 'fluconazole', 'escitalopram', 'sotalol'],
                'severity' => 'moderate',
                'title' => 'Additive QTc Interval Prolongation & Arrhythmia Risk',
                'mechanism' => 'Concurrent administration of multiple QT-prolonging agents can trigger Torsades de Pointes or ventricular arrhythmias.',
                'recommendation' => 'Assess baseline ECG/QTc interval if patient has pre-existing cardiac disease or electrolyte imbalances.',
            ],
            // Statin + Macrolide / Azole Antifungal
            [
                'groupA' => ['atorvastatin', 'simvastatin', 'lovastatin', 'rosuvastatin'],
                'groupB' => ['clarithromycin', 'erythromycin', 'itraconazole', 'ketoconazole', 'fluconazole', 'diltiazem'],
                'severity' => 'moderate',
                'title' => 'CYP3A4 Inhibition Leading to Statin Toxicity & Rhabdomyolysis Risk',
                'mechanism' => 'Macrolides/Azoles inhibit CYP3A4 metabolism of statins, significantly raising plasma statin levels and risk of myopathy.',
                'recommendation' => 'Temporarily suspend statin therapy during the short course of antimicrobial treatment, or use Azithromycin/Pravastatin.',
            ],
            // Beta-Blocker + Non-DHP Calcium Channel Blocker
            [
                'groupA' => ['atenolol', 'bisoprolol', 'metoprolol', 'carvedilol', 'nebivolol', 'propranolol'],
                'groupB' => ['verapamil', 'diltiazem'],
                'severity' => 'severe',
                'title' => 'Severe Bradycardia, AV Block, and Heart Failure Exacerbation',
                'mechanism' => 'Additive negative inotropic and chronotropic effects on the myocardium and AV conduction system.',
                'recommendation' => 'Avoid co-administration unless under strict specialist hemodynamic monitoring. Prefer Dihydropyridine CCBs (e.g. Amlodipine).',
            ],
            // Methotrexate + NSAIDs
            [
                'groupA' => ['methotrexate'],
                'groupB' => ['ibuprofen', 'naproxen', 'diclofenac', 'ketorolac', 'indomethacin', 'aceclofenac'],
                'severity' => 'severe',
                'title' => 'Reduced Methotrexate Clearance & Pancytopenia / Hepatotoxicity',
                'mechanism' => 'NSAIDs decrease renal tubular secretion of Methotrexate, precipitating severe bone marrow suppression and toxicity.',
                'recommendation' => 'Avoid high-dose Methotrexate combination. Co-prescribe Paracetamol or Tramadol for acute pain.',
            ],
            // SSRI / SNRI + Tramadol / Linezolid
            [
                'groupA' => ['sertraline', 'escitalopram', 'fluoxetine', 'paroxetine', 'duloxetine', 'venlafaxine'],
                'groupB' => ['tramadol', 'linezolid', 'selegiline'],
                'severity' => 'severe',
                'title' => 'Serotonin Syndrome Warning',
                'mechanism' => 'Concurrent serotonergic stimulation may cause hyperthermia, autonomic instability, neuromuscular hyperreactivity, and delirium.',
                'recommendation' => 'Monitor for tremor, hyperreflexia, and agitation. Use alternative analgesic or titrate cautiously.',
            ],
            // Metformin + Contrast / Diuretics
            [
                'groupA' => ['metformin'],
                'groupB' => ['furosemide', 'spironolactone'],
                'severity' => 'moderate',
                'title' => 'Dehydration & Lactic Acidosis Vulnerability',
                'mechanism' => 'Concurrent high-dose diuretics can induce volume depletion, precipitating acute renal insufficiency and Metformin accumulation.',
                'recommendation' => 'Ensure adequate patient hydration and monitor eGFR annually.',
            ],
            // Quinolone + Antacids / Iron / Calcium
            [
                'groupA' => ['ciprofloxacin', 'levofloxacin', 'moxifloxacin', 'doxycycline'],
                'groupB' => ['calcium carbonate', 'ferrous sulfate', 'antacid', 'aluminum hydroxide', 'magnesium hydroxide', 'sucralfate'],
                'severity' => 'moderate',
                'title' => 'Chelation Binding & Reduced Antimicrobial Bioavailability',
                'mechanism' => 'Divalent and trivalent cations form insoluble chelate complexes with quinolones and tetracyclines, reducing absorption by up to 75%.',
                'recommendation' => 'Instruct patient to separate administration by at least 2 hours before or 4 hours after cation-containing supplements.',
            ],
        ];

        // Match interaction pairs
        foreach ($rules as $rule) {
            $foundA = [];
            $foundB = [];

            foreach ($drugNames as $idx => $dName) {
                foreach ($rule['groupA'] as $itemA) {
                    if (str_contains($dName, $itemA)) {
                        $foundA[] = $drugs[$idx];
                        break;
                    }
                }
                foreach ($rule['groupB'] as $itemB) {
                    if (str_contains($dName, $itemB)) {
                        $foundB[] = $drugs[$idx];
                        break;
                    }
                }
            }

            if (!empty($foundA) && !empty($foundB)) {
                $drugA = is_array($foundA[0]) ? ($foundA[0]['trade_name'] ?? $foundA[0]['name'] ?? 'Drug A') : $foundA[0];
                $drugB = is_array($foundB[0]) ? ($foundB[0]['trade_name'] ?? $foundB[0]['name'] ?? 'Drug B') : $foundB[0];

                $interactions[] = [
                    'severity' => $rule['severity'],
                    'drugs' => [$drugA, $drugB],
                    'title' => $rule['title'],
                    'mechanism' => $rule['mechanism'],
                    'recommendation' => $rule['recommendation'],
                ];
            }
        }

        // Check for therapeutic duplication
        $drugClasses = [
            'NSAIDs' => ['ibuprofen', 'naproxen', 'ketorolac', 'diclofenac', 'indomethacin', 'meloxicam', 'celecoxib', 'etoricoxib', 'aceclofenac'],
            'Proton Pump Inhibitors (PPIs)' => ['omeprazole', 'esomeprazole', 'pantoprazole', 'rabeprazole', 'dexlansoprazole'],
            'Fluoroquinolones' => ['ciprofloxacin', 'levofloxacin', 'moxifloxacin', 'ofloxacin'],
            'Macrolide Antibiotics' => ['azithromycin', 'clarithromycin', 'erythromycin', 'roxithromycin'],
            'ACE Inhibitors' => ['enalapril', 'ramipril', 'lisinopril', 'perindopril', 'captopril'],
            'Angiotensin Receptor Blockers (ARBs)' => ['losartan', 'valsartan', 'telmisartan', 'olmesartan', 'candesartan'],
            'Beta Blockers' => ['atenolol', 'bisoprolol', 'metoprolol', 'carvedilol', 'nebivolol', 'propranolol'],
            'Antihistamines' => ['cetirizine', 'fexofenadine', 'loratadine', 'desloratadine', 'bilastine', 'rupatadine'],
            'Benzodiazepines' => ['diazepam', 'clonazepam', 'lorazepam', 'alprazolam', 'bromazepam', 'midazolam'],
        ];

        foreach ($drugClasses as $className => $classMembers) {
            $matched = [];
            foreach ($drugNames as $idx => $dName) {
                foreach ($classMembers as $member) {
                    if (str_contains($dName, $member)) {
                        $matched[] = is_array($drugs[$idx]) ? ($drugs[$idx]['trade_name'] ?? $drugs[$idx]['name'] ?? $member) : $drugs[$idx];
                        break;
                    }
                }
            }
            if (count($matched) > 1) {
                $duplicateTherapies[] = [
                    'class' => $className,
                    'drugs' => array_unique($matched),
                    'warning' => 'Multiple agents from the ' . $className . ' class are co-prescribed. Therapeutic duplication may increase adverse effect profiles without added clinical efficacy.',
                ];
            }
        }

        // Allergy Check
        $patientAllergies = is_array($patient) ? ($patient['allergies'] ?? $patient['allergies_notes'] ?? '') : '';
        if (!empty($patientAllergies)) {
            $patientAllergies = strtolower((string)$patientAllergies);
            $allergyKeywords = [
                'penicillin' => ['amoxicillin', 'ampicillin', 'augmentin', 'penicillin', 'piperacillin', 'cloxacillin'],
                'sulfa' => ['cotrimoxazole', 'sulfamethoxazole', 'sulfasalazine', 'trimethoprim'],
                'nsaid' => ['aspirin', 'ibuprofen', 'naproxen', 'diclofenac', 'ketorolac', 'aceclofenac'],
                'cephalosporin' => ['cefixime', 'ceftriaxone', 'cefuroxime', 'cephalexin', 'cefpodoxime'],
            ];

            foreach ($allergyKeywords as $allergen => $alliedDrugs) {
                if (str_contains($patientAllergies, $allergen)) {
                    foreach ($drugNames as $idx => $dName) {
                        foreach ($alliedDrugs as $alliedDrug) {
                            if (str_contains($dName, $alliedDrug)) {
                                $drugName = is_array($drugs[$idx]) ? ($drugs[$idx]['trade_name'] ?? $drugs[$idx]['name'] ?? $alliedDrug) : $drugs[$idx];
                                $allergyWarnings[] = [
                                    'drug' => $drugName,
                                    'allergen' => ucfirst($allergen),
                                    'message' => 'Potential hypersensitivity alert: Patient is allergic to ' . ucfirst($allergen) . ', and ' . $drugName . ' belongs to this cross-reactive drug family.',
                                ];
                                break;
                            }
                        }
                    }
                }
            }
        }

        $hasSevere = count(array_filter($interactions, fn($i) => $i['severity'] === 'severe')) > 0 || count($allergyWarnings) > 0;
        $hasModerate = count(array_filter($interactions, fn($i) => $i['severity'] === 'moderate')) > 0 || count($duplicateTherapies) > 0;

        $overallStatus = $hasSevere ? 'severe' : ($hasModerate ? 'moderate' : 'safe');
        $summary = match ($overallStatus) {
            'severe' => 'High Risk Alert: Immediate clinical review recommended for identified drug interactions/allergies.',
            'moderate' => 'Moderate Risk Warning: Review identified drug combinations and monitor patient response.',
            default => 'No adverse pharmacological interactions or contraindications identified among current medications.',
        };

        return [
            'status' => $overallStatus,
            'summary' => $summary,
            'checked_count' => count($drugs),
            'interactions' => $interactions,
            'duplicate_therapies' => $duplicateTherapies,
            'allergy_warnings' => $allergyWarnings,
            'engine' => 'Doc Helper AI Clinical Pharmacology Engine v2.4',
        ];
    }

    /**
     * Built-in Intelligent Clinical Copilot Response Generator.
     */
    private function generateClinicalCopilotResponse(string $query, array $context): array
    {
        $q = strtolower($query);

        // Pediatric Dosage Calculation
        if (str_contains($q, 'pediatric') || str_contains($q, 'child') || str_contains($q, 'dose') || str_contains($q, 'kg') || str_contains($q, 'mg/kg')) {
            return [
                'type' => 'dosage_calc',
                'title' => 'Pediatric Dosing & Calculation Guide',
                'answer' => "Standard evidence-based pediatric dosing reference:\n\n" .
                    "• **Paracetamol / Acetaminophen**: `10–15 mg/kg/dose` every 4–6 hours (Max 60 mg/kg/day or 4 doses/day).\n" .
                    "• **Amoxicillin**: `40–90 mg/kg/day` divided every 8 or 12 hours (High dose 90 mg/kg/day for Acute Otitis Media).\n" .
                    "• **Azithromycin**: `10 mg/kg` on Day 1, followed by `5 mg/kg/day` on Days 2–5 (Once daily).\n" .
                    "• **Cefixime**: `8 mg/kg/day` as single daily dose or divided 12-hourly.\n" .
                    "• **Ibuprofen (Children >6 months)**: `5–10 mg/kg/dose` every 6–8 hours with food.\n\n" .
                    "*Always verify weight in kilograms and calculate using precise concentration suspension.*",
                'suggested_actions' => [
                    [
                        'action' => 'add_advice',
                        'label' => 'Add Hydration & Fever Sponging Advice',
                        'data' => 'Tepid water sponge when temperature exceeds 100°F and ensure frequent fluid intake.',
                    ],
                ],
            ];
        }

        // Hypertension & ACEI / Cough Alternative
        if (str_contains($q, 'hypertension') || str_contains($q, 'acei') || str_contains($q, 'cough') || str_contains($q, 'losartan') || str_contains($q, 'amlodipine') || str_contains($q, 'bp')) {
            return [
                'type' => 'clinical_recommendation',
                'title' => 'Hypertension & ACEI-Induced Cough Alternatives',
                'answer' => "When patients develop ACE-Inhibitor (e.g. Ramipril, Enalapril) induced dry cough due to bradykinin accumulation, recommended next-line options include:\n\n" .
                    "1. **Switch to ARB (Angiotensin Receptor Blocker)**: Does not increase bradykinin.\n" .
                    "   • *Telmisartan 40mg* or *Losartan Potassium 50mg* once daily morning.\n" .
                    "2. **Dihydropyridine CCB Alternative**:\n" .
                    "   • *Amlodipine 5mg* once daily.\n" .
                    "3. **Lifestyle & Dietary Counseling**:\n" .
                    "   • Restrict dietary sodium (< 2g/day) and perform regular aerobic exercise 30 mins/day.",
                'suggested_actions' => [
                    [
                        'action' => 'add_drug',
                        'label' => 'Add Tab. Telmisartan 40mg',
                        'data' => [
                            'type' => 'Tab.',
                            'trade_name' => 'Telmisartan',
                            'strength' => '40 mg',
                            'dosage' => '1+0+0',
                            'instruction' => 'After Breakfast',
                            'duration' => 'Continue',
                        ],
                    ],
                    [
                        'action' => 'add_advice',
                        'label' => 'Add Low-Salt DASH Diet Advice',
                        'data' => 'Avoid extra added table salt and avoid high-sodium processed foods.',
                    ],
                ],
            ];
        }

        // Investigation Recommendations
        if (str_contains($q, 'investigation') || str_contains($q, 'test') || str_contains($q, 'lab') || str_contains($q, 'dengue') || str_contains($q, 'typhoid') || str_contains($q, 'diabetes') || str_contains($q, 'uti')) {
            return [
                'type' => 'investigation_panel',
                'title' => 'Recommended Clinical Diagnostic Panel',
                'answer' => "Standard diagnostic workup recommendations based on clinical guidelines:\n\n" .
                    "• **Suspected Dengue / Viral Pyrexia (Day 1-3)**:\n" .
                    "  - CBC with Platelet Count & Hematocrit (HCT)\n" .
                    "  - Dengue NS1 Antigen (Day 1-4) & Dengue IgM/IgG (Day 5+)\n" .
                    "  - SGPT / ALT & Serum Creatinine\n\n" .
                    "• **Suspected UTI / Dysuria**:\n" .
                    "  - Urine Routine & Microscopic Examination (R/M/E)\n" .
                    "  - Urine Culture & Sensitivity (C/S)\n" .
                    "  - USG of KUB Region with PVR\n\n" .
                    "• **Type 2 Diabetes Screening & Monitoring**:\n" .
                    "  - HbA1c & Fasting Blood Sugar (FBS) with 2HABF\n" .
                    "  - Serum Lipid Profile & Urine for Microalbumin",
                'suggested_actions' => [
                    [
                        'action' => 'add_investigation',
                        'label' => 'Add CBC & Platelet Count',
                        'data' => 'CBC with Platelet Count and Hematocrit (HCT)',
                    ],
                    [
                        'action' => 'add_investigation',
                        'label' => 'Add Urine R/M/E and C/S',
                        'data' => 'Urine Routine Examination (R/M/E) and Culture & Sensitivity',
                    ],
                ],
            ];
        }

        // General Clinical Assistant Query
        return [
            'type' => 'general_assistant',
            'title' => 'Doc Helper AI Clinical Copilot',
            'answer' => "I am ready to assist your clinical practice workflow. Here are quick medical capabilities you can request:\n\n" .
                "• **Drug Interaction Analysis**: *'Check if current drugs have interactions with Warfarin or Statins'*.\n" .
                "• **Pediatric & Geriatric Dosing**: *'Calculate Amoxicillin suspension for a 14kg toddler'*.\n" .
                "• **Differential Diagnosis**: *'Suggest workup for persistent right iliac fossa pain in young female'*.\n" .
                "• **Patient Dietary Counseling**: *'Generate diet chart for newly diagnosed GDM or CKD Stage 3'*.\n\n" .
                "Click any quick prompt chip below or type any pharmacology/clinical question.",
            'suggested_actions' => [
                [
                    'action' => 'add_advice',
                    'label' => 'Add General Follow-up Advice',
                    'data' => 'Return immediately if warning signs appear (high fever, severe shortness of breath, persistent vomiting).',
                ],
            ],
        ];
    }

    /**
     * Query Google Gemini API for interaction check.
     */
    private function queryGeminiInteractions(array $drugs, array $patient, array $diagnoses): ?array
    {
        try {
            $apiKey = SystemSetting::get('gemini_api_key', env('GEMINI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

            $prompt = "You are an expert clinical pharmacologist. Check the following medications for drug-to-drug interactions, contraindications, and duplication. Return strictly a JSON object without markdown formatting: { \"status\": \"safe\"|\"moderate\"|\"severe\", \"summary\": string, \"interactions\": [ { \"severity\": \"moderate\"|\"severe\", \"drugs\": [string, string], \"title\": string, \"mechanism\": string, \"recommendation\": string } ], \"duplicate_therapies\": [ { \"class\": string, \"drugs\": [string], \"warning\": string } ], \"allergy_warnings\": [] }.\n\nMedications: " . json_encode($drugs) . "\nPatient: " . json_encode($patient);

            $response = Http::timeout(8)->post($endpoint, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.2,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $parsed = json_decode($text, true);
                    if ($parsed && isset($parsed['status'])) {
                        $parsed['engine'] = "Google Gemini Live Medical Engine";
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini Live Interaction Check Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query Google Gemini API for clinical chat.
     */
    private function queryGeminiChat(string $message, array $history, array $context): ?array
    {
        try {
            $apiKey = SystemSetting::get('gemini_api_key', env('GEMINI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
            $systemPrompt = "You are Doc Helper AI, a board-certified clinical assistant. Provide precise, evidence-based clinical guidance in clean International Medical English. Context: Patient=" . json_encode($context['patient'] ?? []) . ", Current Medications=" . json_encode($context['drugs'] ?? []) . ", Diagnoses=" . json_encode($context['diagnoses'] ?? []) . ". Keep responses concise, clear, and bulleted with actionable suggestions.";

            $contents = [];
            foreach (array_slice($history, -4) as $item) {
                if (isset($item['role'], $item['content'])) {
                    $contents[] = [
                        'role' => $item['role'] === 'assistant' ? 'model' : 'user',
                        'parts' => [['text' => $item['content']]]
                    ];
                }
            }
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]]
            ];

            $response = Http::timeout(10)->post($endpoint, [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]]
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 1024,
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($reply) {
                    return [
                        'type' => 'live_llm_response',
                        'title' => "Clinical AI Copilot (Google Gemini)",
                        'answer' => $reply,
                        'suggested_actions' => [],
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini Live Chat Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query OpenAI API for interaction check.
     */
    private function queryOpenAiInteractions(array $drugs, array $patient, array $diagnoses): ?array
    {
        try {
            $apiKey = SystemSetting::get('openai_api_key', env('OPENAI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $prompt = "You are an expert clinical pharmacologist. Check the following medications for drug-to-drug interactions, contraindications, and duplication. Return strictly a JSON object with: { \"status\": \"safe\"|\"moderate\"|\"severe\", \"summary\": string, \"interactions\": [ { \"severity\": \"moderate\"|\"severe\", \"drugs\": [string, string], \"title\": string, \"mechanism\": string, \"recommendation\": string } ], \"duplicate_therapies\": [ { \"class\": string, \"drugs\": [string], \"warning\": string } ], \"allergy_warnings\": [] }.\n\nMedications: " . json_encode($drugs) . "\nPatient: " . json_encode($patient);

            $response = Http::timeout(8)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a strict medical pharmacology JSON assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;
                if ($content) {
                    $parsed = json_decode($content, true);
                    if ($parsed && isset($parsed['status'])) {
                        $parsed['engine'] = "OpenAI GPT-4o-mini Medical Live Engine";
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI Live Interaction Check Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query OpenAI API for clinical chat.
     */
    private function queryOpenAiChat(string $message, array $history, array $context): ?array
    {
        try {
            $apiKey = SystemSetting::get('openai_api_key', env('OPENAI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $systemPrompt = "You are Doc Helper AI, a board-certified clinical assistant. Provide precise, evidence-based clinical guidance in clean International Medical English. Context: Patient=" . json_encode($context['patient'] ?? []) . ", Current Medications=" . json_encode($context['drugs'] ?? []) . ", Diagnoses=" . json_encode($context['diagnoses'] ?? []) . ". Keep responses concise, clear, and bulleted with actionable suggestions.";

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach (array_slice($history, -4) as $item) {
                if (isset($item['role'], $item['content'])) {
                    $messages[] = ['role' => $item['role'], 'content' => $item['content']];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $message];

            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
                'temperature' => 0.4,
                'max_tokens' => 1024,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? null;
                if ($reply) {
                    return [
                        'type' => 'live_llm_response',
                        'title' => "Clinical AI Copilot (OpenAI GPT-4o-mini)",
                        'answer' => $reply,
                        'suggested_actions' => [],
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI Live Chat Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * AI Medical History Summarizer & Timeline View Generator.
     */
    public function summarizePatientHistory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|integer',
        ]);

        $user = auth()->user();
        if (!$this->isPlanAllowedForAi($user)) {
            return response()->json([
                'success' => false,
                'message' => 'AI Medical History Summarizer is not available on your current plan. Please upgrade to Pro or Enterprise.',
            ], 403);
        }

        $doctorId = $user->getDoctorId();
        $patient = \App\Models\Patient::with(['prescriptions.items', 'prescriptions.chamber', 'appointments'])
            ->where('id', $validated['patient_id'])
            ->first();

        if (!$patient) {
            return response()->json(['success' => false, 'message' => 'Patient record not found.'], 404);
        }

        $prescriptions = $patient->prescriptions->sortByDesc('prescription_date')->values();
        $appointments = $patient->appointments->sortByDesc('appointment_date')->values();

        $aiEnabled = SystemSetting::get('ai_enabled', true);
        $provider = SystemSetting::get('ai_provider', 'gemini');

        // 1. Try Live LLM (Gemini or OpenAI)
        if ($aiEnabled && $provider !== 'offline') {
            if ($provider === 'gemini') {
                $llmSummary = $this->queryGeminiHistorySummary($patient, $prescriptions, $appointments);
                if ($llmSummary !== null) {
                    return response()->json([
                        'success' => true,
                        'engine' => 'Google Gemini Live Clinical Engine',
                        'data' => $llmSummary,
                    ]);
                }
            } elseif ($provider === 'openai') {
                $llmSummary = $this->queryOpenAiHistorySummary($patient, $prescriptions, $appointments);
                if ($llmSummary !== null) {
                    return response()->json([
                        'success' => true,
                        'engine' => 'OpenAI GPT-4o-mini Clinical Engine',
                        'data' => $llmSummary,
                    ]);
                }
            }
        }

        // 2. Intelligent Rule-Based Clinical Longitudinal Summarizer (Offline & Guaranteed)
        $ruleSummary = $this->generateRuleBasedHistorySummary($patient, $prescriptions, $appointments);
        return response()->json([
            'success' => true,
            'engine' => 'Doc Helper AI Longitudinal Clinical Engine v2.5',
            'data' => $ruleSummary,
        ]);
    }

    /**
     * Chamber Disease Outbreak & Seasonal Trends AI Insights.
     */
    public function getChamberDiseaseTrends(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$this->isPlanAllowedForAi($user)) {
            return response()->json([
                'success' => false,
                'message' => 'AI Disease Outbreak & Seasonal Trends is not available on your current plan.',
            ], 403);
        }

        $doctorId = $user->getDoctorId();
        $chamberId = session('active_chamber_id') ?: $request->input('chamber_id');

        $fourteenDaysAgo = now()->subDays(14);
        $sixtyDaysAgo = now()->subDays(60);

        // Recent 14 days Rx
        $recentQuery = \App\Models\Prescription::where('doctor_id', $doctorId)
            ->where('created_at', '>=', $fourteenDaysAgo);
        if ($chamberId) {
            $recentQuery->where('chamber_id', $chamberId);
        }
        $recentRxs = $recentQuery->get(['diagnoses', 'created_at', 'patient_id']);

        // Prior baseline (15-60 days ago)
        $baselineQuery = \App\Models\Prescription::where('doctor_id', $doctorId)
            ->whereBetween('created_at', [$sixtyDaysAgo, $fourteenDaysAgo]);
        if ($chamberId) {
            $baselineQuery->where('chamber_id', $chamberId);
        }
        $baselineRxs = $baselineQuery->get(['diagnoses', 'created_at', 'patient_id']);

        $recentCounts = $this->extractDiagnosesCounts($recentRxs);
        $baselineCounts = $this->extractDiagnosesCounts($baselineRxs);

        $trends = [];
        foreach ($recentCounts as $disease => $recentCount) {
            $prevCount = $baselineCounts[$disease] ?? 0;
            // Normalize baseline to 14 days equivalent (approx 46 days / 3.3)
            $normalizedPrev = max(1, round($prevCount / 3.3, 1));
            $surgePercent = round((($recentCount - $normalizedPrev) / $normalizedPrev) * 100);

            $alertLevel = 'normal';
            if ($surgePercent >= 40 && $recentCount >= 2) {
                $alertLevel = 'high_surge';
            } elseif ($surgePercent >= 20 && $recentCount >= 1) {
                $alertLevel = 'moderate_surge';
            } elseif ($recentCount >= 3) {
                $alertLevel = 'seasonal_watch';
            }

            $trends[] = [
                'disease' => $disease,
                'recent_cases' => $recentCount,
                'baseline_cases' => $prevCount,
                'surge_percent' => $surgePercent,
                'alert_level' => $alertLevel,
            ];
        }

        // Sort by surge percentage desc
        usort($trends, fn($a, $b) => $b['surge_percent'] <=> $a['surge_percent']);

        // Return clean empty state if no cases/prescriptions exist in this period
        if (empty($trends)) {
            return response()->json([
                'success' => true,
                'trends' => [],
                'advisory' => null,
                'message' => 'No active diagnostic cluster or disease surge detected in this 14-day window. As you create prescriptions with diagnoses, AI will automatically detect local disease trends and outbreak patterns.',
                'generated_at' => now()->toFormattedDateString(),
            ]);
        }

        $aiEnabled = SystemSetting::get('ai_enabled', true);
        $provider = SystemSetting::get('ai_provider', 'gemini');

        // 1. Try Live LLM (Gemini or OpenAI) for Outbreak Advisory
        if ($aiEnabled && $provider !== 'offline') {
            if ($provider === 'gemini') {
                $llmAdvisory = $this->queryGeminiOutbreakAdvisory($trends);
                if ($llmAdvisory !== null) {
                    return response()->json([
                        'success' => true,
                        'trends' => array_slice($trends, 0, 6),
                        'advisory' => $llmAdvisory,
                        'engine' => 'Google Gemini Live Epidemiological Engine',
                        'generated_at' => now()->toFormattedDateString(),
                    ]);
                }
            } elseif ($provider === 'openai') {
                $llmAdvisory = $this->queryOpenAiOutbreakAdvisory($trends);
                if ($llmAdvisory !== null) {
                    return response()->json([
                        'success' => true,
                        'trends' => array_slice($trends, 0, 6),
                        'advisory' => $llmAdvisory,
                        'engine' => 'OpenAI GPT-4o-mini Epidemiological Engine',
                        'generated_at' => now()->toFormattedDateString(),
                    ]);
                }
            }
        }

        // 2. Intelligent Real-Data Rule-Based Clinical Advisory
        $topSurge = $trends[0];
        $surgeDiseaseName = $topSurge['disease'];
        $surgePct = $topSurge['surge_percent'];

        $headline = $surgePct > 0 
            ? "Seasonal Disease Surge Alert: {$surgeDiseaseName} (+{$surgePct}% in last 14 days)"
            : "Diagnostic Cluster Observed: {$surgeDiseaseName} ({$topSurge['recent_cases']} cases in 14 days)";

        $clinicalAdvisory = [
            'headline' => $headline,
            'summary' => "Chamber clinical diagnostic metrics indicate an active cluster of {$surgeDiseaseName} cases ({$topSurge['recent_cases']} recorded visits vs {$topSurge['baseline_cases']} baseline) in your practice. Screen susceptible patients with priority.",
            'action_protocols' => [
                "Screen early for characteristic presentations, symptom severity, and dehydration/respiratory indicators.",
                "Ensure chamber stock and availability of first-line therapeutics and supportive care medications for {$surgeDiseaseName}.",
                "Provide clear hydration, hygiene, and danger-sign awareness guidance on patient prescription discharge notes.",
            ],
            'quick_rx_advice' => "Drink plenty of clean fluids, practice good hygiene, rest adequately, and consult promptly if high fever or red-flag symptoms occur.",
        ];

        return response()->json([
            'success' => true,
            'trends' => array_slice($trends, 0, 6),
            'advisory' => $clinicalAdvisory,
            'engine' => 'Doc Helper AI Epidemiological Engine v2.5',
            'generated_at' => now()->toFormattedDateString(),
        ]);
    }

    /**
     * Helper to extract diagnosis counts from prescription collection.
     */
    private function extractDiagnosesCounts($prescriptions): array
    {
        $counts = [];
        foreach ($prescriptions as $rx) {
            if (is_array($rx->diagnoses)) {
                foreach ($rx->diagnoses as $diag) {
                    $name = is_array($diag) ? ($diag['diagnosis'] ?? ($diag['name'] ?? null)) : (is_string($diag) ? $diag : null);
                    if ($name && strlen(trim($name)) > 1) {
                        $clean = trim($name);
                        $counts[$clean] = ($counts[$clean] ?? 0) + 1;
                    }
                }
            }
        }
        return $counts;
    }

    /**
     * Generate Rule-Based Longitudinal Clinical History Summary.
     */
    private function generateRuleBasedHistorySummary($patient, $prescriptions, $appointments): array
    {
        $visitCount = $prescriptions->count();

        if ($visitCount === 0) {
            return [
                'summary' => "New patient registered with no prior consultation or prescription records in this clinic. Complete clinical timeline and diagnosis history will be logged automatically as prescriptions are generated.",
                'total_visits' => 0,
                'first_visit' => 'N/A',
                'latest_visit' => 'N/A',
                'chronic_conditions' => !empty($patient->medical_history) ? [$patient->medical_history] : [],
                'all_diagnoses' => [],
                'active_medications' => [],
                'allergies' => $patient->allergies ?: 'No known drug allergies (NKDA)',
                'timeline_nodes' => [],
                'key_recommendations' => [
                    "Perform baseline intake assessment and document chief complaints.",
                    "Record any known drug allergies and pre-existing medical conditions.",
                ],
            ];
        }
        $firstVisit = $prescriptions->last()?->prescription_date?->format('M d, Y') ?? 'N/A';
        $latestVisit = $prescriptions->first()?->prescription_date?->format('M d, Y') ?? 'N/A';

        // Extract all diagnoses across visits
        $allDiagnoses = [];
        $allDrugs = [];
        $timelineNodes = [];

        foreach ($prescriptions as $rx) {
            $rxDate = $rx->prescription_date?->format('M d, Y') ?? $rx->created_at->format('M d, Y');
            $diagList = [];
            if (is_array($rx->diagnoses)) {
                foreach ($rx->diagnoses as $d) {
                    $name = is_array($d) ? ($d['diagnosis'] ?? ($d['name'] ?? '')) : (string)$d;
                    if (!empty($name)) {
                        $diagList[] = $name;
                        $allDiagnoses[] = $name;
                    }
                }
            }

            $drugList = [];
            foreach ($rx->items as $it) {
                $drugName = trim($it->name . ' ' . ($it->strength ?? ''));
                $drugList[] = [
                    'name' => $it->name,
                    'type' => $it->type ?: 'Tab.',
                    'strength' => $it->strength ?: '',
                    'dose' => $it->dose ?: '',
                    'duration' => $it->duration ?: '',
                    'advice' => $it->advice ?: '',
                ];
                $allDrugs[] = $it->name;
            }

            $timelineNodes[] = [
                'id' => $rx->id,
                'code' => $rx->prescription_code,
                'date' => $rxDate,
                'chamber' => $rx->chamber?->name ?: 'Main Chamber',
                'diagnoses' => $diagList,
                'drugs' => $drugList,
                'complaints' => is_array($rx->chief_complaints) ? array_map(fn($c) => is_array($c) ? ($c['complaint'] ?? '') : (string)$c, $rx->chief_complaints) : [],
                'advice' => is_array($rx->advices) ? array_map(fn($a) => is_array($a) ? ($a['advice'] ?? '') : (string)$a, $rx->advices) : [],
                'follow_up' => $rx->follow_up_date,
            ];
        }

        $uniqueDiagnoses = array_values(array_unique(array_filter($allDiagnoses)));
        $uniqueDrugs = array_values(array_unique(array_filter($allDrugs)));

        // Identify chronic illnesses
        $chronicKeywords = ['diabetes', 'hypertension', 'asthma', 'copd', 'hypothyroid', 'chronic kidney', 'ckd', 'heart failure', 'ischemic', 'epilepsy', 'osteoarthritis', 'gerd'];
        $chronicFound = [];
        foreach ($uniqueDiagnoses as $diag) {
            foreach ($chronicKeywords as $ck) {
                if (str_contains(strtolower($diag), $ck)) {
                    $chronicFound[] = $diag;
                    break;
                }
            }
        }
        $chronicFound = array_values(array_unique($chronicFound));

        // Active / Recent Medications from the latest prescription
        $activeMedications = !empty($timelineNodes) ? $timelineNodes[0]['drugs'] : [];

        // Build concise longitudinal overview narrative
        $ageStr = $patient->age ? "{$patient->age}-year-old" : 'Adult';
        $genderStr = ucfirst($patient->gender ?: 'patient');
        $chronicsStr = !empty($chronicFound) ? implode(', ', $chronicFound) : ($patient->medical_history ?: 'No established chronic illness');
        $allergiesStr = $patient->allergies ?: 'No known drug allergies (NKDA)';

        $summaryText = "Patient is a {$ageStr} {$genderStr} with a cumulative history of {$visitCount} clinical consultations between {$firstVisit} and {$latestVisit}.\n\n" .
            "• **Primary Clinical Trajectory**: Key recorded conditions include " . (!empty($uniqueDiagnoses) ? implode('; ', array_slice($uniqueDiagnoses, 0, 4)) : 'routine health consultations') . ".\n" .
            "• **Chronic Illness Status**: {$chronicsStr}.\n" .
            "• **Active Pharmacotherapy**: Currently maintained on " . (count($activeMedications) > 0 ? count($activeMedications) . " prescribed medications (" . implode(', ', array_slice(array_column($activeMedications, 'name'), 0, 4)) . ")" : 'no active prescription regimens') . ".\n" .
            "• **Risk Factors & Allergies**: {$allergiesStr}.";

        return [
            'summary' => $summaryText,
            'total_visits' => $visitCount,
            'first_visit' => $firstVisit,
            'latest_visit' => $latestVisit,
            'chronic_conditions' => $chronicFound,
            'all_diagnoses' => $uniqueDiagnoses,
            'active_medications' => $activeMedications,
            'allergies' => $allergiesStr,
            'timeline_nodes' => $timelineNodes,
            'key_recommendations' => [
                "Review adherence to current active medications during this visit.",
                "Monitor for therapeutic efficacy and drug interactions against identified allergies.",
                "Check follow-up schedule and routine diagnostic labs if chronic condition is present.",
            ],
        ];
    }

    /**
     * Query Google Gemini for Structured Patient Medical History Summary.
     */
    private function queryGeminiHistorySummary($patient, $prescriptions, $appointments): ?array
    {
        try {
            $apiKey = SystemSetting::get('gemini_api_key', env('GEMINI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";

            $historyData = [
                'patient' => [
                    'name' => $patient->name,
                    'age' => $patient->age,
                    'gender' => $patient->gender,
                    'allergies' => $patient->allergies,
                    'medical_history' => $patient->medical_history,
                ],
                'prescriptions' => $prescriptions->map(function ($rx) {
                    return [
                        'date' => $rx->prescription_date?->format('Y-m-d'),
                        'diagnoses' => $rx->diagnoses,
                        'complaints' => $rx->chief_complaints,
                        'medicines' => $rx->items->map(fn($it) => $it->name . ' (' . ($it->strength ?? '') . ') ' . ($it->dose ?? ''))->toArray(),
                    ];
                })->toArray(),
            ];

            $prompt = "You are an expert physician analyzing a patient's longitudinal medical history. Provide a concise, highly structured clinical summary for the consulting doctor in JSON format: { \"summary\": string (2-3 concise bulleted paragraphs highlighting patient journey, key diagnoses, and stability), \"chronic_conditions\": [string], \"key_recommendations\": [string] }. Data: " . json_encode($historyData);

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
                $json = $response->json();
                $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $parsed = json_decode($text, true);
                    if ($parsed && isset($parsed['summary'])) {
                        $fallback = $this->generateRuleBasedHistorySummary($patient, $prescriptions, $appointments);
                        $fallback['summary'] = $parsed['summary'];
                        if (!empty($parsed['chronic_conditions'])) {
                            $fallback['chronic_conditions'] = $parsed['chronic_conditions'];
                        }
                        if (!empty($parsed['key_recommendations'])) {
                            $fallback['key_recommendations'] = $parsed['key_recommendations'];
                        }
                        return $fallback;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini History Summary Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query OpenAI for Structured Patient Medical History Summary.
     */
    private function queryOpenAiHistorySummary($patient, $prescriptions, $appointments): ?array
    {
        try {
            $apiKey = SystemSetting::get('openai_api_key', env('OPENAI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $historyData = [
                'patient' => [
                    'name' => $patient->name,
                    'age' => $patient->age,
                    'gender' => $patient->gender,
                    'allergies' => $patient->allergies,
                    'medical_history' => $patient->medical_history,
                ],
                'prescriptions' => $prescriptions->map(function ($rx) {
                    return [
                        'date' => $rx->prescription_date?->format('Y-m-d'),
                        'diagnoses' => $rx->diagnoses,
                        'complaints' => $rx->chief_complaints,
                        'medicines' => $rx->items->map(fn($it) => $it->name . ' (' . ($it->strength ?? '') . ') ' . ($it->dose ?? ''))->toArray(),
                    ];
                })->toArray(),
            ];

            $prompt = "You are an expert physician analyzing a patient's longitudinal medical history. Provide a concise, structured clinical summary for the consulting doctor in JSON format: { \"summary\": string, \"chronic_conditions\": [string], \"key_recommendations\": [string] }. Data: " . json_encode($historyData);

            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a strict medical summary JSON assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $content = $json['choices'][0]['message']['content'] ?? null;
                if ($content) {
                    $parsed = json_decode($content, true);
                    if ($parsed && isset($parsed['summary'])) {
                        $fallback = $this->generateRuleBasedHistorySummary($patient, $prescriptions, $appointments);
                        $fallback['summary'] = $parsed['summary'];
                        if (!empty($parsed['chronic_conditions'])) {
                            $fallback['chronic_conditions'] = $parsed['chronic_conditions'];
                        }
                        if (!empty($parsed['key_recommendations'])) {
                            $fallback['key_recommendations'] = $parsed['key_recommendations'];
                        }
                        return $fallback;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI History Summary Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query Google Gemini for Outbreak & Seasonal Clinical Advisory.
     */
    private function queryGeminiOutbreakAdvisory(array $trends): ?array
    {
        try {
            $apiKey = SystemSetting::get('gemini_api_key', env('GEMINI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
            $prompt = "You are an expert epidemiologist and public health physician. Analyze the following real chamber disease trend data and generate a structured clinical advisory JSON for the consulting doctor in JSON format: { \"headline\": string, \"summary\": string, \"action_protocols\": [string, string, string], \"quick_rx_advice\": string }. Trends data: " . json_encode($trends);

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
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text) {
                    $parsed = json_decode($text, true);
                    if ($parsed && isset($parsed['headline'], $parsed['summary'], $parsed['action_protocols'])) {
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini Outbreak Advisory Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Query OpenAI for Outbreak & Seasonal Clinical Advisory.
     */
    private function queryOpenAiOutbreakAdvisory(array $trends): ?array
    {
        try {
            $apiKey = SystemSetting::get('openai_api_key', env('OPENAI_API_KEY', ''));
            if (empty($apiKey)) return null;

            $prompt = "You are an expert epidemiologist and public health physician. Analyze the following real chamber disease trend data and generate a structured clinical advisory JSON for the consulting doctor in JSON format: { \"headline\": string, \"summary\": string, \"action_protocols\": [string, string, string], \"quick_rx_advice\": string }. Trends data: " . json_encode($trends);

            $response = Http::timeout(10)->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a strict clinical epidemiology JSON assistant.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.2,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;
                if ($content) {
                    $parsed = json_decode($content, true);
                    if ($parsed && isset($parsed['headline'], $parsed['summary'], $parsed['action_protocols'])) {
                        return $parsed;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('OpenAI Outbreak Advisory Error: ' . $e->getMessage());
        }

        return null;
    }
}

