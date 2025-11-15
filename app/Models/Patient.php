<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Authenticatable
{
    use HasFactory, SoftDeletes;

    /**
     * Get the name of the password attribute for the patient.
     *
     * @return string
     */
    public function getAuthPasswordName()
    {
        return 'patient_portal_password_hash';
    }

    protected $fillable = [
        'uhid', 'mrn', 'external_ids', 'first_name', 'middle_name', 'last_name',
        'preferred_name', 'gender', 'sex_at_birth', 'pronouns', 'date_of_birth',
        'marital_status', 'blood_group', 'nationality', 'occupation', 'primary_phone',
        'primary_phone_country_code', 'secondary_phone', 'email', 'preferred_contact_method',
        'addresses', 'next_of_kin', 'allergies', 'past_medical_history',
        'surgical_history', 'family_history', 'immunization_history', 'consent_flags',
        'legal_flags',
        'photo_capture_path', // <-- ALREADY EXISTS, ENSURE IT'S HERE
        'created_by_user_id', 'updated_by_user_id', 'patient_portal_password_hash',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d', // Format the date casting
        'external_ids' => 'array',
        'addresses' => 'array',
        'next_of_kin' => 'array',
        'allergies' => 'array',
        'past_medical_history' => 'array',
        'surgical_history' => 'array',
        'family_history' => 'array',
        'immunization_history' => 'array',
        'consent_flags' => 'array',
        'legal_flags' => 'array',
        'patient_portal_password_hash' => 'hashed',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['age', 'photo_url']; // <-- ADDED 'photo_url'

    // ... existing relationships ...
    public function vitals(): HasMany { return $this->hasMany(Vital::class)->latest(); }
    public function orders(): HasMany { return $this->hasMany(Order::class)->latest(); }
    public function appointments(): HasMany { return $this->hasMany(Appointment::class); }
    public function bills(): HasMany { return $this->hasMany(Bill::class); }
    public function currentAdmission(): HasOne { return $this->hasOne(Admission::class)->where('status', 'Admitted'); }
    public function admissions(): HasMany { return $this->hasMany(Admission::class)->latest(); }
    public function conversations(): HasMany { return $this->hasMany(Conversation::class); }
    public function emergencyVisits(): HasMany { return $this->hasMany(EmergencyVisit::class)->latest(); }
    public function insurancePolicies(): HasMany { return $this->hasMany(InsurancePolicy::class); }
    public function labOrders(): HasMany { return $this->hasMany(LabOrder::class); }


    /**
     * Get the patient's current age in a human-readable format (years, months, or days).
     */
    public function getAgeAttribute(): ?string
    {
        $dob = $this->getAttribute('date_of_birth');

        if (empty($dob)) {
            return null;
        }

        $birthDate = Carbon::parse($dob);
        $now = Carbon::now();

        if ($birthDate->diffInYears($now) >= 1) {
            $years = $birthDate->diffInYears($now);
            return $years . ($years > 1 ? ' years' : ' year');
        }

        if ($birthDate->diffInMonths($now) >= 1) {
            $months = $birthDate->diffInMonths($now);
            return $months . ($months > 1 ? ' months' : ' month');
        }

        $days = $birthDate->diffInDays($now);
        return $days . ($days > 1 ? ' days' : ' day');
    }

    /**
     * Get the patient's photo URL.
     *
     * @return string
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_capture_path) {
            return asset('storage/' . $this->photo_capture_path);
        }

        // Default avatar
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->first_name . ' ' . $this->last_name) . '&color=7F9CF5&background=EBF4FF';
    }
}
