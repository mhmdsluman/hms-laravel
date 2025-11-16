<?php

namespace Database\Seeders;

use App\Models\LabTest;
use App\Models\ReferenceRange;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoolAnalysisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            // 1. Create the main "Stool Analysis" panel
            $stoolPanel = LabTest::create([
                'name' => 'Stool Analysis',
                'is_panel' => true,
            ]);

            // 2. Define all parameters and their properties
            $parameters = $this->getParameters();

            foreach ($parameters as $param) {
                // 3. Create a Service for each parameter
                $service = Service::create([
                    'name' => $param['name'],
                    'department' => 'Laboratory', // Or a more specific department if available
                ]);

                // 4. Attach the Service to the Stool Analysis panel
                $stoolPanel->tests()->attach($service->id);

                // 5. Create Reference Ranges for the parameter
                if (isset($param['range'])) {
                    ReferenceRange::create(array_merge($param['range'], ['service_id' => $service->id]));
                }
            }
        });
    }

    private function getParameters(): array
    {
        return [
            // Macroscopic Examination
            ['name' => 'Color'],
            ['name' => 'Consistency'],
            ['name' => 'Mucus', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Blood', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Undigested food particles'],
            ['name' => 'Worms', 'range' => ['normal_text' => 'None']],
            ['name' => 'Odor'],
            ['name' => 'Reaction / pH', 'range' => ['range_low' => 6.5, 'range_high' => 7.5]],

            // Microscopic Examination
            ['name' => 'Pus cells', 'range' => ['range_low' => 0, 'range_high' => 5]],
            ['name' => 'RBCs', 'range' => ['range_low' => 0, 'range_high' => 2]],
            ['name' => 'Epithelial cells'],

            // Parasites
            ['name' => 'Entamoeba histolytica cyst', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Entamoeba histolytica trophozoite', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Giardia lamblia cyst', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Giardia lamblia trophozoite', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Ascaris lumbricoides (ova)', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Trichuris trichiura (ova)', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Ancylostoma / Hookworm (ova)', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Enterobius vermicularis (ova)', 'range' => ['normal_text' => 'Absent']],
            ['name' => 'Taenia species (ova)', 'range' => ['normal_text' => 'Absent']],

            // Chemical Examination
            ['name' => 'Occult blood', 'range' => ['normal_text' => 'Negative']],
            ['name' => 'Reducing substances', 'range' => ['normal_text' => 'Negative']],
            ['name' => 'Fat globules', 'range' => ['normal_text' => 'Few']],
            ['name' => 'pH value', 'range' => ['range_low' => 6.5, 'range_high' => 7.5]],
        ];
    }
}
