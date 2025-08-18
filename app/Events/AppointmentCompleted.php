<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;

class AppointmentCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The completed appointment instance.
     *
     * @var \App\Models\Appointment
     */
    public Appointment $appointment;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return void
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }
}
