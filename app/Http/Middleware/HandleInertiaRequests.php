<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if ($request->is('install*') || !file_exists(storage_path('installed'))) {
            return [];
        }

        $user = $request->user();
        $userChambers = [];
        $activeChamber = null;

        $canUseAi = false;
        $permissions = [];
        if ($user) {
            $user->load(['doctorProfile', 'staffProfile']);
            $doctorId = $user->getDoctorId();

            $chambersQuery = \App\Models\Chamber::where('doctor_id', $doctorId)
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('name');

            if ($user->isStaff() && $user->staffProfile) {
                $assignedChamberIds = $user->staffProfile->assignedChamberIds();
                if (!empty($assignedChamberIds)) {
                    $chambersQuery->whereIn('id', $assignedChamberIds);
                }
            }

            $userChambers = $chambersQuery->get();

            $activeChamberId = session('active_chamber_id');
            if ($activeChamberId) {
                $activeChamber = $userChambers->firstWhere('id', $activeChamberId);
            }

            if (!$activeChamber && $userChambers->isNotEmpty()) {
                $activeChamber = $userChambers->firstWhere('is_default', true) ?? $userChambers->first();
                session(['active_chamber_id' => $activeChamber->id]);
            }

            $canUseAi = $user->canUseAi();
            $permissions = $user->getStaffPermissions();
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'can_use_ai' => $canUseAi,
                'permissions' => $permissions,
            ],
            'activeChamber' => $activeChamber,
            'userChambers' => $userChambers,
            'isImpersonating' => session()->has('admin_impersonator_id'),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'appSettings' => [
                'brand_name' => \App\Models\SystemSetting::get('landing_brand_name', 'Doc Helper'),
                'brand_name_highlight' => \App\Models\SystemSetting::get('landing_brand_name_highlight', 'AI'),
                'brand_tagline' => \App\Models\SystemSetting::get('landing_brand_tagline', 'Next-Gen Medical Practice & Rx Platform'),
                'brand_logo_url' => \App\Models\SystemSetting::get('landing_brand_logo_url', null),
                'brand_favicon_url' => \App\Models\SystemSetting::get('landing_brand_favicon_url', null),
                'footer_copyright' => \App\Models\SystemSetting::get('landing_footer_copyright', 'Doc Helper AI SaaS'),
            ],
        ];
    }
}
