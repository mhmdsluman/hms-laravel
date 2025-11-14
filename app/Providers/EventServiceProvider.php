<?php

namespace App\Providers;

use App\Events\AppointmentCompleted;
use App\Listeners\GenerateBillForAppointment;
use App\Models\Admission;
use App\Models\Appointment;
use App\Models\Bill;
use App\Models\ClinicalNote;
use App\Models\LabResult;
use App\Models\MedicationAdministration;
use App\Models\NursingNote;
use App\Models\Order;
use App\Models\Patient;
use App\Models\PharmacyDispensation;
use App\Models\RadiologyReport;
use App\Models\Service;
use App\Models\User;
use App\Models\Vital;
use App\Observers\AuditObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        AppointmentCompleted::class => [
            GenerateBillForAppointment::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Patient::observe(AuditObserver::class);
        Appointment::observe(AuditObserver::class);
        Admission::observe(AuditObserver::class);
        Order::observe(AuditObserver::class);
        Vital::observe(AuditObserver::class);
        ClinicalNote::observe(AuditObserver::class);
        LabResult::observe(AuditObserver::class);
        RadiologyReport::observe(AuditObserver::class);
        PharmacyDispensation::observe(AuditObserver::class);
        Bill::observe(AuditObserver::class);
        NursingNote::observe(AuditObserver::class);
        MedicationAdministration::observe(AuditObserver::class);
        Service::observe(AuditObserver::class);
        User::observe(AuditObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
