<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillableEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'service_id',
        'bill_id',
        'status',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
