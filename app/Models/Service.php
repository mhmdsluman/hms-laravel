<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department',
        'specimen_type',
        'units',
        'formulary_status',
        'is_controlled_substance',
        'price',
        'is_active',
    ];
    protected $casts = [
        'is_controlled_substance' => 'boolean',
        'price' => 'decimal:2',
    ];

    /**
     * Get the reference ranges for the service (lab test).
     */
    public function referenceRanges(): HasMany
    {
        return $this->hasMany(ReferenceRange::class);
    }
}
