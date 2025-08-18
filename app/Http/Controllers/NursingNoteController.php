<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\NursingNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NursingNoteController extends Controller
{
    /**
     * Show the form for creating a new nursing note.
     */
    public function create(Admission $admission): Response
    {
        $admission->load('patient');

        return Inertia::render('IPD/CreateNursingNote', [
            'admission' => $admission,
        ]);
    }

    /**
     * Store a newly created nursing note in storage.
     */
    public function store(Request $request, Admission $admission)
    {
        $validated = $request->validate([
            'note' => 'required|string',
        ]);

        NursingNote::create([
            'admission_id' => $admission->id,
            'nurse_id' => Auth::id(),
            'note' => $validated['note'],
        ]);

        return redirect()->route('nursing.index')->with('success', 'Nursing note added successfully.');
    }
}
