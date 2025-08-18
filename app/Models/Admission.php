<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admission extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'appointment_id', 'bed_id', 'admitting_doctor_id',
        'admission_time', 'discharge_time', 'status', 'reason_for_admission',
    ];

    protected $casts = [
        'admission_time' => 'datetime',
        'discharge_time' => 'datetime',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function bed(): BelongsTo { return $this->belongsTo(Bed::class); }
    public function admittingDoctor(): BelongsTo { return $this->belongsTo(User::class, 'admitting_doctor_id'); }
    public function medicationAdministrations(): HasMany { return $this->hasMany(MedicationAdministration::class)->orderBy('scheduled_time'); }
    public function nursingNotes(): HasMany { return $this->hasMany(NursingNote::class)->latest(); }
    public function carePlan(): HasOne { return $this->hasOne(CarePlan::class); }
    public function medicationOrders(): HasManyThrough {
        return $this->hasManyThrough(OrderItem::class, Order::class, 'patient_id', 'order_id', 'patient_id', 'id')
            ->whereHas('service', fn ($q) => $q->where('department', 'Pharmacy'));
    }

    /**
     * Get the shift handovers for this admission.
     */
    public function shiftHandovers(): HasMany
    {
        return $this->hasMany(ShiftHandover::class)->latest();
    }
}
