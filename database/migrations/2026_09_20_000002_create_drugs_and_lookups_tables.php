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
        // 1. Drug Types (Tab, Cap, Syp, Inj, Drop, etc.)
        Schema::create('drug_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->timestamps();
        });

        // 2. Drug Doses (1+1+1, 1+0+1, 0+0+1, etc.)
        Schema::create('drug_doses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('bangla_name')->nullable();
            $table->timestamps();
        });

        // 3. Drug Strengths (500mg, 20mg, 10mg, 5mg, etc.)
        Schema::create('drug_strengths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 4. Drug Durations (3 days, 5 days, 7 days, 1 month, etc.)
        Schema::create('drug_durations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // 5. Drug Advices (Before meal, After meal, খাবার পরে, ইত্যাদি)
        Schema::create('drug_advices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('bangla_name')->nullable();
            $table->timestamps();
        });

        // 6. Main Drugs Table
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('trade_name');
            $table->string('generic_name')->nullable();
            $table->text('note')->nullable();
            $table->text('warning')->nullable();
            $table->text('side_effect')->nullable();
            $table->text('additional_advice')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('trade_name');
            $table->index('generic_name');
        });

        // 7. Drug Variations (Type, Strength, Dose, Duration, Advice)
        Schema::create('drug_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained()->cascadeOnDelete();
            $table->string('type')->nullable(); // Tab, Cap, Syp, etc.
            $table->string('strength')->nullable(); // 500mg, 20mg
            $table->string('dose')->nullable(); // 1+1+1, 1+0+1
            $table->string('duration')->nullable(); // 7 days
            $table->text('advice')->nullable(); // Before/After meal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_variations');
        Schema::dropIfExists('drugs');
        Schema::dropIfExists('drug_advices');
        Schema::dropIfExists('drug_durations');
        Schema::dropIfExists('drug_strengths');
        Schema::dropIfExists('drug_doses');
        Schema::dropIfExists('drug_types');
    }
};
