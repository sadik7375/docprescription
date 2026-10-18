<?php

namespace Database\Seeders;

use App\Models\ChiefComplaint;
use App\Models\ClinicalAdvice;
use App\Models\Diagnosis;
use App\Models\Investigation;
use Illuminate\Database\Seeder;

class ClinicalLookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Chief Complaints
        $complaints = [
            ['name' => 'Fever', 'description' => 'High or low-grade elevated body temperature'],
            ['name' => 'High fever with chills & rigor', 'description' => 'Sudden onset high temperature with shivering'],
            ['name' => 'Dry cough', 'description' => 'Persistent non-productive cough'],
            ['name' => 'Productive cough', 'description' => 'Cough with sputum or phlegm'],
            ['name' => 'Shortness of breath (Dyspnea)', 'description' => 'Difficulty or discomfort in breathing'],
            ['name' => 'Chest pain', 'description' => 'Retrosternal or pleuritic chest discomfort'],
            ['name' => 'Severe headache', 'description' => 'Throbbing or generalized headache'],
            ['name' => 'Abdominal pain', 'description' => 'Epigastric, umbilical, or generalized pain'],
            ['name' => 'Loose motion / Diarrhea', 'description' => 'Frequent watery stools'],
            ['name' => 'Nausea and vomiting', 'description' => 'Feeling sick with recurrent vomiting'],
            ['name' => 'General weakness and fatigue', 'description' => 'Tiredness and lethargy'],
            ['name' => 'Burning sensation during urination', 'description' => 'Dysuria with urinary urgency'],
            ['name' => 'Sore throat and pain on swallowing', 'description' => 'Pharyngeal irritation and dysphagia'],
            ['name' => 'Joint pain & body ache', 'description' => 'Arthralgia and generalized myalgia'],
            ['name' => 'Dizziness and vertigo', 'description' => 'Lightheadedness and spinning sensation'],
            ['name' => 'Skin rash and itching', 'description' => 'Pruritus with maculopapular eruptions'],
            ['name' => 'Loss of appetite (Anorexia)', 'description' => 'Reduced desire to eat'],
            ['name' => 'Insomnia / Sleep disturbance', 'description' => 'Difficulty falling or staying asleep'],
        ];

        foreach ($complaints as $c) {
            ChiefComplaint::firstOrCreate(['name' => $c['name']], $c);
        }

        // 2. Diagnoses
        $diagnoses = [
            ['name' => 'Acute Upper Respiratory Tract Infection (URTI)', 'icd10_code' => 'J06.9', 'description' => 'Viral or bacterial infection of upper airway'],
            ['name' => 'Acute Bronchitis', 'icd10_code' => 'J20.9', 'description' => 'Inflammation of tracheobronchial tree'],
            ['name' => 'Type 2 Diabetes Mellitus', 'icd10_code' => 'E11.9', 'description' => 'Non-insulin dependent diabetes'],
            ['name' => 'Essential (Primary) Hypertension', 'icd10_code' => 'I10', 'description' => 'Chronically elevated systemic arterial pressure'],
            ['name' => 'Acute Peptic Ulcer Disease (APD / Gastritis)', 'icd10_code' => 'K27.9', 'description' => 'Mucosal erosion in stomach or duodenum'],
            ['name' => 'Acute Gastroenteritis (AGE)', 'icd10_code' => 'A09', 'description' => 'Inflammation of stomach and intestines'],
            ['name' => 'Urinary Tract Infection (UTI)', 'icd10_code' => 'N39.0', 'description' => 'Bacterial infection in the urinary system'],
            ['name' => 'Viral Fever / Influenza', 'icd10_code' => 'J11.1', 'description' => 'Seasonal flu and viral illness'],
            ['name' => 'Bronchial Asthma (Acute Exacerbation)', 'icd10_code' => 'J45.9', 'description' => 'Chronic airway hyper-reactivity and bronchospasm'],
            ['name' => 'Migraine without aura', 'icd10_code' => 'G43.0', 'description' => 'Neurovascular throbbing unilateral headache'],
            ['name' => 'Enteric / Typhoid Fever', 'icd10_code' => 'A01.0', 'description' => 'Salmonella typhi infection'],
            ['name' => 'Dengue Fever with/without Warning Signs', 'icd10_code' => 'A90', 'description' => 'Flavivirus mosquito-borne infection'],
            ['name' => 'Ischemic Heart Disease (IHD / CAD)', 'icd10_code' => 'I25.9', 'description' => 'Reduced blood supply to myocardium'],
            ['name' => 'Allergic Rhinitis', 'icd10_code' => 'J30.4', 'description' => 'IgE-mediated nasal hypersensitivity'],
            ['name' => 'Osteoarthritis (Knee / Generalized)', 'icd10_code' => 'M19.9', 'description' => 'Degenerative joint disease'],
        ];

        foreach ($diagnoses as $d) {
            Diagnosis::firstOrCreate(['name' => $d['name']], $d);
        }

        // 3. Investigations / Lab Tests
        $investigations = [
            ['name' => 'CBC with ESR (Complete Blood Count)', 'category' => 'Hematology', 'description' => 'Hemoglobin, TC, DC, Platelets, ESR'],
            ['name' => 'Random Blood Sugar (RBS)', 'category' => 'Biochemistry', 'description' => 'Random glucose measurement'],
            ['name' => 'Fasting Blood Sugar (FBS) & 2HABF', 'category' => 'Biochemistry', 'description' => 'Fasting and 2-hour post-breakfast glucose'],
            ['name' => 'HbA1c (Glycated Hemoglobin)', 'category' => 'Biochemistry', 'description' => '3-month average plasma glucose control'],
            ['name' => 'Serum Creatinine', 'category' => 'Biochemistry', 'description' => 'Kidney function marker'],
            ['name' => 'SGPT / ALT (Alanine Aminotransferase)', 'category' => 'Biochemistry', 'description' => 'Liver function evaluation'],
            ['name' => 'Lipid Profile (Cholesterol, TG, HDL, LDL)', 'category' => 'Biochemistry', 'description' => 'Cardiovascular risk assessment'],
            ['name' => 'Urine Routine & Microscopic Examination (R/M/E)', 'category' => 'Clinical Pathology', 'description' => 'Pus cells, RBC, Albumin, Sugar'],
            ['name' => 'Urine Culture & Sensitivity (C/S)', 'category' => 'Microbiology', 'description' => 'Bacterial organism and antibiotic susceptibility'],
            ['name' => 'Chest X-Ray (P/A View)', 'category' => 'Radiology', 'description' => 'Lungs, pleura, and cardiothoracic ratio'],
            ['name' => 'ECG 12 Leads', 'category' => 'Cardiology', 'description' => 'Cardiac electrical activity & rhythm'],
            ['name' => 'Ultrasonography of Whole Abdomen (USG)', 'category' => 'Radiology', 'description' => 'Liver, GB, Spleen, Kidneys, Bladder, Prostate/Uterus'],
            ['name' => 'Serum Electrolytes (Na+, K+, Cl-, HCO3-)', 'category' => 'Biochemistry', 'description' => 'Electrolyte and acid-base balance'],
            ['name' => 'Serum Uric Acid', 'category' => 'Biochemistry', 'description' => 'Gout and hyperuricemia screening'],
            ['name' => 'Dengue NS1 Antigen & IgM/IgG Antibody', 'category' => 'Serology', 'description' => 'Dengue virus diagnostic confirmation'],
            ['name' => 'Serum Bilirubin (Total & Direct)', 'category' => 'Biochemistry', 'description' => 'Jaundice and hepatic function'],
            ['name' => 'Thyroid Stimulating Hormone (TSH)', 'category' => 'Endocrinology', 'description' => 'Thyroid gland functional screening'],
        ];

        foreach ($investigations as $i) {
            Investigation::firstOrCreate(['name' => $i['name']], $i);
        }

        // 4. Clinical Advices
        $advices = [
            ['name' => 'Drink 2.5 to 3 liters of clean boiled water daily', 'category' => 'Diet', 'description' => 'Maintain optimal hydration'],
            ['name' => 'Maintain a low-salt and low-fat diet', 'category' => 'Diet', 'description' => 'Avoid excess table salt and fried food'],
            ['name' => 'Strictly follow diabetic diet guidelines (avoid sugar and sweets)', 'category' => 'Diet', 'description' => 'Carbohydrate regulation'],
            ['name' => 'Avoid cold food, iced drinks, dust, and smoke', 'category' => 'Lifestyle', 'description' => 'Prevent respiratory triggers'],
            ['name' => 'Complete physical rest for 3 to 5 days', 'category' => 'Lifestyle', 'description' => 'Facilitate physical recovery'],
            ['name' => 'Engage in moderate aerobic exercise / walking 30 mins daily', 'category' => 'Lifestyle', 'description' => 'Cardiovascular fitness'],
            ['name' => 'Avoid oily, spicy, and street fast foods', 'category' => 'Diet', 'description' => 'Prevent gastrointestinal irritation'],
            ['name' => 'Do steam inhalation 2-3 times daily for congestion', 'category' => 'Treatment', 'description' => 'Airway clearance'],
            ['name' => 'Follow-up in 7 days with investigation reports', 'category' => 'Followup', 'description' => 'Review clinical progress'],
            ['name' => 'Seek emergency care immediately if severe chest pain or breathing difficulty occurs', 'category' => 'Emergency', 'description' => 'Red flag warning'],
        ];

        foreach ($advices as $a) {
            ClinicalAdvice::firstOrCreate(['name' => $a['name']], $a);
        }
    }
}
