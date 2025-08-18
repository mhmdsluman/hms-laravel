<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\ShiftHandover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ShiftHandoverController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Admission $admission): Response
    {
        $admission->load('patient');

        return Inertia::render('IPD/CreateShiftHandover', [
            'admission' => $admission,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Admission $admission)
    {
        $validated = $request->validate([
            'summary' => 'required|string',
        ]);

        ShiftHandover::create([
            'admission_id' => $admission->id,
            'outgoing_nurse_id' => Auth::id(),
            'summary' => $validated['summary'],
        ]);

        return redirect()->route('nursing.index')->with('success', 'Shift handover note saved successfully.');
    }
}
