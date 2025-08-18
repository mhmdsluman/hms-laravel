<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TestCatalogueController extends Controller
{
    private function getLabFormData(): array
    {
        return [
            'specimenTypes' => [
                'Blood', 'Serum', 'Plasma', 'Urine', 'CSF', 'Swab', 'Tissue', 'Stool',
            ],
            'units' => [
                'mg/dL', 'g/dL', 'mmol/L', 'U/L', 'cells/mcL', '10^3/uL', '10^6/uL', '%', 'pg', 'fL',
            ],
        ];
    }

    public function index(): Response
    {
        return Inertia::render('Admin/TestCatalogue/Index', [
            'labTests' => Service::where('department', 'Laboratory')->latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/TestCatalogue/Builder', $this->getLabFormData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specimen_type' => 'required|string|max:255',
            'units' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'reference_ranges' => 'required|array|min:1',
            'reference_ranges.*.gender' => 'required|string|in:Male,Female,All',
            'reference_ranges.*.age_min' => 'required|integer|min:0',
            'reference_ranges.*.age_max' => 'required|integer|min:0',
            'reference_ranges.*.range_low' => 'nullable|numeric',
            'reference_ranges.*.range_high' => 'nullable|numeric',
            'reference_ranges.*.critical_low' => 'nullable|numeric',
            'reference_ranges.*.critical_high' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($validated) {
            $service = Service::create([
                'name' => $validated['name'],
                'department' => 'Laboratory',
                'specimen_type' => $validated['specimen_type'],
                'units' => $validated['units'],
                'price' => $validated['price'],
            ]);

            $service->referenceRanges()->createMany($validated['reference_ranges']);
        });

        return redirect()->route('test-catalogue.index')->with('success', 'Lab test created successfully.');
    }

    public function edit(Service $service): Response
    {
        $service->load('referenceRanges');

        return Inertia::render('Admin/TestCatalogue/Builder', array_merge($this->getLabFormData(), [
            'labTest' => $service,
        ]));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specimen_type' => 'required|string|max:255',
            'units' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:0',
            'reference_ranges' => 'required|array|min:1',
            'reference_ranges.*.gender' => 'required|string|in:Male,Female,All',
            'reference_ranges.*.age_min' => 'required|integer|min:0',
            'reference_ranges.*.age_max' => 'required|integer|min:0',
            'reference_ranges.*.range_low' => 'nullable|numeric',
            'reference_ranges.*.range_high' => 'nullable|numeric',
            'reference_ranges.*.critical_low' => 'nullable|numeric',
            'reference_ranges.*.critical_high' => 'nullable|numeric',
        ]);

        DB::transaction(function () use ($validated, $service) {
            $service->update([
                'name' => $validated['name'],
                'specimen_type' => $validated['specimen_type'],
                'units' => $validated['units'],
                'price' => $validated['price'],
            ]);

            $service->referenceRanges()->delete();
            $service->referenceRanges()->createMany($validated['reference_ranges']);
        });

        return redirect()->route('test-catalogue.index')->with('success', 'Lab test updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('test-catalogue.index')->with('success', 'Lab test deleted successfully.');
    }
}
