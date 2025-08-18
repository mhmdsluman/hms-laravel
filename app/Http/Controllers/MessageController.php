<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $patient = Patient::where('email', Auth::user()->email)->firstOrFail();

        $conversations = $patient->conversations()->with('clinician')->get();

        return Inertia::render('Portal/Messages/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Conversation $conversation): Response
    {
        // Add authorization check here in a real app to ensure patient owns conversation
        $conversation->load(['messages.sender', 'patient', 'clinician']);

        return Inertia::render('Portal/Messages/Show', [
            'conversation' => $conversation,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $validated['body'],
        ]);

        return redirect()->route('portal.messages.show', $conversation->id);
    }
}
