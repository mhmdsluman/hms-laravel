<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnesthesiaRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'operative_note_id',
        'anesthetist_id',
        'anesthesia_type',
        'vitals_log',
        'medications_log',
        'fluids_log',
        'notes',
    ];

    protected $casts = [
        'vitals_log' => 'array',
        'medications_log' => 'array',
        'fluids_log' => 'array',
    ];

    public function operativeNote(): BelongsTo
    {
        return $this->belongsTo(OperativeNote::class);
    }

    public function anesthetist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anesthetist_id');
    }
}
