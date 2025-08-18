<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'outgoing_nurse_id',
        'incoming_nurse_id',
        'summary',
    ];

    public function outgoingNurse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'outgoing_nurse_id');
    }
}
