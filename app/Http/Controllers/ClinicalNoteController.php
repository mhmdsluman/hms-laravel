<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClinicalNote;
use App\Models\OrderSet;
use App\Models\Service;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClinicalNoteController extends Controller
{
    /**
     * Show the consultation form for an appointment.
     *
     * Loads patient vitals, any existing clinical note (and its data),
     * plus provisional diagnosis code when available. Also loads active
     * order sets (template-based order bundles) so clinicians can quickly
     * apply common orders and marks already ordered services.
     */
    public function create(Appointment $appointment): Response
    {
        // Eager load required relationships
        $appointment->load([
            'patient.vitals',
            'clinicalNote.data',
            'clinicalNote.provisionalDiagnosisCode',
            'orders.items',
        ]);

        // Get first active Clinical Note template
        $template = Template::where('type', 'Clinical Note')
            ->where('is_active', true)
            ->with('fields')
            ->first();

        // Active services grouped by department
        $services = Service::where('is_active', true)
            ->orderBy('department')
            ->get();

        // Active order sets with their items
        $orderSets = OrderSet::where('is_active', true)
            ->with('items')
            ->get();

        // List of service IDs already ordered for this appointment
        $orderedServiceIds = $appointment->orders
            ->flatMap(fn($order) => $order->items->pluck('service_id'))
            ->unique()
            ->values()
            ->all();

        return Inertia::render('Appointments/Consultation', [
            'appointment'        => $appointment,
            'services'           => $services->groupBy('department'),
            'template'           => $template,
            'orderSets'          => $orderSets,
            'orderedServiceIds'  => $orderedServiceIds,
        ]);
    }

    /**
     * Store or update clinical notes for an appointment.
     *
     * Validates template selection, field values and optional provisional diagnosis code.
     * Saves note and its data inside a DB transaction and marks the appointment as Completed.
     */
    public function store(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'template_id'                    => 'required|exists:templates,id',
            'fields'                         => 'required|array',
            'fields.*'                       => 'nullable|string',
            'provisional_diagnosis_code_id'  => 'nullable|exists:diagnosis_codes,id',
        ]);

        DB::transaction(function () use ($validated, $appointment) {
            // Create or update the clinical note
            $note = ClinicalNote::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'patient_id'                     => $appointment->patient_id,
                    'clinician_id'                   => Auth::id(),
                    'template_id'                    => $validated['template_id'],
                    'provisional_diagnosis_code_id'  => $validated['provisional_diagnosis_code_id'] ?? null,
                ]
            );

            // Remove old data
            $note->data()->delete();

            // Save only non-empty values (but keep values like "0")
            foreach ($validated['fields'] as $fieldId => $value) {
                if ($value !== null && $value !== '') {
                    $note->data()->create([
                        'template_field_id' => $fieldId,
                        'value'             => $value,
                    ]);
                }
            }

            // Mark appointment as completed
            $appointment->update(['status' => 'Completed']);
        });

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Clinical notes saved and appointment completed.');
    }
}
