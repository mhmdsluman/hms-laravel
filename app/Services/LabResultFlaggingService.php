<?php

namespace App\Services;

use App\Models\Service;
use App\Models\ReferenceRange;

class LabResultFlaggingService
{
    public static function getFlag(Service $service, $value, int $ageInDays, string $gender): ?string
    {
        $range = ReferenceRange::where('service_id', $service->id)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                      ->orWhere('gender', 'any');
            })
            ->where('age_min', '<=', $ageInDays)
            ->where(function ($query) use ($ageInDays) {
                $query->whereNull('age_max')
                      ->orWhere('age_max', '>=', $ageInDays);
            })
            ->first();

        if (!$range) {
            return 'No Range';
        }

        if (!is_null($range->normal_text)) {
            return strtolower(trim($value)) === strtolower(trim($range->normal_text)) ? 'Normal' : 'Abnormal';
        }

        if (is_numeric($value)) {
            if (!is_null($range->range_low) && $value < $range->range_low) {
                return 'Low';
            }
            if (!is_null($range->range_high) && $value > $range->range_high) {
                return 'High';
            }
        }

        return 'Normal';
    }
}
