<?php

namespace App\Listeners;

use App\Events\AppointmentCompleted;
use App\Models\Bill;
use Illuminate\Contracts\Queue\ShouldQueue; // remove if you don't want to queue
use Illuminate\Queue\InteractsWithQueue;

class GenerateBillForAppointment // implements ShouldQueue (optional)
{
    // use InteractsWithQueue; // uncomment only if implementing queueing

    /**
     * Handle the event.
     *
     * @param  \App\Events\AppointmentCompleted  $event
     * @return void
     */
    public function handle(AppointmentCompleted $event): void
    {
        $appointment = $event->appointment;

        if (! $appointment || ! $appointment->id) {
            return;
        }

        // Avoid creating duplicate bills for the same appointment
        Bill::firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'patient_id' => $appointment->patient_id ?? null,
                // Use an appropriate amount field from your appointment/service model.
                // This is a placeholder — replace with your pricing logic:
                'amount' => $appointment->fee ?? 0,
                'description' => 'Auto-generated bill for appointment #' . $appointment->id,
                'status' => 'unpaid', // adjust to your schema
                'created_by_user_id' => $appointment->clinician_id ?? null,
            ]
        );
    }
}
