<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QcResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'qc_lot_id',
        'result',
        'in_range',
        'user_id',
    ];

    public function qcLot(): BelongsTo
    {
        return $this->belongsTo(QcLot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
