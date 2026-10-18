<?php

namespace Database\Seeders;

use App\Models\Chamber;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = User::first();
        if (!$doctor) {
            return;
        }

        $chamber = Chamber::where('doctor_id', $doctor->id)->first();
        $patient = Patient::where('doctor_id', $doctor->id)->first();

        if (!$chamber || !$patient) {
            return;
        }

        // Check if sample prescription already exists
        if (Prescription::where('doctor_id', $doctor->id)->exists()) {
            return;
        }

        // Prescription 1: Acute Bronchitis + APD
        $rx1 = Prescription::create([
            'doctor_id' => $doctor->id,
            'chamber_id' => $chamber->id,
            'patient_id' => $patient->id,
            'prescription_code' => 'RX-' . date('Ymd') . '-0001',
            'prescription_date' => today(),
            'vitals' => [
                'bp' => '120/80',
                'pulse' => '78',
                'weight' => '68',
                'height' => "5'7\"",
                'temperature' => '99.2',
                'spo2' => '98',
                'bmi' => '23.5',
            ],
            'chief_complaints' => [
                ['complaint' => 'Fever with chills', 'duration' => '3 days', 'severity' => 'Moderate', 'notes' => 'Mostly in evening'],
                ['complaint' => 'Dry Cough', 'duration' => '5 days', 'severity' => 'Mild', 'notes' => 'Worse at night'],
                ['complaint' => 'Acidity / Heartburn', 'duration' => '1 week', 'severity' => 'Mild', 'notes' => 'After meals'],
            ],
            'on_examinations' => [
                ['examination' => 'Chest / Lungs', 'finding' => 'Bilateral vesicular breath sounds, mild rhonchi right base'],
                ['examination' => 'Throat', 'finding' => 'Congested pharynx, no tonsillar exudate'],
                ['examination' => 'Abdomen', 'finding' => 'Soft, mild epigastric tenderness'],
            ],
            'diagnoses' => [
                ['diagnosis' => 'Acute Bronchitis', 'icd10' => 'J20.9', 'type' => 'Final', 'notes' => 'Viral etiology likely'],
                ['diagnosis' => 'APD (Acid Peptic Disease)', 'icd10' => 'K31.8', 'type' => 'Provisional', 'notes' => 'Lifestyle related'],
            ],
            'investigations' => [
                ['test_name' => 'CBC with ESR', 'instruction' => 'Standard venous sample'],
                ['test_name' => 'Chest X-Ray P/A View', 'instruction' => 'Digital X-Ray'],
                ['test_name' => 'Serum Creatinine', 'instruction' => 'Routine checkup'],
            ],
            'clinical_advices' => [
                'Drink plenty of warm water and fluids.',
                'Avoid smoking, cold drinks, and dust exposure.',
                'Avoid oily and overly spicy foods.',
                'Take adequate physical rest and avoid heavy work.',
            ],
            'follow_up_date' => 'After 7 days',
            'follow_up_notes' => 'Review with investigation lab reports.',
            'doctor_notes' => 'Patient responded well to initial consultation. Advised lifestyle modification.',
            'status' => 'finalized',
        ]);

        // Medications for Rx 1
        $medications = [
            [
                'medicine_name' => 'Napa Extra 500mg+65mg',
                'generic_name' => 'Paracetamol + Caffeine',
                'dosage_form' => 'Tab',
                'strength' => '500mg+65mg',
                'dosage' => '1+0+1',
                'duration' => '5 Days',
                'instruction' => 'After meal',
                'extra_notes' => 'Take if fever or body ache persists',
                'item_order' => 1,
            ],
            [
                'medicine_name' => 'Maxpro 20mg',
                'generic_name' => 'Esomeprazole',
                'dosage_form' => 'Cap',
                'strength' => '20mg',
                'dosage' => '1+0+1',
                'duration' => '14 Days',
                'instruction' => '20 mins before meal',
                'extra_notes' => '',
                'item_order' => 2,
            ],
            [
                'medicine_name' => 'Azithrocin 500mg',
                'generic_name' => 'Azithromycin',
                'dosage_form' => 'Tab',
                'strength' => '500mg',
                'dosage' => '1+0+0',
                'duration' => '3 Days',
                'instruction' => '1 hour before meal',
                'extra_notes' => 'Take once daily at the same time',
                'item_order' => 3,
            ],
            [
                'medicine_name' => 'Tusca 100ml',
                'generic_name' => 'Dextromethorphan + Guaifenesin',
                'dosage_form' => 'Syp',
                'strength' => '100ml',
                'dosage' => '2 tsp 3 times daily',
                'duration' => '7 Days',
                'instruction' => 'After meal',
                'extra_notes' => '',
                'item_order' => 4,
            ],
        ];

        foreach ($medications as $med) {
            $med['prescription_id'] = $rx1->id;
            PrescriptionItem::create($med);
        }
    }
}

