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
        Schema::create('chambers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('branch')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->decimal('consultation_fee', 10, 2)->default(0.00);
            $table->decimal('followup_fee', 10, 2)->default(0.00);
            $table->json('visiting_days')->nullable();
            $table->string('visiting_time', 100)->nullable();
            $table->string('room_no', 100)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chambers');
    }
};
