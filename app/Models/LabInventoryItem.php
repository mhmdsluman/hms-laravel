<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabInventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'item_type',
        'quantity_in_stock',
        'reorder_level',
        'supplier',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(LabStockTransaction::class);
    }
}
