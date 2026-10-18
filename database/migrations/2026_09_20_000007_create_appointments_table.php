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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('chamber_id')->constrained('chambers')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->integer('serial_no')->default(1);
            $table->string('token_code', 20)->nullable();
            $table->date('appointment_date')->index();
            $table->string('appointment_time', 50)->nullable();
            $table->enum('type', ['new_consultation', 'follow_up', 'report_review', 'emergency'])->default('new_consultation');
            $table->enum('status', ['scheduled', 'in_queue', 'in_consultation', 'completed', 'canceled', 'no_show'])->default('in_queue');
            $table->decimal('fee_amount', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'paid', 'waived'])->default('unpaid');
            $table->json('vitals')->nullable(); // bp, weight, pulse, temperature, spo2
            $table->text('problem')->nullable(); // chief reason for visit
            $table->text('doctor_notes')->nullable();
            $table->timestamps();

            $table->index(['chamber_id', 'appointment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
