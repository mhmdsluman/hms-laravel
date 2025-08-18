<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Bill;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index(): Response
    {
        // KPI 1: Total Patients
        $totalPatients = Patient::count();

        // KPI 2: Bed Occupancy Rate
        $totalBeds = Bed::count();
        $occupiedBeds = Bed::where('status', 'Occupied')->count();
        $bedOccupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 2) : 0;

        // KPI 3: Total Revenue This Month
        $totalRevenueThisMonth = Bill::where('status', 'Paid')
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('total_amount');

        // KPI 4: Average Length of Stay (in days)
        $avgLengthOfStay = Admission::where('status', 'Discharged')
            ->whereNotNull('discharge_time')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, admission_time, discharge_time)) as avg_stay_seconds')
            ->first()
            ->avg_stay_seconds;

        $avgLengthOfStayDays = $avgLengthOfStay ? round($avgLengthOfStay / (60 * 60 * 24), 1) : 0;

        return Inertia::render('Admin/Analytics/Index', [
            'stats' => [
                'total_patients' => $totalPatients,
                'bed_occupancy_rate' => $bedOccupancyRate,
                'total_revenue_this_month' => number_format($totalRevenueThisMonth, 2),
                'avg_length_of_stay_days' => $avgLengthOfStayDays,
            ],
        ]);
    }
}
