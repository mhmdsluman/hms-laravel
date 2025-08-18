<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsuranceProviderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/InsuranceProviders/Index', [
            'providers' => InsuranceProvider::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/InsuranceProviders/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:insurance_providers,name',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        InsuranceProvider::create($validated);

        return redirect()->route('insurance-providers.index')->with('success', 'Insurance provider created successfully.');
    }

    public function edit(InsuranceProvider $insuranceProvider): Response
    {
        return Inertia::render('Admin/InsuranceProviders/Edit', [
            'provider' => $insuranceProvider,
        ]);
    }

    public function update(Request $request, InsuranceProvider $insuranceProvider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:insurance_providers,name,' . $insuranceProvider->id,
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $insuranceProvider->update($validated);

        return redirect()->route('insurance-providers.index')->with('success', 'Insurance provider updated successfully.');
    }

    public function destroy(InsuranceProvider $insuranceProvider)
    {
        $insuranceProvider->delete();
        return redirect()->route('insurance-providers.index')->with('success', 'Insurance provider deleted successfully.');
    }
}
