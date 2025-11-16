<?php

namespace Database\Seeders;

use App\Models\LabTest;
use App\Models\ReferenceRange;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CbcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $cbcPanel = LabTest::create([
                'name' => 'Complete Blood Count (CBC)',
                'code' => 'CBC',
                'is_panel' => true,
            ]);

            $parameters = $this->getParameters();

            foreach ($parameters as $param) {
                $service = Service::create([
                    'name' => $param['name'],
                    'department' => 'Laboratory',
                ]);

                $cbcPanel->tests()->attach($service->id);

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
            ['name' => 'White Blood Cell Count', 'ranges' => [['range_low' => 4.0, 'range_high' => 11.0]]],
            ['name' => 'Neutrophils', 'ranges' => [['range_low' => 40, 'range_high' => 75]]],
            ['name' => 'Lymphocytes', 'ranges' => [['range_low' => 20, 'range_high' => 45]]],
            ['name' => 'Red Blood Cell Count', 'ranges' => [
                ['gender' => 'male', 'range_low' => 4.5, 'range_high' => 5.9],
                ['gender' => 'female', 'range_low' => 4.0, 'range_high' => 5.2],
            ]],
            ['name' => 'Hemoglobin', 'ranges' => [
                ['gender' => 'male', 'range_low' => 13.5, 'range_high' => 17.5],
                ['gender' => 'female', 'range_low' => 12.0, 'range_high' => 15.5],
            ]],
            ['name' => 'Hematocrit', 'ranges' => [
                ['gender' => 'male', 'range_low' => 41, 'range_high' => 53],
                ['gender' => 'female', 'range_low' => 36, 'range_high' => 46],
            ]],
            ['name' => 'Mean Corpuscular Volume', 'ranges' => [['range_low' => 80, 'range_high' => 100]]],
            ['name' => 'Mean Corpuscular Hemoglobin', 'ranges' => [['range_low' => 27, 'range_high' => 34]]],
            ['name' => 'Mean Corpuscular Hemoglobin Concentration', 'ranges' => [['range_low' => 32, 'range_high' => 36]]],
            ['name' => 'Red Cell Distribution Width', 'ranges' => [['range_low' => 11.5, 'range_high' => 14.5]]],
            ['name' => 'Platelet Count', 'ranges' => [['range_low' => 150, 'range_high' => 450]]],
            ['name' => 'Mean Platelet Volume', 'ranges' => [['range_low' => 7.5, 'range_high' => 12.5]]],
        ];
    }
}
