<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrineTest extends Model
{
    use HasFactory;

    protected $casts = [
        'values' => 'array',
        'calculated' => 'array',
        'flags' => 'array',
    ];
}
