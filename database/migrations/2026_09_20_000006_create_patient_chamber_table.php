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
        Schema::create('patient_chamber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('chamber_id')->constrained('chambers')->cascadeOnDelete();
            $table->integer('visit_count')->default(1);
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamps();

            $table->unique(['patient_id', 'chamber_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_chamber');
    }
};
