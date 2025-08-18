<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabStockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_inventory_item_id',
        'transaction_type',
        'quantity',
        'user_id',
        'notes',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(LabInventoryItem::class, 'lab_inventory_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
