<?php

namespace App\Services;

class CbcCalculator
{
    public static function calculate(array $values): array
    {
        $calc = [];
        $flags = [];

        // WBC absolute for each differential provided
        if (!empty($values['wbc'])) {
            $wbc = (float) $values['wbc'];
            $diffs = ['neutrophils_pct','lymphocytes_pct','monocytes_pct','eosinophils_pct','basophils_pct'];
            foreach ($diffs as $d) {
                if (isset($values[$d])) {
                    $pct = (float) $values[$d];
                    $calc[str_replace('_pct','_abs',$d)] = round(($pct / 100.0) * $wbc, 3);
                }
            }
        }

        // RBC indices
        $rbc = $values['rbc'] ?? null; // 10^12/L
        $hb = $values['hb'] ?? null;   // g/dL
        $hct = $values['hct'] ?? null; // %

        if ($rbc && $hct) {
            $calc['mcv'] = round(($hct * 10.0) / $rbc, 2);
        }
        if ($rbc && $hb) {
            $calc['mch'] = round(($hb * 10.0) / $rbc, 2);
        }
        if ($hct && $hb) {
            $calc['mchc'] = round(($hb * 100.0) / $hct, 2);
        }

        // Platelets
        if (isset($values['plt']) && isset($values['mpv'])) {
            $plt = (float)$values['plt'];
            $mpv = (float)$values['mpv'];
            $calc['pct'] = round(($plt * $mpv) / 10000.0, 4); // percentage e.g. 0.25
        }

        return ['calculated'=>$calc, 'flags'=>$flags];
    }
}
