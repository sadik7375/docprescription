<?php

namespace Database\Seeders;

use App\Models\Chamber;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChamberSeeder extends Seeder
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

        $chamber1 = Chamber::firstOrCreate(
            [
                'doctor_id' => $doctor->id,
                'name' => 'Popular Diagnostic Centre',
                'branch' => 'Downtown Medical Center',
            ],
            [
                'address' => '100 Medical Plaza, Suite 400, San Francisco, CA 94143',
                'phone' => '+1 (555) 234-5678',
                'consultation_fee' => 1000.00,
                'followup_fee' => 600.00,
                'visiting_days' => ['Saturday', 'Monday', 'Wednesday'],
                'visiting_time' => '05:00 PM - 09:00 PM',
                'room_no' => 'Room #304 (3rd Floor)',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        $chamber2 = Chamber::firstOrCreate(
            [
                'doctor_id' => $doctor->id,
                'name' => 'Ibn Sina Diagnostic & Consultation Centre',
                'branch' => 'Uptown Specialist Clinic',
            ],
            [
                'address' => '500 Health Blvd, Building B, New York, NY 10001',
                'phone' => '+1 (555) 876-5432',
                'consultation_fee' => 1200.00,
                'followup_fee' => 700.00,
                'visiting_days' => ['Sunday', 'Tuesday', 'Thursday'],
                'visiting_time' => '06:00 PM - 10:00 PM',
                'room_no' => 'Room #402 (4th Floor)',
                'is_default' => false,
                'is_active' => true,
            ]
        );

        // Attach existing patients across chambers to demonstrate multi-chamber history
        $patients = Patient::all();
        foreach ($patients as $idx => $patient) {
            // Patient 1 visits both chambers
            if ($idx === 0) {
                $patient->chambers()->syncWithoutDetaching([
                    $chamber1->id => ['visit_count' => 2, 'last_visited_at' => now()->subDays(3)],
                    $chamber2->id => ['visit_count' => 1, 'last_visited_at' => now()->subDays(10)],
                ]);
            } else {
                $patient->chambers()->syncWithoutDetaching([
                    $chamber1->id => ['visit_count' => 1, 'last_visited_at' => now()->subDays(1)],
                ]);
            }
        }
    }
}
