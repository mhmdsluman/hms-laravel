<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrder extends Model
{
    use HasFactory;

    protected $table = 'lab_orders';

    protected $fillable = ['order_id', 'patient_id'];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function results()
    {
        return $this->hasMany(LabOrderResult::class);
    }

    public function tests()
    {
        return $this->belongsToMany(LabTest::class, 'lab_order_results');
    }

    public function samples()
    {
        return $this->hasMany(LabSample::class);
    }
}
