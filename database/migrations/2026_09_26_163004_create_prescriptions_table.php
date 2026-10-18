<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('chamber_id')->constrained('chambers')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->string('prescription_code', 30)->unique();
            $table->date('prescription_date')->index();
            $table->json('vitals')->nullable(); // bp, pulse, weight, height, temperature, bmi, spo2
            $table->json('chief_complaints')->nullable(); // [{ complaint, duration, severity, notes }]
            $table->json('on_examinations')->nullable(); // [{ examination, finding }]
            $table->json('diagnoses')->nullable(); // [{ diagnosis, icd10, type, notes }]
            $table->json('investigations')->nullable(); // [{ test_name, instruction }]
            $table->json('clinical_advices')->nullable(); // array of strings
            $table->string('follow_up_date', 100)->nullable(); // e.g. "After 7 days" or "2026-10-05"
            $table->text('follow_up_notes')->nullable();
            $table->text('doctor_notes')->nullable(); // private internal notes
            $table->enum('status', ['draft', 'finalized', 'archived'])->default('finalized');
            $table->timestamps();

            $table->index(['doctor_id', 'prescription_date']);
            $table->index(['chamber_id', 'prescription_date']);
            $table->index(['patient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
