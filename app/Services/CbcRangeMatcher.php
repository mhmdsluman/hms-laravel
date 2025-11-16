<?php

namespace App\Services;

use App\Models\CbcParameterRange;
use Carbon\Carbon;

class CbcRangeMatcher
{
    /**
     * Find the matching range and determine the status of a given value.
     *
     * @param int $ageInDays
     * @param string $gender 'male', 'female', or 'other'
     * @param string $parameter
     * @param float $value
     * @return string 'normal', 'low', 'high', 'critical_low', 'critical_high', or 'no_range'
     */
    public static function getStatus(int $ageInDays, string $gender, string $parameter, float $value): string
    {
        $range = CbcParameterRange::where('parameter', $parameter)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                      ->orWhere('gender', 'any');
            })
            ->where('min_age_days', '<=', $ageInDays)
            ->where(function ($query) use ($ageInDays) {
                $query->whereNull('max_age_days')
                      ->orWhere('max_age_days', '>=', $ageInDays);
            })
            // In case of multiple matches, prefer specific gender over 'any'
            ->orderByRaw("CASE WHEN gender = 'any' THEN 1 ELSE 0 END")
            ->first();

        if (!$range) {
            return 'no_range';
        }

        if (!is_null($range->min) && $value < $range->min) {
            return 'low';
        }

        if (!is_null($range->max) && $value > $range->max) {
            return 'high';
        }

        return 'normal';
    }

    /**
     * Compute flags for all values in a CBC test.
     *
     * @param \App\Models\Patient $patient
     * @param array $values
     * @param array $calculated
     * @return array
     */
    public static function computeFlagsForTest(\App\Models\Patient $patient, array $values, array $calculated): array
    {
        $flags = [];
        $ageInDays = Carbon::parse($patient->date_of_birth)->diffInDays(Carbon::now());
        $gender = $patient->gender;

        $allParams = array_merge($values, $calculated);

        foreach ($allParams as $param => $value) {
            if (is_numeric($value)) {
                $flags[$param] = self::getStatus($ageInDays, $gender, $param, (float)$value);
            }
        }

        // Handle calculation errors from the CbcCalculator service if any were added
        if (isset($calculated['errors'])) {
            foreach ($calculated['errors'] as $key => $error) {
                $flags[$key] = $error; // e.g., 'calc_error'
            }
        }

        return $flags;
    }
}
