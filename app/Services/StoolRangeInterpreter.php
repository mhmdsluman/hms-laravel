<?php

namespace App\Services;

use App\Models\ReferenceRange;

class StoolRangeInterpreter
{
    /**
     * Interpret the given value against the reference range for the service.
     *
     * @param int $serviceId
     * @param mixed $value
     * @param int $ageInDays
     * @param string $gender
     * @return string 'Normal', 'Abnormal', 'Present', 'Absent', 'Critical', or 'No Range'
     */
    public static function getStatus(int $serviceId, $value, int $ageInDays, string $gender): string
    {
        $range = ReferenceRange::where('service_id', $serviceId)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                      ->orWhere('gender', 'any');
            })
            ->where('age_min', '<=', $ageInDays / 365) // age_min is in years
            ->where(function ($query) use ($ageInDays) {
                $query->whereNull('age_max')
                      ->orWhere('age_max', '>=', $ageInDays / 365); // age_max is in years
            })
            ->first();

        if (!$range) {
            return 'No Range';
        }

        if (!is_null($range->normal_text)) {
            if (strtolower(trim($value)) === strtolower(trim($range->normal_text))) {
                return $range->normal_text === 'Absent' ? 'Absent' : 'Normal';
            } else {
                return 'Present';
            }
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
