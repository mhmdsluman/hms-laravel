<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FormularyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Formulary/Index', [
            'medications' => Service::where('department', 'Pharmacy')->latest()->paginate(10),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service): Response
    {
        return Inertia::render('Admin/Formulary/Edit', [
            'medication' => $service,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'formulary_status' => 'required|string|in:Formulary,Non-Formulary,Restricted',
            'is_controlled_substance' => 'required|boolean',
        ]);

        $service->update($validated);

        return redirect()->route('formulary.index')->with('success', 'Formulary status updated successfully.');
    }
}
