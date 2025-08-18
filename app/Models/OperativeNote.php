<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OperativeNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'surgeon_id',
        'preoperative_diagnosis',
        'postoperative_diagnosis',
        'procedure_description',
        'findings',
        'procedure_start_time',
        'procedure_end_time',
    ];

    protected $casts = [
        'procedure_start_time' => 'datetime',
        'procedure_end_time' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surgeon_id');
    }

    /**
     * Get the anesthesia record for the operative note.
     */
    public function anesthesiaRecord(): HasOne
    {
        return $this->hasOne(AnesthesiaRecord::class);
    }
}
