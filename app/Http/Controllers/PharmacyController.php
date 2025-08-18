<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyController extends Controller
{
    /**
     * Display the pharmacy dashboard with pending medication orders.
     */
    public function index(): Response
    {
        $pendingPharmacyOrders = OrderItem::where('status', 'Pending')
            ->whereHas('service', function ($query) {
                $query->where('department', 'Pharmacy');
            })
            ->with(['order.patient', 'service']) // Eager load relationships
            ->latest()
            ->get();

        return Inertia::render('Pharmacy/Index', [
            'pendingOrders' => $pendingPharmacyOrders,
        ]);
    }
}
