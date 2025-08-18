<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BedController extends Controller
{
    /**
     * Display the bed management dashboard.
     */
    public function index(): Response
    {
        // Eager load the current admission and the patient's details for occupied beds
        $beds = Bed::with(['currentAdmission.patient'])
            ->orderBy('ward')
            ->orderBy('bed_number')
            ->get();

        return Inertia::render('IPD/Index', [
            'bedsByWard' => $beds->groupBy('ward'),
        ]);
    }
}
