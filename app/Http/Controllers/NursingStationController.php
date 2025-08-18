<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NursingStationController extends Controller
{
    /**
     * Display the nursing station dashboard.
     */
    public function index(): Response
    {
        // Fetch all current admissions and eager load patient and bed details
        $admissions = Admission::where('status', 'Admitted')
            ->with(['patient', 'bed'])
            ->latest('admission_time')
            ->get();

        return Inertia::render('IPD/NursingStation', [
            'admissions' => $admissions,
        ]);
    }
}
