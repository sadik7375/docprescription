<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminLandingContentController extends Controller
{
    /**
     * Display the Landing Page Content Customizer in Super Admin.
     */
    public function index(): Response
    {
        $content = [
            // Brand & Logo
            'brand_name' => SystemSetting::get('landing_brand_name', 'Doc Helper'),
            'brand_name_highlight' => SystemSetting::get('landing_brand_name_highlight', 'AI'),
            'brand_tagline' => SystemSetting::get('landing_brand_tagline', 'Clinical Practice OS'),
            'brand_logo_url' => SystemSetting::get('landing_brand_logo_url', null),
            'brand_favicon_url' => SystemSetting::get('landing_brand_favicon_url', null),

            // Meta
            'meta_title' => SystemSetting::get('landing_meta_title', 'Doc Helper AI - Intelligent Medical Prescription & Clinic Practice OS'),
            'meta_description' => SystemSetting::get('landing_meta_description', 'Generate comprehensive, flawless digital prescriptions in under 45 seconds. Manage multi-chamber queues, patient vitals, diagnosis lookups, and instant branded printouts effortlessly.'),

            // Hero Section
            'hero_badge' => SystemSetting::get('landing_hero_badge', 'Next-Generation Medical Practice & AI Prescription Platform'),
            'hero_version_text' => SystemSetting::get('landing_hero_version_text', 'v2.4 Live'),
            'hero_title_line1' => SystemSetting::get('landing_hero_title_line1', 'The Smart AI-Powered'),
            'hero_title_highlight' => SystemSetting::get('landing_hero_title_highlight', 'Prescription & Practice OS'),
            'hero_title_line2' => SystemSetting::get('landing_hero_title_line2', 'for Modern Doctors'),
            'hero_subtitle' => SystemSetting::get('landing_hero_subtitle', 'Generate comprehensive, flawless digital prescriptions in under 45 seconds. Manage multi-chamber queues, patient vitals, diagnosis lookups, and instant branded printouts effortlessly.'),
            'hero_cta_primary_text' => SystemSetting::get('landing_hero_cta_primary_text', 'Start 14-Day Free Trial'),
            'hero_cta_primary_link' => SystemSetting::get('landing_hero_cta_primary_link', '/register'),
            'hero_cta_secondary_text' => SystemSetting::get('landing_hero_cta_secondary_text', 'Try Interactive Demo Rx'),
            'hero_cta_secondary_link' => SystemSetting::get('landing_hero_cta_secondary_link', '#live-demo'),
            'hero_image_url' => SystemSetting::get('landing_hero_image_url', null),

            // Trust Points
            'trust_point_1' => SystemSetting::get('landing_trust_point_1', 'No Credit Card Required'),
            'trust_point_2' => SystemSetting::get('landing_trust_point_2', 'HIPAA-Ready Architecture'),
            'trust_point_3' => SystemSetting::get('landing_trust_point_3', '4.9/5 Rating (2,500+ Doctors)'),

            // Key Statistics Counters (Array)
            'stats' => SystemSetting::get('landing_stats', [
                ['value' => '< 45s', 'label' => 'Average Rx Creation Time', 'sub' => 'Save 3+ hours daily'],
                ['value' => '2.5M+', 'label' => 'Prescriptions Generated', 'sub' => 'Across 40+ countries'],
                ['value' => '99.8%', 'label' => 'Dosage Precision Rate', 'sub' => 'Zero handwriting errors'],
                ['value' => '45+', 'label' => 'Clinical Specialties', 'sub' => 'General to Sub-specialties'],
            ]),

            // Features Section Header
            'features_badge' => SystemSetting::get('landing_features_badge', 'Engineered for High-Volume Clinical Precision'),
            'features_title' => SystemSetting::get('landing_features_title', 'Everything a Doctor Needs in One Seamless Platform'),
            'features_subtitle' => SystemSetting::get('landing_features_subtitle', 'Replace messy paper pads, slow generic ERPs, and disjointed systems with a purpose-built medical practice operating system.'),

            // Features List (Array of 6 features)
            'features' => SystemSetting::get('landing_features_items', [
                [
                    'title' => 'Smart Type-Ahead Rx Pad',
                    'desc' => "Blazing fast dosage shortcuts ('1+0+1', '1+1+1+1'), intelligent meal advice chips, auto-duration calculations, and customized drug favorites.",
                    'color' => 'teal'
                ],
                [
                    'title' => 'Multi-Chamber & Visiting Slots',
                    'desc' => 'Manage multiple private clinics and hospital chambers with specific visiting days, consulting hours, fees, room numbers, and active switchers.',
                    'color' => 'cyan'
                ],
                [
                    'title' => 'Live Queue & Patient Vitals',
                    'desc' => 'Organize daily patient queues, capture comprehensive vitals (BP, Pulse, SpO2, BMI, Temp), and update consultation status in real-time.',
                    'color' => 'indigo'
                ],
                [
                    'title' => 'Clinical & Drug Database',
                    'desc' => 'Pre-loaded lookup libraries for Chief Complaints, ICD-10 Diagnoses, Investigations, Advice, and custom brand/generic medicines.',
                    'color' => 'emerald'
                ],
                [
                    'title' => '1-Click High-Res Print & PDF',
                    'desc' => 'Generate clean, international medical print layouts with degrees, Reg. No, customized seals, QR verification, and margin adjustment.',
                    'color' => 'purple'
                ],
                [
                    'title' => 'Patient Portal & History',
                    'desc' => 'Enable patient portal access for digital prescriptions, past visit summaries, follow-up date notifications, and paperless medical records.',
                    'color' => 'rose'
                ],
            ]),

            // Specialties List (Array)
            'specialties_title' => SystemSetting::get('landing_specialties_title', 'Tailored for Every Medical Specialty'),
            'specialties_subtitle' => SystemSetting::get('landing_specialties_subtitle', 'Customizable clinical templates and database lookups optimized for individual clinical workflows.'),
            'specialties' => SystemSetting::get('landing_specialties', [
                'Cardiology', 'Pediatrics', 'Gynecology & OBS', 'General Medicine',
                'Orthopedics', 'Dermatology', 'Neurology', 'ENT', 'Gastroenterology',
                'Psychiatry', 'Ophthalmology', 'Pulmonology', 'Endocrinology', 'Urology'
            ]),

            // Testimonials (Array)
            'testimonials_title' => SystemSetting::get('landing_testimonials_title', 'Trusted by Thousands of Doctors Worldwide'),
            'testimonials_subtitle' => SystemSetting::get('landing_testimonials_subtitle', 'See how clinical specialists streamline their daily patient consultations.'),
            'testimonials' => SystemSetting::get('landing_testimonials', [
                [
                    'quote' => 'Doc Helper AI cut my consultation prescription time from 5 minutes down to 35 seconds. The dual-chamber switcher lets me manage both my hospital and private practice seamlessly.',
                    'author' => 'Dr. Alexander King',
                    'title' => 'Cardiologist, Apollo Health',
                    'rating' => 5,
                ],
                [
                    'quote' => 'The print layout is crisp and professional. All my qualifications and medical registration details are automatically positioned. Patients love the clean, legible prescriptions.',
                    'author' => 'Dr. Elena Morales',
                    'title' => 'Senior Consultant Pediatrician',
                    'rating' => 5,
                ],
                [
                    'quote' => 'The best SaaS medical tool we have deployed. As a clinic director, managing 12 visiting specialists with distinct schedules and queues has never been this smooth.',
                    'author' => 'Dr. Robert Jenkins',
                    'title' => 'Medical Director, Apex Health',
                    'rating' => 5,
                ],
            ]),

            // FAQs (Array)
            'faqs_title' => SystemSetting::get('landing_faqs_title', 'Frequently Asked Questions'),
            'faqs_subtitle' => SystemSetting::get('landing_faqs_subtitle', 'Everything you need to know about Doc Helper AI and clinical deployment.'),
            'faqs' => SystemSetting::get('landing_faqs', [
                [
                    'q' => 'Can I customize the prescription header with my medical degree and registration number?',
                    'a' => 'Yes, absolutely. Doc Helper AI includes a dedicated Doctor Profile & Credentials Manager where you can add all your medical degrees (MBBS, FCPS, MD, MRCP, FRCS, etc.), medical registration/license number, specialty, workplace affiliations, and custom signature text with a live prescription header preview.'
                ],
                [
                    'q' => 'Does Doc Helper AI support multiple chambers and different visiting hours?',
                    'a' => 'Yes! You can add and manage unlimited chambers or hospital visiting rooms. Each chamber has its own consultation fees, follow-up fees, visiting schedule, room numbers, and patient queues. You can switch between active chambers with a single click.'
                ],
                [
                    'q' => 'Is this software ready for international deployment and SaaS licensing?',
                    'a' => 'Yes. Doc Helper AI is built according to 100% international medical practice standards in clean English, making it perfect for private clinics, multi-doctor hospitals, and CodeCanyon SaaS distribution.'
                ],
                [
                    'q' => 'How fast is prescription generation compared to traditional writing?',
                    'a' => 'With intelligent type-ahead auto-complete, pre-saved clinical lookups (Chief Complaints, Diagnoses, Investigations, General Advice) and drug dosages, doctors routinely draft complete, error-free prescriptions in under 45 seconds.'
                ],
                [
                    'q' => 'Does Doc Helper AI support high-resolution printing on custom letterheads or thermal printers?',
                    'a' => 'Yes. You can print directly to standard A4/A5 prescription pads, plain white paper with customizable headers, pre-printed clinic letterheads (with margins adjusted), or digital PDF downloads for patient sharing.'
                ],
                [
                    'q' => 'Can patients access their medical history and previous prescriptions?',
                    'a' => 'Yes. Doc Helper AI features an integrated Patient Portal module where doctors or staff can create secure patient logins, allowing patients to view their previous prescriptions, vital trends, and follow-up dates.'
                ]
            ]),

            // Bottom CTA
            'bottom_cta_title' => SystemSetting::get('landing_bottom_cta_title', 'Transform Your Medical Practice Today'),
            'bottom_cta_subtitle' => SystemSetting::get('landing_bottom_cta_subtitle', 'Join thousands of doctors who generate accurate digital prescriptions and manage their clinical chambers with Doc Helper AI.'),
            'bottom_cta_btn_text' => SystemSetting::get('landing_bottom_cta_btn_text', 'Start 14-Day Free Trial'),
            'bottom_cta_note' => SystemSetting::get('landing_bottom_cta_note', 'Instant Setup • No Installation Required • Cloud & Dedicated Host Ready'),

            // Footer & Legal
            'footer_about' => SystemSetting::get('landing_footer_about', 'The comprehensive medical prescription generator, clinical lookup management, and multi-chamber practice operating system for physicians, clinics, and health systems.'),
            'footer_copyright' => SystemSetting::get('landing_footer_copyright', '© Doc Helper AI. All rights reserved. International Medical Software Standard.'),
            'privacy_policy' => SystemSetting::get('landing_privacy_policy', "Your clinical and patient data is encrypted in transit and at rest using industry-standard 256-bit AES encryption.\n\n1. Patient Medical Record Confidentiality\nDoc Helper AI complies strictly with international healthcare data protection frameworks including HIPAA guidelines. All patient vitals, diagnoses, clinical history, and prescription records are stored in isolated doctor-tenant databases.\n\n2. No Unauthorized Data Commercialization\nWe do not sell, rent, or distribute patient medical records, physician prescription trends, or personal contact details to any third-party pharmaceutical entities or advertising networks.\n\n3. Doctor Ownership of Clinical Data\nPhysicians and registered clinics maintain full ownership of all clinical lookup databases, patient records, and generated prescription files. Doctors can export practice records at any time in CSV/PDF format."),
            'terms_of_service' => SystemSetting::get('landing_terms_of_service', "1. SaaS Subscription & Service License\nDoc Helper AI grants licensed physicians and clinical practices a non-exclusive, non-transferable license to access and utilize the prescription pad, multi-chamber queue, and patient management platform according to their subscribed plan tier.\n\n2. Multi-Chamber & Staff Account Usage\nPractice owners are responsible for safeguarding staff credentials and ensuring appropriate role-based permissions are assigned for appointment desk operators and nursing staff.\n\n3. Uptime & Service Level Agreement (SLA)\nWe target 99.9% service availability. Scheduled system upgrades and database optimizations are performed during off-peak clinical hours with advance dashboard notices."),
            'medical_disclaimer' => SystemSetting::get('landing_medical_disclaimer', "Notice: Doc Helper AI is a clinical workflow and practice management tool for licensed medical professionals.\n\n1. Clinical Professional Judgment\nThe prescription suggestions, type-ahead dosage hints, and drug lookup libraries provided within the platform are decision-support aids designed to accelerate typing and minimize transcription errors. They do not replace independent clinical judgment.\n\n2. Prescribing Authority\nOnly certified and legally registered healthcare practitioners are authorized to generate, finalize, and sign medical prescriptions for patients."),
        ];

        return Inertia::render('Admin/Landing/Index', [
            'content' => $content,
        ]);
    }

    /**
     * Save/Update Landing Page Content Settings & Images.
     */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Brand & Logo
            'brand_name' => 'nullable|string|max:100',
            'brand_name_highlight' => 'nullable|string|max:50',
            'brand_tagline' => 'nullable|string|max:150',
            'brand_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:3072',
            'brand_favicon' => 'nullable|image|mimes:jpeg,png,jpg,svg,ico,webp|max:1024',
            'remove_logo' => 'nullable|boolean',
            'remove_favicon' => 'nullable|boolean',

            // Meta
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',

            // Hero
            'hero_badge' => 'nullable|string|max:255',
            'hero_version_text' => 'nullable|string|max:50',
            'hero_title_line1' => 'nullable|string|max:255',
            'hero_title_highlight' => 'nullable|string|max:255',
            'hero_title_line2' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:1000',
            'hero_cta_primary_text' => 'nullable|string|max:100',
            'hero_cta_primary_link' => 'nullable|string|max:255',
            'hero_cta_secondary_text' => 'nullable|string|max:100',
            'hero_cta_secondary_link' => 'nullable|string|max:255',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'remove_hero_image' => 'nullable|boolean',

            // Trust points
            'trust_point_1' => 'nullable|string|max:150',
            'trust_point_2' => 'nullable|string|max:150',
            'trust_point_3' => 'nullable|string|max:150',

            // Stats
            'stats' => 'nullable|array',
            'stats.*.value' => 'required|string',
            'stats.*.label' => 'required|string',
            'stats.*.sub' => 'nullable|string',

            // Features
            'features_badge' => 'nullable|string|max:255',
            'features_title' => 'nullable|string|max:255',
            'features_subtitle' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
            'features.*.title' => 'required|string',
            'features.*.desc' => 'required|string',
            'features.*.color' => 'nullable|string',

            // Specialties
            'specialties_title' => 'nullable|string|max:255',
            'specialties_subtitle' => 'nullable|string|max:1000',
            'specialties' => 'nullable|array',

            // Testimonials
            'testimonials_title' => 'nullable|string|max:255',
            'testimonials_subtitle' => 'nullable|string|max:1000',
            'testimonials' => 'nullable|array',
            'testimonials.*.quote' => 'required|string',
            'testimonials.*.author' => 'required|string',
            'testimonials.*.title' => 'required|string',
            'testimonials.*.rating' => 'nullable|integer',

            // FAQs
            'faqs_title' => 'nullable|string|max:255',
            'faqs_subtitle' => 'nullable|string|max:1000',
            'faqs' => 'nullable|array',
            'faqs.*.q' => 'required|string',
            'faqs.*.a' => 'required|string',

            // Bottom CTA
            'bottom_cta_title' => 'nullable|string|max:255',
            'bottom_cta_subtitle' => 'nullable|string|max:1000',
            'bottom_cta_btn_text' => 'nullable|string|max:100',
            'bottom_cta_note' => 'nullable|string|max:255',

            // Footer & Legal
            'footer_about' => 'nullable|string|max:1000',
            'footer_copyright' => 'nullable|string|max:255',
            'privacy_policy' => 'nullable|string|max:10000',
            'terms_of_service' => 'nullable|string|max:10000',
            'medical_disclaimer' => 'nullable|string|max:10000',
        ]);

        // Handle Brand Logo Upload
        if ($request->hasFile('brand_logo')) {
            $path = $request->file('brand_logo')->store('landing/branding', 'public');
            SystemSetting::set('landing_brand_logo_url', '/storage/' . $path, 'landing', 'string');
        } elseif ($request->boolean('remove_logo')) {
            SystemSetting::set('landing_brand_logo_url', null, 'landing', 'string');
        }

        // Handle Favicon Upload
        if ($request->hasFile('brand_favicon')) {
            $path = $request->file('brand_favicon')->store('landing/branding', 'public');
            SystemSetting::set('landing_brand_favicon_url', '/storage/' . $path, 'landing', 'string');
        } elseif ($request->boolean('remove_favicon')) {
            SystemSetting::set('landing_brand_favicon_url', null, 'landing', 'string');
        }

        // Handle Custom Hero Mockup/Banner Image Upload
        if ($request->hasFile('hero_image')) {
            $path = $request->file('hero_image')->store('landing/hero', 'public');
            SystemSetting::set('landing_hero_image_url', '/storage/' . $path, 'landing', 'string');
        } elseif ($request->boolean('remove_hero_image')) {
            SystemSetting::set('landing_hero_image_url', null, 'landing', 'string');
        }

        // Unset file/flag keys before saving standard attributes
        unset($data['brand_logo'], $data['brand_favicon'], $data['hero_image'], $data['remove_logo'], $data['remove_favicon'], $data['remove_hero_image']);

        foreach ($data as $key => $value) {
            $settingKey = 'landing_' . $key;
            $type = is_array($value) ? 'json' : 'string';
            SystemSetting::set($settingKey, $value, 'landing', $type);
        }

        return back()->with('success', 'Landing page branding, logo, images, and content updated successfully!');
    }
}