<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminPricingPlanController extends Controller
{
    /**
     * Display all pricing plans for Super Admin.
     */
    public function index(): Response
    {
        $plans = PricingPlan::orderBy('sort_order', 'asc')->get();
        $annualDiscountBadge = SystemSetting::get('pricing_annual_discount_badge', 'SAVE 25%');

        return Inertia::render('Admin/PricingPlans/Index', [
            'plans' => $plans,
            'annualDiscountBadge' => $annualDiscountBadge,
        ]);
    }

    /**
     * Update global pricing settings (e.g. Annual Discount Badge text).
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $badge = $request->input('pricing_annual_discount_badge') 
            ?? $request->input('annual_discount_badge') 
            ?? 'SAVE 25%';

        SystemSetting::set('pricing_annual_discount_badge', $badge, 'pricing', 'string');

        return back()->with('success', 'Annual discount badge text updated successfully.');
    }

    /**
     * Store a newly created pricing plan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:pricing_plans,slug',
            'tagline' => 'nullable|string|max:255',
            'currency' => 'required|string|max:10',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
            'annual_discount_text' => 'nullable|string|max:100',
            'badge_text' => 'nullable|string|max:100',
            'is_featured' => 'required|boolean',
            'is_active' => 'required|boolean',
            'button_text' => 'required|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = (PricingPlan::max('sort_order') ?? 0) + 1;
        }

        PricingPlan::create($validated);

        return back()->with('success', "Pricing Plan '{$validated['name']}' created successfully.");
    }

    /**
     * Update an existing pricing plan.
     */
    public function update(Request $request, PricingPlan $pricingPlan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:pricing_plans,slug,' . $pricingPlan->id,
            'tagline' => 'nullable|string|max:255',
            'currency' => 'required|string|max:10',
            'monthly_price' => 'required|numeric|min:0',
            'annual_price' => 'required|numeric|min:0',
            'annual_discount_text' => 'nullable|string|max:100',
            'badge_text' => 'nullable|string|max:100',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:255',
            'features' => 'required|array|min:1',
            'features.*' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['slug'] = $validated['slug'] ?? $pricingPlan->slug;
        $validated['button_text'] = $validated['button_text'] ?? $pricingPlan->button_text ?? 'Get Started';
        $validated['is_featured'] = isset($validated['is_featured']) ? (bool)$validated['is_featured'] : $pricingPlan->is_featured;
        $validated['is_active'] = isset($validated['is_active']) ? (bool)$validated['is_active'] : $pricingPlan->is_active;

        $pricingPlan->update($validated);

        return back()->with('success', "Pricing Plan '{$pricingPlan->name}' updated successfully.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(PricingPlan $pricingPlan): RedirectResponse
    {
        $pricingPlan->update([
            'is_active' => !$pricingPlan->is_active,
        ]);

        $statusStr = $pricingPlan->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Plan '{$pricingPlan->name}' {$statusStr}.");
    }

    /**
     * Delete a pricing plan.
     */
    public function destroy(PricingPlan $pricingPlan): RedirectResponse
    {
        $name = $pricingPlan->name;
        $pricingPlan->delete();

        return back()->with('success', "Pricing Plan '{$name}' deleted.");
    }
}