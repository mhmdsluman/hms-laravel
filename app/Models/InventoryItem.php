<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'quantity_in_stock',
        'batch_number',
        'expiry_date',
        'location',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
