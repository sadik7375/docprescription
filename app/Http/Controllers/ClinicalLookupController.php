<?php

namespace App\Http\Controllers;

use App\Models\ChiefComplaint;
use App\Models\ClinicalAdvice;
use App\Models\Diagnosis;
use App\Models\Investigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ClinicalLookupController extends Controller
{
    /**
     * Display a listing of clinical lookups.
     */
    public function index(Request $request, string $type = 'complaints'): Response
    {
        $validTypes = ['complaints', 'diagnoses', 'investigations', 'advices'];
        if (!in_array($type, $validTypes)) {
            $type = 'complaints';
        }

        $search = $request->input('search');

        $query = match ($type) {
            'complaints' => ChiefComplaint::query(),
            'diagnoses' => Diagnosis::query(),
            'investigations' => Investigation::query(),
            'advices' => ClinicalAdvice::query(),
        };

        if ($search) {
            $query->where(function ($q) use ($search, $type) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");

                if ($type === 'diagnoses') {
                    $q->orWhere('icd10_code', 'like', "%{$search}%");
                }
                if ($type === 'investigations' || $type === 'advices') {
                    $q->orWhere('category', 'like', "%{$search}%");
                }
            });
        }

        $items = $query->orderBy('name')->paginate(15)->withQueryString();

        $counts = [
            'complaints' => ChiefComplaint::count(),
            'diagnoses' => Diagnosis::count(),
            'investigations' => Investigation::count(),
            'advices' => ClinicalAdvice::count(),
        ];

        return Inertia::render('Prescriptions/Lookups/Index', [
            'type' => $type,
            'items' => $items,
            'counts' => $counts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created lookup item.
     */
    public function store(Request $request, string $type): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icd10_code' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
        ]);

        $data = [
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ];

        match ($type) {
            'complaints' => ChiefComplaint::create($data),
            'diagnoses' => Diagnosis::create(array_merge($data, ['icd10_code' => $validated['icd10_code'] ?? null])),
            'investigations' => Investigation::create(array_merge($data, ['category' => $validated['category'] ?? null])),
            'advices' => ClinicalAdvice::create(array_merge($data, ['category' => $validated['category'] ?? null])),
            default => null,
        };

        return redirect()->back()->with('success', 'Clinical lookup item added successfully!');
    }

    /**
     * Remove the specified lookup item.
     */
    public function destroy(string $type, int $id): RedirectResponse
    {
        match ($type) {
            'complaints' => ChiefComplaint::findOrFail($id)->delete(),
            'diagnoses' => Diagnosis::findOrFail($id)->delete(),
            'investigations' => Investigation::findOrFail($id)->delete(),
            'advices' => ClinicalAdvice::findOrFail($id)->delete(),
            default => null,
        };

        return redirect()->back()->with('success', 'Item deleted successfully!');
    }
}
