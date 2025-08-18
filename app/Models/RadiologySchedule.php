<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'scheduled_time',
        'room',
        'machine',
        'technologist_id',
        'preparation_instructions',
    ];

    protected $casts = [
        'scheduled_time' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function technologist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technologist_id');
    }
}
