<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'is_active',
    ];

    /**
     * Get the contract rules for the insurance provider.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(InsuranceContract::class);
    }
}
