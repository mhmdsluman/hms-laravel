<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'appointment_id',
        'total_amount',
        'insurance_amount',
        'patient_co_pay',
        'discount_amount',
        'discount_reason',
        'paid_amount',
        'status',
    ];

    // sensible defaults so arithmetic doesn't hit nulls
    protected $attributes = [
        'total_amount'     => 0.0,
        'insurance_amount' => 0.0,
        'patient_co_pay'   => 0.0,
        'discount_amount'  => 0.0,
        'paid_amount'      => 0.0,
        'status'           => 'Unpaid',
    ];

    protected $casts = [
        'total_amount'     => 'float',
        'insurance_amount' => 'float',
        'patient_co_pay'   => 'float',
        'discount_amount'  => 'float',
        'paid_amount'      => 'float',
    ];

    protected $appends = ['balance_due'];

    /**
     * Relations (use string names to avoid fatal errors if a class is missing
     * or autoload needs refresh)
     */
    public function patient()
    {
        return $this->belongsTo(\App\Models\Patient::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Models\BillItem::class);
    }

    public function appointment()
    {
        return $this->belongsTo(\App\Models\Appointment::class);
    }

    /**
     * Balance accessor (defensive)
     */
    public function getBalanceDueAttribute()
    {
        $patientCoPay = (float) ($this->patient_co_pay ?? 0);
        $discount = (float) ($this->discount_amount ?? 0);
        $paid = (float) ($this->paid_amount ?? 0);

        $due = $patientCoPay - $discount - $paid;

        return max(0.0, $due);
    }

    /**
     * Adds a service to the bill.
     * Wrap DB write in try/catch so a missing model or DB schema issue surfaces clearly in logs.
     */
    public function addService($service)
    {
        // Basic validation to avoid calling properties on null
        if (empty($service) || !isset($service->id)) {
            throw new \InvalidArgumentException('Invalid service passed to Bill::addService');
        }

        $itemPrice = (float) ($service->price ?? 0);

        try {
            return $this->items()->create([
                'service_id'       => $service->id,
                'quantity'         => 1,
                'unit_price'       => $itemPrice,
                'total_price'      => $itemPrice,
                'insurance_amount' => 0.0,
                'patient_co_pay'   => $itemPrice,
            ]);
        } catch (\Throwable $e) {
            // Re-throw with clearer message so the log shows what's wrong (missing table/column/model, etc.)
            throw new \RuntimeException('Failed to add bill item. Check BillItem model, migration and DB columns. Original: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Recalculate totals from items and persist.
     */
    public function recalculateTotals()
    {
        try {
            $totalAmount = (float) $this->items()->sum('total_price');
            $insuranceAmount = (float) $this->items()->sum('insurance_amount');
            $patientCoPay = (float) $this->items()->sum('patient_co_pay');

            $this->total_amount = $totalAmount;
            $this->insurance_amount = $insuranceAmount;
            $this->patient_co_pay = $patientCoPay;

            if ($this->balance_due > 0 && in_array($this->status, ['Paid', 'Void'], true)) {
                $this->status = 'Unpaid';
            }

            $this->save();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to recalculate bill totals. Check BillItem table/columns. Original: ' . $e->getMessage(), 0, $e);
        }
    }
}
