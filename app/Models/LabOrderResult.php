<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabOrderResult extends Model
{
    use HasFactory;

    protected $table = 'lab_order_results';

    protected $fillable = ['lab_order_id', 'lab_test_id', 'result', 'is_abnormal', 'comment'];

    public function test()
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class);
    }
}
