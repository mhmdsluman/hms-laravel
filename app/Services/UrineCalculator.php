<?php

namespace App\Services;

class UrineCalculator
{
    public static function calculate(array $values): array
    {
        $calc = [];
        $flags = [];

        // Specific Gravity
        if (!empty($values['specific_gravity'])) {
            $sg = (float) $values['specific_gravity'];
            if ($sg < 1.005) $flags['specific_gravity'] = 'low';
            elseif ($sg > 1.030) $flags['specific_gravity'] = 'high';
            else $flags['specific_gravity'] = 'normal';
        }

        // pH
        if (!empty($values['ph'])) {
            $ph = (float) $values['ph'];
            if ($ph < 4.5 || $ph > 8) $flags['ph'] = 'abnormal';
            else $flags['ph'] = 'normal';
        }

        // Dipstick positive flags
        $dipstick = ['protein','glucose','ketones','blood','bilirubin','urobilinogen','nitrite','leukocyte_esterase'];
        foreach ($dipstick as $p) {
            if (!empty($values[$p]) && strtolower($values[$p]) !== 'negative') {
                $flags[$p] = 'positive';
            }
        }

        // Microscopy numeric abnormal
        if (isset($values['rbcs']) && $values['rbcs'] > 2) $flags['rbcs'] = 'high';
        if (isset($values['wbcs']) && $values['wbcs'] > 2) $flags['wbcs'] = 'high';

        return ['calculated'=>$calc, 'flags'=>$flags];
    }
}
