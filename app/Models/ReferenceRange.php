<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'gender',
        'age_min',
        'age_max',
        'range_low',
        'range_high',
        'critical_low',
        'critical_high',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
