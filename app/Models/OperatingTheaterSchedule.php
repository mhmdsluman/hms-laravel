<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperatingTheaterSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'scheduled_start_time',
        'scheduled_end_time',
        'theater_room',
        'surgeon_id',
        'anesthetist_id',
        'scrub_nurse_id',
        'notes',
    ];

    protected $casts = [
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surgeon_id');
    }

    public function anesthetist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anesthetist_id');
    }

    public function scrubNurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scrub_nurse_id');
    }
}
