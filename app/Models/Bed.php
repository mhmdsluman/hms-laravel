<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'bed_number',
        'ward',
        'room_number',
        'status',
    ];

    /**
     * Get the current admission for the bed.
     */
    public function currentAdmission(): HasOne
    {
        return $this->hasOne(Admission::class)->where('status', 'Admitted');
    }
}
