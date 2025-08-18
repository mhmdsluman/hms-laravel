<?php

namespace Database\Seeders;

use App\Models\Bed;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear the table first to avoid duplicates on re-seeding
        Bed::query()->delete();

        // Create beds for General Ward
        for ($i = 1; $i <= 10; $i++) {
            Bed::create([
                'ward' => 'General Ward',
                'bed_number' => 'GW-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'Available',
            ]);
        }

        // Create beds for ICU
        for ($i = 1; $i <= 5; $i++) {
            Bed::create([
                'ward' => 'ICU',
                'bed_number' => 'ICU-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'status' => 'Available',
            ]);
        }

        // Create beds for Maternity Ward
        for ($i = 1; $i <= 8; $i++) {
            Bed::create([
                'ward' => 'Maternity Ward',
                'bed_number' => 'MW-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'Available',
            ]);
        }
    }
}
