<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = ['is_panel', 'specimen_type', 'specimen_volume', 'turnaround_time', 'methodology'];
    protected $table = 'lab_tests';

    public function panels()
    {
        return $this->belongsToMany(LabTest::class, 'lab_test_panel', 'test_id', 'panel_id');
    }

    public function tests()
    {
        return $this->belongsToMany(LabTest::class, 'lab_test_panel', 'panel_id', 'test_id');
    }
}
