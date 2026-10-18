<?php

namespace App\Http\Controllers;

use App\Models\DrugAdvice;
use App\Models\DrugDose;
use App\Models\DrugDuration;
use App\Models\DrugStrength;
use App\Models\DrugType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DrugLookupController extends Controller
{
    /**
     * Display a listing of lookups for a specific section (doses, advice, strength, type, duration).
     */
    public function index(Request $request, string $type = 'types'): Response
    {
        $validTypes = ['doses', 'advices', 'strengths', 'types', 'durations'];
        if (!in_array($type, $validTypes)) {
            $type = 'types';
        }

        $data = match ($type) {
            'doses' => DrugDose::latest()->paginate(15),
            'advices' => DrugAdvice::latest()->paginate(15),
            'strengths' => DrugStrength::latest()->paginate(15),
            'types' => DrugType::latest()->paginate(15),
            'durations' => DrugDuration::latest()->paginate(15),
        };

        return Inertia::render('Drugs/Lookups/Index', [
            'activeSection' => $type,
            'items' => $data,
        ]);
    }

    /**
     * Store a newly created lookup item.
     */
    public function store(Request $request, string $type): RedirectResponse
    {
        match ($type) {
            'doses' => DrugDose::create($request->validate([
                'name' => 'required|string|max:100',
                'bangla_name' => 'nullable|string|max:255',
            ])),
            'advices' => DrugAdvice::create($request->validate([
                'name' => 'required|string',
            ])),
            'strengths' => DrugStrength::create($request->validate([
                'name' => 'required|string|max:100',
            ])),
            'types' => DrugType::create($request->validate([
                'name' => 'required|string|max:100',
                'short_name' => 'nullable|string|max:50',
            ])),
            'durations' => DrugDuration::create($request->validate([
                'name' => 'required|string|max:100',
            ])),
            default => null,
        };

        return redirect()->back()->with('success', 'Item created successfully!');
    }

    /**
     * Delete a lookup item.
     */
    public function destroy(Request $request, string $type, int $id): RedirectResponse
    {
        match ($type) {
            'doses' => DrugDose::destroy($id),
            'advices' => DrugAdvice::destroy($id),
            'strengths' => DrugStrength::destroy($id),
            'types' => DrugType::destroy($id),
            'durations' => DrugDuration::destroy($id),
            default => null,
        };

        return redirect()->back()->with('success', 'Item deleted successfully!');
    }
}
