<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->string('currency')->default('$');
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('annual_price', 10, 2)->default(0);
            $table->string('annual_discount_text')->nullable()->default('SAVE 25%');
            $table->string('badge_text')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('button_text')->default('Get Started');
            $table->string('button_link')->nullable();
            $table->json('features')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed initial default plans
        DB::table('pricing_plans')->insert([
            [
                'slug' => 'solo',
                'name' => 'Solo Physician',
                'tagline' => 'Essential digital prescription pad for independent private practitioners.',
                'currency' => '$',
                'monthly_price' => 19.00,
                'annual_price' => 228.00,
                'annual_discount_text' => 'Billed annually ($228/yr)',
                'badge_text' => null,
                'is_featured' => false,
                'is_active' => true,
                'button_text' => 'Start 14-Day Free Trial',
                'button_link' => '/register',
                'features' => json_encode([
                    'Unlimited Digital Prescriptions',
                    'Single Chamber & Schedule',
                    'Standard Print & PDF Layouts',
                    'Doctor Profile & Degree Customizer',
                    'Basic Drug & Lookup Database',
                ]),
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'pro',
                'name' => 'Professional Practice',
                'tagline' => 'Complete multi-chamber and AI-powered practice system.',
                'currency' => '$',
                'monthly_price' => 39.00,
                'annual_price' => 468.00,
                'annual_discount_text' => 'Billed annually ($468/yr)',
                'badge_text' => '★ MOST POPULAR FOR CLINICS',
                'is_featured' => true,
                'is_active' => true,
                'button_text' => 'Get Started with Pro Practice',
                'button_link' => '/register',
                'features' => json_encode([
                    'Everything in Solo Physician',
                    'Unlimited Multi-Chamber Visiting Slots',
                    'AI Drug Interaction & Allergy Checker',
                    'AI Clinical Chat Copilot (dosing & alternatives)',
                    'Live Patient Queue & Vitals Tracking',
                    'Patient Portal & Account Access',
                    'Custom Digital Seal & Signature',
                    'Full Clinical & Drug Lookup Manager',
                ]),
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Hospital & Enterprise',
                'tagline' => 'For multi-doctor clinics, hospitals, or CodeCanyon full SaaS deployment.',
                'currency' => '$',
                'monthly_price' => 99.00,
                'annual_price' => 999.00,
                'annual_discount_text' => 'Or Lifetime CodeCanyon License',
                'badge_text' => null,
                'is_featured' => false,
                'is_active' => true,
                'button_text' => 'Contact Enterprise Team',
                'button_link' => '#contact',
                'features' => json_encode([
                    'Unlimited Doctors & Staff',
                    'Multi-Branch Clinic Management',
                    'Centralized Super Admin Control',
                    'Custom Domain & White-Labeling',
                    'HL7 / FHIR Medical Data Export',
                    'Dedicated 24/7 SLA & Setup Support',
                ]),
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
