<?php

namespace App\Services;

use App\Models\UrineParameterRange;
use Carbon\Carbon;

class UrineRangeMatcher
{
    /**
     * Find the matching range and determine the status of a given value.
     *
     * @param int $ageInDays
     * @param string $gender 'male', 'female', or 'other'
     * @param string $parameter
     * @param mixed $value
     * @return string 'normal', 'low', 'high', 'abnormal', or 'no_range'
     */
    public static function getStatus(int $ageInDays, string $gender, string $parameter, $value): string
    {
        $range = UrineParameterRange::where('parameter', $parameter)
            ->where(function ($query) use ($gender) {
                $query->where('gender', $gender)
                      ->orWhere('gender', 'any');
            })
            ->where('min_age_days', '<=', $ageInDays)
            ->where(function ($query) use ($ageInDays) {
                $query->whereNull('max_age_days')
                      ->orWhere('max_age_days', '>=', $ageInDays);
            })
            ->orderByRaw("CASE WHEN gender = 'any' THEN 1 ELSE 0 END")
            ->first();

        if (!$range) {
            return 'no_range';
        }

        if (!is_null($range->normal_text)) {
            return strtolower(trim($value)) === strtolower(trim($range->normal_text)) ? 'normal' : 'abnormal';
        }

        if (is_numeric($value)) {
            if (!is_null($range->min) && $value < $range->min) {
                return 'low';
            }
            if (!is_null($range->max) && $value > $range->max) {
                return 'high';
            }
        }

        return 'normal';
    }

    /**
     * Compute flags for all values in a Urine test.
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
            $flags[$param] = self::getStatus($ageInDays, $gender, $param, $value);
        }

        return $flags;
    }
}
