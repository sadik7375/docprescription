<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\PricingPlan;

use App\Http\Controllers\ContactController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\AdminSupportController;
use App\Models\SystemSetting;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'pricingPlans' => PricingPlan::active()->get(),
        'annualDiscountBadge' => SystemSetting::get('pricing_annual_discount_badge', 'SAVE 25%'),
        'landingContent' => [
            'brand_name' => SystemSetting::get('landing_brand_name', 'Doc Helper'),
            'brand_name_highlight' => SystemSetting::get('landing_brand_name_highlight', 'AI'),
            'brand_tagline' => SystemSetting::get('landing_brand_tagline', 'Clinical Practice OS'),
            'brand_logo_url' => SystemSetting::get('landing_brand_logo_url', null),
            'brand_favicon_url' => SystemSetting::get('landing_brand_favicon_url', null),
            'hero_image_url' => SystemSetting::get('landing_hero_image_url', null),
            'meta_title' => SystemSetting::get('landing_meta_title', 'Doc Helper AI - Intelligent Medical Prescription & Clinic Practice OS'),
            'meta_description' => SystemSetting::get('landing_meta_description', 'Generate comprehensive, flawless digital prescriptions in under 45 seconds. Manage multi-chamber queues, patient vitals, diagnosis lookups, and instant branded printouts effortlessly.'),
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
            'trust_point_1' => SystemSetting::get('landing_trust_point_1', 'No Credit Card Required'),
            'trust_point_2' => SystemSetting::get('landing_trust_point_2', 'HIPAA-Ready Architecture'),
            'trust_point_3' => SystemSetting::get('landing_trust_point_3', '4.9/5 Rating (2,500+ Doctors)'),
            'stats' => SystemSetting::get('landing_stats', [
                ['value' => '< 45s', 'label' => 'Average Rx Creation Time', 'sub' => 'Save 3+ hours daily'],
                ['value' => '2.5M+', 'label' => 'Prescriptions Generated', 'sub' => 'Across 40+ countries'],
                ['value' => '99.8%', 'label' => 'Dosage Precision Rate', 'sub' => 'Zero handwriting errors'],
                ['value' => '45+', 'label' => 'Clinical Specialties', 'sub' => 'General to Sub-specialties'],
            ]),
            'features_badge' => SystemSetting::get('landing_features_badge', 'Engineered for High-Volume Clinical Precision'),
            'features_title' => SystemSetting::get('landing_features_title', 'Everything a Doctor Needs in One Seamless Platform'),
            'features_subtitle' => SystemSetting::get('landing_features_subtitle', 'Replace messy paper pads, slow generic ERPs, and disjointed systems with a purpose-built medical practice operating system.'),
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
            'specialties_title' => SystemSetting::get('landing_specialties_title', 'Tailored for Every Medical Specialty'),
            'specialties_subtitle' => SystemSetting::get('landing_specialties_subtitle', 'Customizable clinical templates and database lookups optimized for individual clinical workflows.'),
            'specialties' => SystemSetting::get('landing_specialties', [
                'Cardiology', 'Pediatrics', 'Gynecology & OBS', 'General Medicine',
                'Orthopedics', 'Dermatology', 'Neurology', 'ENT', 'Gastroenterology',
                'Psychiatry', 'Ophthalmology', 'Pulmonology', 'Endocrinology', 'Urology'
            ]),
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
            'bottom_cta_title' => SystemSetting::get('landing_bottom_cta_title', 'Transform Your Medical Practice Today'),
            'bottom_cta_subtitle' => SystemSetting::get('landing_bottom_cta_subtitle', 'Join thousands of doctors who generate accurate digital prescriptions and manage their clinical chambers with Doc Helper AI.'),
            'bottom_cta_btn_text' => SystemSetting::get('landing_bottom_cta_btn_text', 'Start 14-Day Free Trial'),
            'bottom_cta_note' => SystemSetting::get('landing_bottom_cta_note', 'Instant Setup • No Installation Required • Cloud & Dedicated Host Ready'),
            'footer_about' => SystemSetting::get('landing_footer_about', 'The comprehensive medical prescription generator, clinical lookup management, and multi-chamber practice operating system for physicians, clinics, and health systems.'),
            'footer_copyright' => SystemSetting::get('landing_footer_copyright', '© Doc Helper AI. All rights reserved. International Medical Software Standard.'),
            'privacy_policy' => SystemSetting::get('landing_privacy_policy', null),
            'terms_of_service' => SystemSetting::get('landing_terms_of_service', null),
            'medical_disclaimer' => SystemSetting::get('landing_medical_disclaimer', null),
        ],
        'supportContacts' => [
            'email' => SystemSetting::get('support_email', 'contact@cannyapps.com'),
            'phone' => SystemSetting::get('support_phone', '+1 (800) 555-0199'),
            'whatsapp' => SystemSetting::get('support_whatsapp', '+18005550199'),
            'hours' => SystemSetting::get('support_hours', 'Monday - Friday: 9:00 AM - 8:00 PM EST'),
            'address' => SystemSetting::get('support_address', '100 Medical Plaza, Suite 400, San Francisco, CA 94143'),
        ],
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::post('/contact-message', [ContactController::class, 'submitLandingContact'])->name('contact.submit');

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ChamberController;
use App\Http\Controllers\ClinicalLookupController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\DrugLookupController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Prescription & Live Rx Pad Routes
    Route::resource('prescriptions', PrescriptionController::class);
    Route::get('/prescriptions/{prescription}/print', [PrescriptionController::class, 'print'])->name('prescriptions.print');

    // Appointment & Patient Queue Management Routes
    Route::resource('appointments', AppointmentController::class);
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
    Route::patch('/appointments/{appointment}/vitals', [AppointmentController::class, 'updateVitals'])->name('appointments.update-vitals');
    Route::patch('/appointments/{appointment}/toggle-payment', [AppointmentController::class, 'togglePayment'])->name('appointments.toggle-payment');

    // Chamber Management Routes & Switcher
    Route::resource('chambers', ChamberController::class);
    Route::post('/chambers/{chamber}/switch', [ChamberController::class, 'switchChamber'])->name('chambers.switch');
    Route::post('/chambers/{chamber}/set-default', [ChamberController::class, 'setDefault'])->name('chambers.set-default');

    // Patient Management Module Routes
    Route::resource('patients', PatientController::class);
    Route::post('/patients/{patient}/create-account', [PatientController::class, 'createAccount'])->name('patients.create-account');

    // Prescription & Clinical Lookups Routes
    Route::get('/clinical-lookups/{type?}', [ClinicalLookupController::class, 'index'])->name('clinical-lookups.index');

    // Doctor Helpdesk & Support Tickets
    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportTicketController::class, 'store'])->name('support.store');
    Route::post('/support/{supportTicket}/reply', [SupportTicketController::class, 'reply'])->name('support.reply');
    Route::post('/clinical-lookups/{type}', [ClinicalLookupController::class, 'store'])->name('clinical-lookups.store');
    Route::delete('/clinical-lookups/{type}/{id}', [ClinicalLookupController::class, 'destroy'])->name('clinical-lookups.destroy');

    // Drug Management Module Routes
    Route::resource('drugs', DrugController::class);
    Route::get('/drug-lookups/{type?}', [DrugLookupController::class, 'index'])->name('drug-lookups.index');
    Route::post('/drug-lookups/{type}', [DrugLookupController::class, 'store'])->name('drug-lookups.store');
    Route::delete('/drug-lookups/{type}/{id}', [DrugLookupController::class, 'destroy'])->name('drug-lookups.destroy');

    // Staff & Role Permissions Management Routes
    Route::resource('staff', \App\Http\Controllers\StaffController::class);
    Route::patch('/staff/{staff}/toggle-status', [\App\Http\Controllers\StaffController::class, 'toggleStatus'])->name('staff.toggle-status');

    // AI Clinical Assistant, Pharmacology Checker & Outbreak Trends Routes
    Route::post('/ai/check-interactions', [\App\Http\Controllers\AiAssistantController::class, 'checkInteractions'])->name('ai.check-interactions');
    Route::post('/ai/chat', [\App\Http\Controllers\AiAssistantController::class, 'chat'])->name('ai.chat');
    Route::post('/ai/patient-history-summary', [\App\Http\Controllers\AiAssistantController::class, 'summarizePatientHistory'])->name('ai.patient-history-summary');
    Route::get('/ai/chamber-disease-trends', [\App\Http\Controllers\AiAssistantController::class, 'getChamberDiseaseTrends'])->name('ai.chamber-disease-trends');

    // Practice Reports, Running Medicines & AI Analytics Module Routes
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'exportCsv'])->name('reports.export');

    // Doctor Subscription, Plan Upgrade & Multi-Gateway Checkout Routes
    Route::get('/subscription', [\App\Http\Controllers\SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/stripe/checkout', [\App\Http\Controllers\SubscriptionController::class, 'initiateStripe'])->name('subscription.stripe.checkout');
    Route::get('/subscription/stripe/success', [\App\Http\Controllers\SubscriptionController::class, 'stripeSuccess'])->name('subscription.stripe.success');
    Route::post('/subscription/paypal/checkout', [\App\Http\Controllers\SubscriptionController::class, 'initiatePayPal'])->name('subscription.paypal.checkout');
    Route::get('/subscription/paypal/success', [\App\Http\Controllers\SubscriptionController::class, 'paypalSuccess'])->name('subscription.paypal.success');
    Route::post('/subscription/razorpay/order', [\App\Http\Controllers\SubscriptionController::class, 'createRazorpayOrder'])->name('subscription.razorpay.order');
    Route::post('/subscription/razorpay/verify', [\App\Http\Controllers\SubscriptionController::class, 'verifyRazorpay'])->name('subscription.razorpay.verify');
    Route::post('/subscription/manual-payment', [\App\Http\Controllers\SubscriptionController::class, 'submitManualPayment'])->name('subscription.manual-payment');
});

use App\Http\Controllers\Admin\AdminAiSettingController;
use App\Http\Controllers\Admin\AdminLandingContentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminDoctorController;
use App\Http\Controllers\Admin\AdminMailSettingController;
use App\Http\Controllers\Admin\AdminPaymentSettingController;
use App\Http\Controllers\Admin\AdminPricingPlanController;
use App\Http\Middleware\EnsureSuperAdmin;

Route::middleware(['auth', EnsureSuperAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.index');
    
    // Doctor & Trial Management
    Route::get('/doctors', [AdminDoctorController::class, 'index'])->name('doctors.index');
    Route::post('/doctors', [AdminDoctorController::class, 'store'])->name('doctors.store');
    Route::patch('/doctors/{doctor}/subscription', [AdminDoctorController::class, 'updateSubscription'])->name('doctors.update-subscription');
    Route::patch('/doctors/{doctor}/toggle-status', [AdminDoctorController::class, 'toggleStatus'])->name('doctors.toggle-status');
    Route::post('/doctors/{doctor}/impersonate', [AdminDoctorController::class, 'impersonate'])->name('doctors.impersonate');
    Route::delete('/doctors/{doctor}', [AdminDoctorController::class, 'destroy'])->name('doctors.destroy');

    // AI API Key & Model Configuration
    Route::get('/ai-settings', [AdminAiSettingController::class, 'index'])->name('ai-settings.index');
    Route::post('/ai-settings', [AdminAiSettingController::class, 'update'])->name('ai-settings.update');
    Route::post('/ai-settings/test', [AdminAiSettingController::class, 'testConnection'])->name('ai-settings.test');

    // Pricing & Subscription Packages Management
    Route::get('/pricing-plans', [AdminPricingPlanController::class, 'index'])->name('pricing-plans.index');
    Route::post('/pricing-plans/settings', [AdminPricingPlanController::class, 'updateSettings'])->name('pricing-plans.update-settings');
    Route::post('/pricing-plans', [AdminPricingPlanController::class, 'store'])->name('pricing-plans.store');
    Route::put('/pricing-plans/{pricingPlan}', [AdminPricingPlanController::class, 'update'])->name('pricing-plans.update');
    Route::patch('/pricing-plans/{pricingPlan}/toggle-status', [AdminPricingPlanController::class, 'toggleStatus'])->name('pricing-plans.toggle-status');
    Route::delete('/pricing-plans/{pricingPlan}', [AdminPricingPlanController::class, 'destroy'])->name('pricing-plans.destroy');

    // Payment Gateway Settings & Transaction Verification
    Route::get('/payment-settings', [AdminPaymentSettingController::class, 'index'])->name('payment-settings.index');
    Route::post('/payment-settings', [AdminPaymentSettingController::class, 'update'])->name('payment-settings.update');
    Route::post('/payment-settings/approve/{transaction}', [AdminPaymentSettingController::class, 'approveTransaction'])->name('payment-settings.approve');

    // SMTP & Email Delivery Settings
    Route::get('/mail-settings', [AdminMailSettingController::class, 'index'])->name('mail-settings.index');
    Route::post('/mail-settings', [AdminMailSettingController::class, 'update'])->name('mail-settings.update');
    Route::post('/mail-settings/test', [AdminMailSettingController::class, 'testEmail'])->name('mail-settings.test');

        // Landing Page Content Customizer
    Route::get('/landing-content', [AdminLandingContentController::class, 'index'])->name('landing-content.index');
    Route::post('/landing-content', [AdminLandingContentController::class, 'update'])->name('landing-content.update');

    // Support Tickets & Landing Contact Inquiries
    Route::get('/contacts', [AdminSupportController::class, 'contacts'])->name('contacts.index');
    Route::post('/contacts/{contactMessage}/status', [AdminSupportController::class, 'updateContactStatus'])->name('contacts.update-status');
    Route::delete('/contacts/{contactMessage}', [AdminSupportController::class, 'destroyContact'])->name('contacts.destroy');

    Route::get('/tickets', [AdminSupportController::class, 'tickets'])->name('tickets.index');
    Route::post('/tickets/{supportTicket}/reply', [AdminSupportController::class, 'replyTicket'])->name('tickets.reply');
    Route::post('/tickets/{supportTicket}/status', [AdminSupportController::class, 'updateTicketStatus'])->name('tickets.update-status');

    Route::get('/support-settings', [AdminSupportController::class, 'settings'])->name('support-settings.index');
    Route::post('/support-settings', [AdminSupportController::class, 'updateSettings'])->name('support-settings.update');
});

Route::post('/admin/stop-impersonating', [AdminDoctorController::class, 'stopImpersonating'])
    ->middleware('auth')
    ->name('admin.stop-impersonating');

require __DIR__.'/auth.php';



use App\Http\Controllers\InstallerController;

/*
|--------------------------------------------------------------------------
| Web Installation Wizard Routes
|--------------------------------------------------------------------------
*/
Route::prefix('install')->name('installer.')->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
    Route::get('/permissions', [InstallerController::class, 'permissions'])->name('permissions');
    Route::get('/environment', [InstallerController::class, 'environment'])->name('environment');
    Route::post('/environment', [InstallerController::class, 'saveEnvironment'])->name('environment.save');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database/run', [InstallerController::class, 'runDatabase'])->name('database.run');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin/save', [InstallerController::class, 'saveAdmin'])->name('admin.save');
    Route::get('/finish', [InstallerController::class, 'finish'])->name('finish');
});
