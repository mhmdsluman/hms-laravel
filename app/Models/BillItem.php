<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'service_id',
        'quantity',
        'unit_price',
        'total_price',
        'insurance_amount',
        'patient_co_pay',
    ];

    protected $casts = [
        'quantity'         => 'integer',
        'unit_price'       => 'float',
        'total_price'      => 'float',
        'insurance_amount' => 'float',
        'patient_co_pay'   => 'float',
    ];

    /**
     * Belongs to service
     */
    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    /**
     * Belongs to bill
     */
    public function bill()
    {
        return $this->belongsTo(\App\Models\Bill::class);
    }
}
