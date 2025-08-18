<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'arrival_time',
        'chief_complaint',
        'triage_level',
        'status',
        'registered_by_user_id',
    ];

    protected $casts = [
        'arrival_time' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function registrar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }
}
