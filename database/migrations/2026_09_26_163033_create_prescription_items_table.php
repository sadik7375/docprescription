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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('drug_id')->nullable()->constrained('drugs')->nullOnDelete();
            $table->string('medicine_name');
            $table->string('generic_name')->nullable();
            $table->string('dosage_form', 50)->nullable(); // Tab, Cap, Syp, Inj, Drop, etc.
            $table->string('strength', 50)->nullable(); // 500mg, 20mg
            $table->string('dosage', 100)->nullable(); // 1+0+1, 1+1+1, etc.
            $table->string('duration', 100)->nullable(); // 7 days, 1 month, Continue
            $table->string('instruction', 150)->nullable(); // After meal / Before meal
            $table->text('extra_notes')->nullable();
            $table->integer('item_order')->default(1);
            $table->timestamps();

            $table->index(['prescription_id', 'item_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
