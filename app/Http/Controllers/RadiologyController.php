<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RadiologyController extends Controller
{
    /**
     * Display the radiology dashboard with imaging orders.
     */
    public function index(): Response
    {
        $baseQuery = OrderItem::whereHas('service', function ($query) {
            $query->where('department', 'Radiology');
        })->with(['order.patient', 'service']);

        $pendingOrders = (clone $baseQuery)->where('status', 'Pending')->latest()->get();
        $scheduledOrders = (clone $baseQuery)->where('status', 'Scheduled')->latest()->get();
        $completedOrders = (clone $baseQuery)->where('status', 'Completed')->latest()->limit(10)->get();


        return Inertia::render('Radiology/Index', [
            'pendingOrders' => $pendingOrders,
            'scheduledOrders' => $scheduledOrders,
            'completedOrders' => $completedOrders,
        ]);
    }
}
