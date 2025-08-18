<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'service_id', 'status', 'placer_order_number'];

    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function labResult(): HasOne { return $this->hasOne(LabResult::class); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function radiologyReport(): HasOne { return $this->hasOne(RadiologyReport::class); }
    public function radiologySchedule(): HasOne { return $this->hasOne(RadiologySchedule::class); }
    public function otSchedule(): HasOne { return $this->hasOne(OperatingTheaterSchedule::class); }

    /**
     * Get the operative note for the order item.
     */
    public function operativeNote(): HasOne
    {
        return $this->hasOne(OperativeNote::class);
    }
}
