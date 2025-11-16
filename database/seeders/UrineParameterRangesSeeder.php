<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UrineParameterRangesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('urine_parameter_ranges')->insert([
            // Physical Exam
            ['parameter' => 'color', 'normal_text' => 'Yellow'],
            ['parameter' => 'appearance', 'normal_text' => 'Clear'],
            ['parameter' => 'specific_gravity', 'min' => 1.005, 'max' => 1.030],
            ['parameter' => 'ph', 'min' => 4.5, 'max' => 8.0],

            // Chemical Exam
            ['parameter' => 'protein', 'normal_text' => 'Negative'],
            ['parameter' => 'glucose', 'normal_text' => 'Negative'],
            ['parameter' => 'ketones', 'normal_text' => 'Negative'],
            ['parameter' => 'blood', 'normal_text' => 'Negative'],
            ['parameter' => 'bilirubin', 'normal_text' => 'Negative'],
            ['parameter' => 'urobilinogen', 'normal_text' => 'Negative'],
            ['parameter' => 'nitrite', 'normal_text' => 'Negative'],
            ['parameter' => 'leukocyte_esterase', 'normal_text' => 'Negative'],

            // Microscopic Exam
            ['parameter' => 'rbcs', 'unit' => '/HPF', 'min' => 0, 'max' => 2],
            ['parameter' => 'wbcs', 'unit' => '/HPF', 'min' => 0, 'max' => 2],
            ['parameter' => 'epithelial_cells', 'unit' => '/HPF', 'min' => 0, 'max' => 5],
            ['parameter' => 'casts', 'unit' => '/LPF', 'normal_text' => 'None'],
            ['parameter' => 'crystals', 'unit' => '/HPF', 'normal_text' => 'None'],
            ['parameter' => 'bacteria', 'normal_text' => 'None'],
            ['parameter' => 'yeast', 'normal_text' => 'None'],
            ['parameter' => 'mucus', 'normal_text' => 'None'],
        ]);
    }
}
