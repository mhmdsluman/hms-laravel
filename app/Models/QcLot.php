<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QcLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_number',
        'lab_test_id',
        'mean',
        'std_dev',
        'expiry_date',
    ];

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(QcResult::class);
    }
}
