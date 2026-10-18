<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\DrugAdvice;
use App\Models\DrugDose;
use App\Models\DrugDuration;
use App\Models\DrugStrength;
use App\Models\DrugType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DrugController extends Controller
{
    /**
     * Display a listing of the drugs.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $type = $request->input('type');

        $drugsQuery = Drug::query()
            ->with('variations')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('trade_name', 'like', "%{$search}%")
                      ->orWhere('generic_name', 'like', "%{$search}%")
                      ->orWhere('note', 'like', "%{$search}%");
                });
            })
            ->when($type, function ($query, $type) {
                $query->whereHas('variations', function ($q) use ($type) {
                    $q->where('type', $type);
                });
            })
            ->latest();

        $drugs = $drugsQuery->paginate(12)->withQueryString();
        $types = DrugType::orderBy('name')->get(['id', 'name', 'short_name']);

        return Inertia::render('Drugs/Index', [
            'drugs' => $drugs,
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
            'types' => $types,
        ]);
    }

    /**
     * Show the form for creating a new drug.
     */
    public function create(): Response
    {
        return Inertia::render('Drugs/Create', [
            'lookups' => [
                'types' => DrugType::orderBy('name')->get(['id', 'name', 'short_name']),
                'doses' => DrugDose::orderBy('name')->get(['id', 'name', 'bangla_name']),
                'strengths' => DrugStrength::orderBy('name')->get(['id', 'name']),
                'durations' => DrugDuration::orderBy('name')->get(['id', 'name']),
                'advices' => DrugAdvice::orderBy('name')->get(['id', 'name', 'bangla_name']),
            ],
        ]);
    }

    /**
     * Store a newly created drug in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trade_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'warning' => 'nullable|string',
            'side_effect' => 'nullable|string',
            'additional_advice' => 'nullable|string',
            'variations' => 'required|array|min:1',
            'variations.*.type' => 'nullable|string|max:100',
            'variations.*.strength' => 'nullable|string|max:100',
            'variations.*.dose' => 'nullable|string|max:100',
            'variations.*.duration' => 'nullable|string|max:100',
            'variations.*.advice' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $drug = Drug::create([
                'user_id' => Auth::id(),
                'trade_name' => $validated['trade_name'],
                'generic_name' => $validated['generic_name'] ?? null,
                'note' => $validated['note'] ?? null,
                'warning' => $validated['warning'] ?? null,
                'side_effect' => $validated['side_effect'] ?? null,
                'additional_advice' => $validated['additional_advice'] ?? null,
                'is_active' => true,
            ]);

            foreach ($validated['variations'] as $var) {
                // Ignore completely empty rows
                if (!empty($var['type']) || !empty($var['strength']) || !empty($var['dose']) || !empty($var['duration']) || !empty($var['advice'])) {
                    $drug->variations()->create([
                        'type' => $var['type'] ?? 'Tablet',
                        'strength' => $var['strength'] ?? null,
                        'dose' => $var['dose'] ?? null,
                        'duration' => $var['duration'] ?? null,
                        'advice' => $var['advice'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('drugs.index')->with('success', 'Drug created successfully!');
    }

    /**
     * Show the form for editing the specified drug.
     */
    public function edit(Drug $drug): Response
    {
        $drug->load('variations');

        return Inertia::render('Drugs/Edit', [
            'drug' => $drug,
            'lookups' => [
                'types' => DrugType::orderBy('name')->get(['id', 'name', 'short_name']),
                'doses' => DrugDose::orderBy('name')->get(['id', 'name', 'bangla_name']),
                'strengths' => DrugStrength::orderBy('name')->get(['id', 'name']),
                'durations' => DrugDuration::orderBy('name')->get(['id', 'name']),
                'advices' => DrugAdvice::orderBy('name')->get(['id', 'name', 'bangla_name']),
            ],
        ]);
    }

    /**
     * Update the specified drug in storage.
     */
    public function update(Request $request, Drug $drug): RedirectResponse
    {
        $validated = $request->validate([
            'trade_name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'warning' => 'nullable|string',
            'side_effect' => 'nullable|string',
            'additional_advice' => 'nullable|string',
            'variations' => 'required|array|min:1',
            'variations.*.type' => 'nullable|string|max:100',
            'variations.*.strength' => 'nullable|string|max:100',
            'variations.*.dose' => 'nullable|string|max:100',
            'variations.*.duration' => 'nullable|string|max:100',
            'variations.*.advice' => 'nullable|string',
        ]);

        DB::transaction(function () use ($drug, $validated) {
            $drug->update([
                'trade_name' => $validated['trade_name'],
                'generic_name' => $validated['generic_name'] ?? null,
                'note' => $validated['note'] ?? null,
                'warning' => $validated['warning'] ?? null,
                'side_effect' => $validated['side_effect'] ?? null,
                'additional_advice' => $validated['additional_advice'] ?? null,
            ]);

            // Sync variations
            $drug->variations()->delete();

            foreach ($validated['variations'] as $var) {
                if (!empty($var['type']) || !empty($var['strength']) || !empty($var['dose']) || !empty($var['duration']) || !empty($var['advice'])) {
                    $drug->variations()->create([
                        'type' => $var['type'] ?? 'Tablet',
                        'strength' => $var['strength'] ?? null,
                        'dose' => $var['dose'] ?? null,
                        'duration' => $var['duration'] ?? null,
                        'advice' => $var['advice'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('drugs.index')->with('success', 'Drug updated successfully!');
    }

    /**
     * Remove the specified drug from storage.
     */
    public function destroy(Drug $drug): RedirectResponse
    {
        $drug->delete();

        return redirect()->route('drugs.index')->with('success', 'Drug deleted successfully!');
    }
}
