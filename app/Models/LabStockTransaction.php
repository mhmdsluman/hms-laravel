<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabStockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'transaction_type',
        'quantity',
        'user_id',
        'notes',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
