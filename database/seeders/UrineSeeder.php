<?php

namespace Database\Seeders;

use App\Models\LabTest;
use App\Models\ReferenceRange;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UrineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $urinePanel = LabTest::create([
                'name' => 'Urinalysis',
                'code' => 'UA',
                'is_panel' => true,
            ]);

            $parameters = $this->getParameters();

            foreach ($parameters as $param) {
                $service = Service::create([
                    'name' => $param['name'],
                    'department' => 'Laboratory',
                ]);

                $urinePanel->tests()->attach($service->id);

                if (isset($param['ranges'])) {
                    foreach ($param['ranges'] as $range) {
                        ReferenceRange::create(array_merge($range, ['service_id' => $service->id]));
                    }
                }
            }
        });
    }

    private function getParameters(): array
    {
        return [
            // Physical Exam
            ['name' => 'Color', 'ranges' => [['normal_text' => 'Yellow']]],
            ['name' => 'Appearance', 'ranges' => [['normal_text' => 'Clear']]],
            ['name' => 'Specific Gravity', 'ranges' => [['range_low' => 1.005, 'range_high' => 1.030]]],
            ['name' => 'pH', 'ranges' => [['range_low' => 4.5, 'range_high' => 8.0]]],

            // Chemical Exam
            ['name' => 'Protein', 'ranges' => [['normal_text' => 'Negative']]],
            ['name' => 'Glucose', 'ranges' => [['normal_text' => 'Negative']]],
            ['name' => 'Ketones', 'ranges' => [['normal_text' => 'Negative']]],
            ['name' => 'Blood', 'ranges' => [['normal_text' => 'Negative']]],
            ['name' => 'Bilirubin', 'ranges' => [['normal_text' => 'Negative']]],
            ['name' => 'Urobilinogen', 'ranges' => [['normal_text' => 'Negative']]],
            ['name' => 'Nitrite', 'ranges' => [['normal_text' => 'Negative']]],
            ['name' => 'Leukocyte Esterase', 'ranges' => [['normal_text' => 'Negative']]],

            // Microscopic Exam
            ['name' => 'RBCs', 'ranges' => [['range_low' => 0, 'range_high' => 2]]],
            ['name' => 'WBCs', 'ranges' => [['range_low' => 0, 'range_high' => 2]]],
            ['name' => 'Epithelial Cells', 'ranges' => [['range_low' => 0, 'range_high' => 5]]],
            ['name' => 'Casts', 'ranges' => [['normal_text' => 'None']]],
            ['name' => 'Crystals', 'ranges' => [['normal_text' => 'None']]],
            ['name' => 'Bacteria', 'ranges' => [['normal_text' => 'None']]],
            ['name' => 'Yeast', 'ranges' => [['normal_text' => 'None']]],
            ['name' => 'Mucus', 'ranges' => [['normal_text' => 'None']]],
        ];
    }
}
