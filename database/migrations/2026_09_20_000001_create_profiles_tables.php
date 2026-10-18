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
        Schema::create('doctor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('Dr.');
            $table->string('specialty')->nullable();
            $table->string('bmdc_reg_no')->nullable();
            $table->json('degrees')->nullable();
            $table->json('experiences')->nullable();
            $table->string('signature')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_code')->unique();
            $table->string('gender')->default('male');
            $table->integer('age')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('address')->nullable();
            $table->json('medical_history')->nullable();
            $table->timestamps();
        });

        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('chamber_id')->nullable();
            $table->string('designation')->default('Receptionist');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('patient_profiles');
        Schema::dropIfExists('doctor_profiles');
    }
};
