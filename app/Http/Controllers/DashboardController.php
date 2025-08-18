<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\OrderItem;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the main application dashboard.
     */
    public function __invoke(): Response|\Illuminate\Http\RedirectResponse
    {
        // **MODIFIED**: Add a security check here
        if (Auth::user()->role === 'patient') {
            return redirect()->route('portal.dashboard');
        }

        // Fetch stats for the dashboard cards
        $stats = [
            'total_patients' => Patient::count(),
            'upcoming_appointments' => Appointment::where('appointment_time', '>=', now())->count(),
            'pending_lab_orders' => OrderItem::where('status', 'Pending')
                ->whereHas('service', fn ($q) => $q->where('department', 'Laboratory'))
                ->count(),
            'pending_pharmacy_orders' => OrderItem::where('status', 'Pending')
                ->whereHas('service', fn ($q) => $q->where('department', 'Pharmacy'))
                ->count(),
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    }
}
