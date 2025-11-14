<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyDispensation extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'inventory_id',
        'quantity_dispensed',
        'dispensed_by_user_id',
        'verified_by_user_id', // <-- Add this
        'verified_at',         // <-- Add this
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function dispenser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by_user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
