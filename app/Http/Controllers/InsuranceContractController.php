<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InsuranceContractController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InsuranceProvider $provider): Response
    {
        // Load existing contracts and their associated services
        $provider->load('contracts.service');

        return Inertia::render('Admin/InsuranceContracts/Builder', [
            'provider' => $provider,
            'services' => Service::orderBy('department')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InsuranceProvider $provider)
    {
        $validated = $request->validate([
            'contracts' => 'required|array',
            'contracts.*.service_id' => 'required|exists:services,id',
            'contracts.*.coverage_percentage' => 'required|numeric|min:0|max:100',
            'contracts.*.co_pay_amount' => 'nullable|numeric|min:0',
            'contracts.*.requires_pre_authorization' => 'required|boolean',
        ]);

        DB::transaction(function () use ($validated, $provider) {
            // Delete old contract rules and create the new ones
            $provider->contracts()->delete();
            $provider->contracts()->createMany($validated['contracts']);
        });

        return redirect()->route('insurance-providers.index')->with('success', 'Insurance contract updated successfully.');
    }
}
