<?php

namespace Database\Seeders;

use App\Models\DiagnosisCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiagnosisCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiagnosisCode::query()->delete();

        $codes = [
            ['code' => 'A09', 'description' => 'Infectious gastroenteritis and colitis, unspecified'],
            ['code' => 'I10', 'description' => 'Essential (primary) hypertension'],
            ['code' => 'J02.9', 'description' => 'Acute pharyngitis, unspecified'],
            ['code' => 'J45.909', 'description' => 'Unspecified asthma, uncomplicated'],
            ['code' => 'K35.80', 'description' => 'Acute appendicitis, without perforation or peritonitis'],
            ['code' => 'M54.5', 'description' => 'Low back pain'],
            ['code' => 'R05', 'description' => 'Cough'],
            ['code' => 'R51', 'description' => 'Headache'],
        ];

        foreach ($codes as $code) {
            DiagnosisCode::create([
                'code_system' => 'ICD-10',
                'code' => $code['code'],
                'description' => $code['description'],
            ]);
        }
    }
}
