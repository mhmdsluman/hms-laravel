<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\OrderItem;
use App\Models\PharmacyDispensation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyDispensationController extends Controller
{
    public function create(OrderItem $orderItem): Response
    {
        $orderItem->load(['order.patient', 'service']);

        $inventory = Inventory::where('service_id', $orderItem->service_id)
            ->where('quantity', '>', 0)
            ->get();

        return Inertia::render('Pharmacy/Dispense', [
            'orderItem' => $orderItem,
            'inventory' => $inventory,
        ]);
    }

    public function store(Request $request, OrderItem $orderItem)
    {
        $orderItem->load('service');
        $isControlled = $orderItem->service->is_controlled_substance;

        $validationRules = [
            'inventory_id' => 'required|exists:inventory,id',
            'quantity_dispensed' => 'required|integer|min:1',
        ];

        if ($isControlled) {
            $validationRules['verifier_email'] = 'required|email|exists:users,email';
            $validationRules['verifier_password'] = 'required|string';
        }

        $validated = $request->validate($validationRules);

        $inventory = Inventory::findOrFail($validated['inventory_id']);

        if ($inventory->quantity < $validated['quantity_dispensed']) {
            return back()->withErrors(['quantity_dispensed' => 'Not enough stock available.']);
        }

        $verifierId = null;
        if ($isControlled) {
            $verifier = User::where('email', $validated['verifier_email'])->first();

            if (!$verifier || !Hash::check($validated['verifier_password'], $verifier->password)) {
                throw ValidationException::withMessages(['verifier_password' => 'The provided verifier credentials are incorrect.']);
            }
            if ($verifier->id === Auth::id()) {
                throw ValidationException::withMessages(['verifier_email' => 'The verifier cannot be the same as the dispenser.']);
            }
            $verifierId = $verifier->id;
        }

        DB::transaction(function () use ($validated, $orderItem, $inventory, $verifierId) {
            PharmacyDispensation::create([
                'order_item_id' => $orderItem->id,
                'inventory_id' => $validated['inventory_id'],
                'quantity_dispensed' => $validated['quantity_dispensed'],
                'dispensed_by_user_id' => Auth::id(),
                'verified_by_user_id' => $verifierId,
                'verified_at' => $verifierId ? now() : null,
            ]);

            $inventory->decrement('quantity', $validated['quantity_dispensed']);
            $orderItem->update(['status' => 'Completed']);
        });

        return redirect()->route('pharmacy.index')->with('success', 'Medication dispensed successfully.');
    }
}
