<?php

namespace Database\Seeders;

use App\Models\Drug;
use App\Models\DrugAdvice;
use App\Models\DrugDose;
use App\Models\DrugDuration;
use App\Models\DrugStrength;
use App\Models\DrugType;
use App\Models\DrugVariation;
use Illuminate\Database\Seeder;

class DrugLookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Drug Types
        $types = [
            ['name' => 'Tablet', 'short_name' => 'Tab.'],
            ['name' => 'Capsule', 'short_name' => 'Cap.'],
            ['name' => 'Syrup', 'short_name' => 'Syp.'],
            ['name' => 'Suspension', 'short_name' => 'Susp.'],
            ['name' => 'Injection', 'short_name' => 'Inj.'],
            ['name' => 'Eye/Ear Drop', 'short_name' => 'Drop'],
            ['name' => 'Ointment / Cream', 'short_name' => 'Oint.'],
            ['name' => 'Inhaler', 'short_name' => 'Inh.'],
            ['name' => 'Suppository', 'short_name' => 'Supp.'],
            ['name' => 'Oral Gel', 'short_name' => 'Gel'],
        ];

        foreach ($types as $t) {
            DrugType::firstOrCreate(['name' => $t['name']], $t);
        }

        // 2. Drug Doses
        $doses = [
            ['name' => '1+0+1', 'bangla_name' => 'Morning + Night'],
            ['name' => '1+1+1', 'bangla_name' => 'Morning + Noon + Night'],
            ['name' => '0+0+1', 'bangla_name' => 'Night (Once daily)'],
            ['name' => '1+0+0', 'bangla_name' => 'Morning (Once daily)'],
            ['name' => '0+1+0', 'bangla_name' => 'Noon (Once daily)'],
            ['name' => '1+1+1+1', 'bangla_name' => '4 times daily (every 6 hours)'],
            ['name' => '1/2+0+1/2', 'bangla_name' => 'Half tablet twice daily'],
            ['name' => '1 tsp 3 times daily', 'bangla_name' => '1 teaspoon 3 times daily'],
            ['name' => '2 tsp 3 times daily', 'bangla_name' => '2 teaspoons 3 times daily'],
            ['name' => '1 Puff 2 times daily', 'bangla_name' => '1 puff twice daily'],
        ];

        foreach ($doses as $d) {
            DrugDose::firstOrCreate(['name' => $d['name']], $d);
        }

        // 3. Drug Strengths
        $strengths = [
            '500mg', '650mg', '250mg', '125mg', '20mg', '40mg', '10mg', '5mg', '2.5mg', '1mg', '100ml', '200ml', '100IU', '500IU'
        ];

        foreach ($strengths as $s) {
            DrugStrength::firstOrCreate(['name' => $s]);
        }

        // 4. Drug Durations
        $durations = [
            '3 Days', '5 Days', '7 Days', '10 Days', '14 Days', '1 Month', '2 Months', '3 Months', 'Continue', 'As needed (SOS)'
        ];

        foreach ($durations as $dur) {
            DrugDuration::firstOrCreate(['name' => $dur]);
        }

        // 5. Drug Advices
        $advices = [
            ['name' => 'After meal', 'bangla_name' => 'After meal'],
            ['name' => '15 mins before meal', 'bangla_name' => '15 mins before meal'],
            ['name' => '30 mins before meal', 'bangla_name' => '30 mins before meal'],
            ['name' => 'Empty stomach', 'bangla_name' => 'Empty stomach'],
            ['name' => 'Before bedtime', 'bangla_name' => 'Before bedtime'],
            ['name' => 'Chewable after meal', 'bangla_name' => 'Chewable after meal'],
            ['name' => 'Drink plenty of water', 'bangla_name' => 'Drink plenty of water'],
        ];

        foreach ($advices as $a) {
            DrugAdvice::firstOrCreate(['name' => $a['name']], $a);
        }

        // 6. Sample Base Drugs with Variations
        $sampleDrugs = [
            [
                'trade_name' => 'Napa Extra',
                'generic_name' => 'Paracetamol 500mg + Caffeine 65mg',
                'note' => 'For fever and moderate to severe headache / body ache.',
                'warning' => 'Do not exceed 4g daily.',
                'side_effect' => 'Nausea, allergic skin rash in rare cases.',
                'additional_advice' => 'Drink plenty of oral fluids.',
                'variations' => [
                    ['type' => 'Tablet', 'strength' => '500mg+65mg', 'dose' => '1+0+1', 'duration' => '3 Days', 'advice' => 'After meal'],
                ]
            ],
            [
                'trade_name' => 'Seclo',
                'generic_name' => 'Omeprazole',
                'note' => 'Proton pump inhibitor for GERD and Peptic Ulcer Disease.',
                'warning' => 'Take before meals for best efficacy.',
                'side_effect' => 'Headache, abdominal pain, diarrhea.',
                'additional_advice' => 'Avoid spicy and oily food items.',
                'variations' => [
                    ['type' => 'Capsule', 'strength' => '20mg', 'dose' => '1+0+1', 'duration' => '14 Days', 'advice' => '15 mins before meal'],
                    ['type' => 'Capsule', 'strength' => '40mg', 'dose' => '1+0+1', 'duration' => '14 Days', 'advice' => '15 mins before meal'],
                    ['type' => 'Injection', 'strength' => '40mg IV', 'dose' => '1+0+1', 'duration' => '3 Days', 'advice' => 'Slow IV injection over 5 mins'],
                ]
            ],
            [
                'trade_name' => 'Monas',
                'generic_name' => 'Montelukast Sodium',
                'note' => 'Leukotriene receptor antagonist for asthma and allergic rhinitis.',
                'warning' => 'Not for acute asthma attacks.',
                'side_effect' => 'Mild dizziness, fatigue.',
                'additional_advice' => 'Take at night consistently.',
                'variations' => [
                    ['type' => 'Tablet', 'strength' => '10mg', 'dose' => '0+0+1', 'duration' => '1 Month', 'advice' => 'Before bedtime'],
                    ['type' => 'Tablet', 'strength' => '5mg', 'dose' => '0+0+1', 'duration' => '1 Month', 'advice' => 'Before bedtime (For children)'],
                ]
            ],
            [
                'trade_name' => 'Ceevit',
                'generic_name' => 'Ascorbic Acid (Vitamin C)',
                'note' => 'Nutritional supplement for immunity and tissue repair.',
                'warning' => 'High dose may cause gastrointestinal discomfort.',
                'side_effect' => 'Mild stomach upset.',
                'additional_advice' => 'Chew thoroughly before swallowing.',
                'variations' => [
                    ['type' => 'Tablet', 'strength' => '250mg', 'dose' => '1+0+1', 'duration' => '10 Days', 'advice' => 'Chewable after meal'],
                ]
            ],
            [
                'trade_name' => 'Azithrocin',
                'generic_name' => 'Azithromycin',
                'note' => 'Macrolide antibiotic for respiratory tract infections.',
                'warning' => 'Complete full 5-day course as prescribed.',
                'side_effect' => 'Nausea, abdominal cramps, loose stool.',
                'additional_advice' => 'Take 1 hour before or 2 hours after meals.',
                'variations' => [
                    ['type' => 'Tablet', 'strength' => '500mg', 'dose' => '1+0+0', 'duration' => '5 Days', 'advice' => '15 mins before meal'],
                    ['type' => 'Syrup', 'strength' => '200mg/5ml', 'dose' => '1 tsp 3 times daily', 'duration' => '5 Days', 'advice' => 'After meal'],
                ]
            ],
            [
                'trade_name' => 'Tuzid',
                'generic_name' => 'Dextromethorphan + Guaiphenesin',
                'note' => 'Expectorant and cough suppressant for productive cough.',
                'warning' => 'May cause slight drowsiness.',
                'side_effect' => 'Drowsiness, mild dizziness.',
                'additional_advice' => 'Drink warm water with honey.',
                'variations' => [
                    ['type' => 'Syrup', 'strength' => '100ml', 'dose' => '2 tsp 3 times daily', 'duration' => '7 Days', 'advice' => 'After meal'],
                ]
            ],
        ];

        foreach ($sampleDrugs as $drugData) {
            $variations = $drugData['variations'];
            unset($drugData['variations']);

            $drug = Drug::firstOrCreate(['trade_name' => $drugData['trade_name']], $drugData);

            // Re-sync variations
            $drug->variations()->delete();
            foreach ($variations as $var) {
                $drug->variations()->create($var);
            }
        }
    }
}
