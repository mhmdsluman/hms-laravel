<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $services = [
            ['name' => 'General Consultation', 'department' => 'Outpatient', 'units' => null, 'price' => 15.00],
            ['name' => 'Emergency Consultation', 'department' => 'Emergency', 'units' => null, 'price' => 25.00],
            ['name' => 'Complete Blood Count (CBC)', 'department' => 'Laboratory', 'units' => null, 'price' => 8.00],
            ['name' => 'Basic Metabolic Panel (BMP)', 'department' => 'Laboratory', 'units' => null, 'price' => 12.00],
            ['name' => 'Liver Function Tests (LFT) Panel', 'department' => 'Laboratory', 'units' => null, 'price' => 14.00],
            ['name' => 'Urinalysis (UA)', 'department' => 'Laboratory', 'units' => null, 'price' => 6.00],
            ['name' => 'Chest X-Ray', 'department' => 'Radiology', 'units' => null, 'price' => 20.00],
            ['name' => 'Abdominal Ultrasound', 'department' => 'Radiology', 'units' => null, 'price' => 45.00],
            ['name' => 'Paracetamol 500mg Tablet', 'department' => 'Pharmacy', 'units' => 'tablet', 'price' => 0.10],
            ['name' => 'Amoxicillin 500mg Capsule', 'department' => 'Pharmacy', 'units' => 'capsule', 'price' => 0.25],
            ['name' => 'Intravenous Cannulation', 'department' => 'Procedures', 'units' => null, 'price' => 5.00],
            ['name' => 'Electrocardiogram (ECG)', 'department' => 'Cardiology', 'units' => null, 'price' => 18.00],
            ['name' => 'Troponin I', 'department' => 'Laboratory', 'units' => 'ng/L', 'price' => 22.00],
            ['name' => 'Rapid COVID-19 Antigen', 'department' => 'Laboratory', 'units' => null, 'price' => 12.00],
            ['name' => 'Blood Culture', 'department' => 'Microbiology', 'units' => null, 'price' => 30.00],
            ['name' => 'HIV 4th Gen (Ag/Ab)', 'department' => 'Immunology/Serology', 'units' => null, 'price' => 10.00],
            ['name' => 'HbA1c', 'department' => 'Laboratory', 'units' => '%', 'price' => 9.00],
            ['name' => 'Glucose (Fasting)', 'department' => 'Laboratory', 'units' => 'mg/dL', 'price' => 4.00],
            ['name' => 'Urine Culture', 'department' => 'Microbiology', 'units' => null, 'price' => 18.00],
            ['name' => 'Sputum Culture', 'department' => 'Microbiology', 'units' => null, 'price' => 22.00],
        ];

        foreach ($services as $s) {
            DB::table('services')->insert(array_merge($s, ['is_active' => true, 'is_controlled_substance' => false, 'formulary_status' => null, 'created_at' => $now, 'updated_at' => $now]));
        }
    }
}
