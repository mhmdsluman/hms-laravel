<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CbcParameterRangesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cbc_parameter_ranges')->insert([
            // WBC
            ['parameter' => 'wbc', 'unit' => '10^9/L', 'min' => 4.0, 'max' => 11.0, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            // Neutrophils
            ['parameter' => 'neutrophils_abs', 'unit' => '10^9/L', 'min' => 1.8, 'max' => 7.5, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            ['parameter' => 'neutrophils_pct', 'unit' => '%', 'min' => 40, 'max' => 75, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            // Lymphocytes
            ['parameter' => 'lymphocytes_abs', 'unit' => '10^9/L', 'min' => 1.0, 'max' => 4.0, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            ['parameter' => 'lymphocytes_pct', 'unit' => '%', 'min' => 20, 'max' => 45, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            // RBC
            ['parameter' => 'rbc', 'unit' => '10^12/L', 'min' => 4.5, 'max' => 5.9, 'min_age_days' => 6570, 'gender' => 'male', 'label' => 'Adult Male'],
            ['parameter' => 'rbc', 'unit' => '10^12/L', 'min' => 4.0, 'max' => 5.2, 'min_age_days' => 6570, 'gender' => 'female', 'label' => 'Adult Female'],
            // Hemoglobin
            ['parameter' => 'hb', 'unit' => 'g/dL', 'min' => 13.5, 'max' => 17.5, 'min_age_days' => 6570, 'gender' => 'male', 'label' => 'Adult Male'],
            ['parameter' => 'hb', 'unit' => 'g/dL', 'min' => 12.0, 'max' => 15.5, 'min_age_days' => 6570, 'gender' => 'female', 'label' => 'Adult Female'],
            // Hematocrit
            ['parameter' => 'hct', 'unit' => '%', 'min' => 41, 'max' => 53, 'min_age_days' => 6570, 'gender' => 'male', 'label' => 'Adult Male'],
            ['parameter' => 'hct', 'unit' => '%', 'min' => 36, 'max' => 46, 'min_age_days' => 6570, 'gender' => 'female', 'label' => 'Adult Female'],
            // MCV
            ['parameter' => 'mcv', 'unit' => 'fL', 'min' => 80, 'max' => 100, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            // MCH
            ['parameter' => 'mch', 'unit' => 'pg', 'min' => 27, 'max' => 34, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            // MCHC
            ['parameter' => 'mchc', 'unit' => 'g/dL', 'min' => 32, 'max' => 36, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            // RDW-CV
            ['parameter' => 'rdw_cv', 'unit' => '%', 'min' => 11.5, 'max' => 14.5, 'min_age_days' => 6570, 'gender' => 'any', 'label' => 'Adult'],
            // Platelets
            ['parameter' => 'plt', 'unit' => '10^9/L', 'min' => 150, 'max' => 450, 'min_age_days' => 0, 'gender' => 'any', 'label' => 'All Ages'],
            // MPV
            ['parameter' => 'mpv', 'unit' => 'fL', 'min' => 7.5, 'max' => 12.5, 'min_age_days' => 0, 'gender' => 'any', 'label' => 'All Ages'],
             // PCT
             ['parameter' => 'pct', 'unit' => '%', 'min' => 0.15, 'max' => 0.45, 'min_age_days' => 0, 'gender' => 'any', 'label' => 'All Ages'],
        ]);
    }
}
