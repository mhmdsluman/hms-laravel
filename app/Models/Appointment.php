<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'clinician_id', 'appointment_time', 'duration_minutes',
        'status', 'reason_for_visit', 'notes', 'created_by_user_id',
    ];

    protected $casts = [ 'appointment_time' => 'datetime' ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function clinician(): BelongsTo { return $this->belongsTo(User::class, 'clinician_id'); }
    public function clinicalNote(): HasOne { return $this->hasOne(ClinicalNote::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }

    /**
     * Get the bill associated with the appointment.
     */
    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }
}
