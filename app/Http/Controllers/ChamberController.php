<?php

namespace App\Http\Controllers;

use App\Models\Chamber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ChamberController extends Controller
{
    /**
     * Display a listing of doctor's chambers.
     */
    public function index(): Response
    {
        $doctorId = Auth::id();

        $chambers = Chamber::where('doctor_id', $doctorId)
            ->withCount('patients')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return Inertia::render('Chambers/Index', [
            'chambers' => $chambers,
            'limitCheck' => \App\Services\SubscriptionLimitService::canCreateChamber(Auth::user()),
        ]);
    }

    /**
     * Show the form for creating a new chamber.
     */
    public function create(): Response|RedirectResponse
    {
        $limitCheck = \App\Services\SubscriptionLimitService::canCreateChamber(Auth::user());
        if (!$limitCheck['allowed']) {
            return redirect()->route('chambers.index')->with('error', $limitCheck['reason']);
        }

        return Inertia::render('Chambers/Create');
    }

    /**
     * Store a newly created chamber.
     */
    public function store(Request $request): RedirectResponse
    {
        $limitCheck = \App\Services\SubscriptionLimitService::canCreateChamber(Auth::user());
        if (!$limitCheck['allowed']) {
            return redirect()->route('chambers.index')->with('error', $limitCheck['reason']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'consultation_fee' => 'nullable|numeric|min:0',
            'followup_fee' => 'nullable|numeric|min:0',
            'visiting_days' => 'nullable|array',
            'visiting_days.*' => 'string',
            'visiting_time' => 'nullable|string|max:100',
            'room_no' => 'nullable|string|max:100',
            'is_default' => 'boolean',
        ]);

        $doctorId = Auth::id();
        $isFirst = Chamber::where('doctor_id', $doctorId)->count() === 0;

        DB::transaction(function () use ($validated, $doctorId, $isFirst) {
            $isDefault = $validated['is_default'] ?? $isFirst;

            if ($isDefault) {
                Chamber::where('doctor_id', $doctorId)->update(['is_default' => false]);
            }

            $chamber = Chamber::create([
                'doctor_id' => $doctorId,
                'name' => $validated['name'],
                'branch' => $validated['branch'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'consultation_fee' => $validated['consultation_fee'] ?? 0.00,
                'followup_fee' => $validated['followup_fee'] ?? 0.00,
                'visiting_days' => $validated['visiting_days'] ?? [],
                'visiting_time' => $validated['visiting_time'] ?? null,
                'room_no' => $validated['room_no'] ?? null,
                'is_default' => $isDefault,
                'is_active' => true,
            ]);

            // Set as active in session if default or first
            if ($isDefault || !session('active_chamber_id')) {
                session(['active_chamber_id' => $chamber->id]);
            }
        });

        return redirect()->route('chambers.index')->with('success', 'Chamber created successfully!');
    }

    /**
     * Show the form for editing the specified chamber.
     */
    public function edit(Chamber $chamber): Response
    {
        if ($chamber->doctor_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('Chambers/Edit', [
            'chamber' => $chamber,
        ]);
    }

    /**
     * Update the specified chamber.
     */
    public function update(Request $request, Chamber $chamber): RedirectResponse
    {
        if ($chamber->doctor_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'consultation_fee' => 'nullable|numeric|min:0',
            'followup_fee' => 'nullable|numeric|min:0',
            'visiting_days' => 'nullable|array',
            'visiting_days.*' => 'string',
            'visiting_time' => 'nullable|string|max:100',
            'room_no' => 'nullable|string|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($chamber, $validated) {
            $doctorId = Auth::id();
            if (!empty($validated['is_default'])) {
                Chamber::where('doctor_id', $doctorId)
                    ->where('id', '!=', $chamber->id)
                    ->update(['is_default' => false]);
            }

            $chamber->update($validated);
        });

        return redirect()->route('chambers.index')->with('success', 'Chamber updated successfully!');
    }

    /**
     * Switch the active chamber for the current doctor session.
     */
    public function switchChamber(Chamber $chamber): RedirectResponse
    {
        if ($chamber->doctor_id !== Auth::id()) {
            abort(403);
        }

        session(['active_chamber_id' => $chamber->id]);

        return redirect()->back()->with('success', "Active chamber switched to {$chamber->name} ({$chamber->branch})");
    }

    /**
     * Set a chamber as the default primary chamber.
     */
    public function setDefault(Chamber $chamber): RedirectResponse
    {
        if ($chamber->doctor_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($chamber) {
            Chamber::where('doctor_id', Auth::id())->update(['is_default' => false]);
            $chamber->update(['is_default' => true]);
            session(['active_chamber_id' => $chamber->id]);
        });

        return redirect()->back()->with('success', "{$chamber->name} is now your default chamber.");
    }

    /**
     * Remove the specified chamber from storage.
     */
    public function destroy(Chamber $chamber): RedirectResponse
    {
        if ($chamber->doctor_id !== Auth::id()) {
            abort(403);
        }

        $doctorId = Auth::id();
        $chamberId = $chamber->id;

        DB::transaction(function () use ($chamber, $doctorId, $chamberId) {
            $chamber->delete();

            // If deleted chamber was active, switch to next available chamber
            if (session('active_chamber_id') == $chamberId) {
                $nextChamber = Chamber::where('doctor_id', $doctorId)->first();
                if ($nextChamber) {
                    session(['active_chamber_id' => $nextChamber->id]);
                } else {
                    session()->forget('active_chamber_id');
                }
            }
        });

        return redirect()->route('chambers.index')->with('success', 'Chamber deleted successfully!');
    }
}
