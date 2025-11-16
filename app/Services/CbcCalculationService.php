<?php

namespace App\Services;

use App\Models\Service;

class CbcCalculationService
{
    public static function calculate(array &$results): array
    {
        $calculated = [];
        $serviceIds = array_column($results, 'service_id');
        $services = Service::whereIn('id', $serviceIds)->pluck('name', 'id');
        $values = collect($results)->pluck('result', 'service_id')->mapWithKeys(function ($result, $serviceId) use ($services) {
            return [$services[$serviceId] => $result];
        });

        // WBC absolute for each differential provided
        if ($values->has('White Blood Cell Count')) {
            $wbc = (float) $values->get('White Blood Cell Count');
            if ($values->has('Neutrophils')) {
                $calculated['Neutrophils Abs'] = round(((float)$values->get('Neutrophils') / 100.0) * $wbc, 3);
            }
            if ($values->has('Lymphocytes')) {
                $calculated['Lymphocytes Abs'] = round(((float)$values->get('Lymphocytes') / 100.0) * $wbc, 3);
            }
        }

        // RBC indices
        $rbc = $values->get('Red Blood Cell Count');
        $hb = $values->get('Hemoglobin');
        $hct = $values->get('Hematocrit');

        if ($rbc && $hct) {
            $calculated['Mean Corpuscular Volume'] = round(((float)$hct * 10.0) / (float)$rbc, 2);
        }
        if ($rbc && $hb) {
            $calculated['Mean Corpuscular Hemoglobin'] = round(((float)$hb * 10.0) / (float)$rbc, 2);
        }
        if ($hct && $hb) {
            $calculated['Mean Corpuscular Hemoglobin Concentration'] = round(((float)$hb * 100.0) / (float)$hct, 2);
        }

        return $calculated;
    }
}
