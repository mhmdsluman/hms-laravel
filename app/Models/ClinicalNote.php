<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicalNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'clinician_id',
        'template_id',
        'notes_content',
        'provisional_diagnosis',
        'final_diagnosis',
        'provisional_diagnosis_code_id', // <-- Add this
        'final_diagnosis_code_id',     // <-- Add this
    ];

    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function clinician(): BelongsTo { return $this->belongsTo(User::class, 'clinician_id'); }
    public function template(): BelongsTo { return $this->belongsTo(Template::class); }
    public function data(): HasMany { return $this->hasMany(ClinicalNoteData::class); }

    /**
     * Get the provisional diagnosis code.
     */
    public function provisionalDiagnosisCode(): BelongsTo
    {
        return $this->belongsTo(DiagnosisCode::class, 'provisional_diagnosis_code_id');
    }

    /**
     * Get the final diagnosis code.
     */
    public function finalDiagnosisCode(): BelongsTo
    {
        return $this->belongsTo(DiagnosisCode::class, 'final_diagnosis_code_id');
    }
}
