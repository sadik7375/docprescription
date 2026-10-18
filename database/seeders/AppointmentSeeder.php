<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Chamber;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $doctor = User::first();
        $chamber = Chamber::first();
        $patients = Patient::take(3)->get();

        if (!$doctor || !$chamber || $patients->isEmpty()) {
            return;
        }

        $today = date('Y-m-d');

        $sampleAppointments = [
            [
                'patient_id' => $patients[0]->id,
                'appointment_time' => '05:00 PM',
                'type' => 'new_consultation',
                'status' => 'in_consultation',
                'fee_amount' => 1000.00,
                'vitals' => ['bp' => '130/85', 'weight' => '68', 'pulse' => '76', 'temperature' => '99.1'],
                'problem' => 'High fever for 3 days and dry cough',
            ],
            [
                'patient_id' => $patients[1]->id ?? $patients[0]->id,
                'appointment_time' => '05:30 PM',
                'type' => 'follow_up',
                'status' => 'in_queue',
                'fee_amount' => 600.00,
                'vitals' => ['bp' => '140/90', 'weight' => '72', 'pulse' => '80', 'temperature' => '98.4'],
                'problem' => 'Hypertension routine follow-up & vertigo',
            ],
        ];

        foreach ($sampleAppointments as $appData) {
            Appointment::create(array_merge($appData, [
                'doctor_id' => $doctor->id,
                'chamber_id' => $chamber->id,
                'appointment_date' => $today,
                'payment_status' => 'unpaid',
            ]));
        }
    }
}
