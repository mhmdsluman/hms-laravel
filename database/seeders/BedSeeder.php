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
        // Create beds idempotently using firstOrCreate so we don't attempt to delete
        // rows that may be referenced by admissions (avoids FK constraint failures).

        // Create beds for General Ward
        for ($i = 1; $i <= 10; $i++) {
            Bed::firstOrCreate([
                'bed_number' => 'GW-' . str_pad($i, 3, '0', STR_PAD_LEFT),
            ], [
                'ward' => 'General Ward',
                'status' => 'Available',
            ]);
        }

        // Create beds for ICU
        for ($i = 1; $i <= 5; $i++) {
            Bed::firstOrCreate([
                'bed_number' => 'ICU-' . str_pad($i, 2, '0', STR_PAD_LEFT),
            ], [
                'ward' => 'ICU',
                'status' => 'Available',
            ]);
        }

        // Create beds for Maternity Ward
        for ($i = 1; $i <= 8; $i++) {
            Bed::firstOrCreate([
                'bed_number' => 'MW-' . str_pad($i, 3, '0', STR_PAD_LEFT),
            ], [
                'ward' => 'Maternity Ward',
                'status' => 'Available',
            ]);
        }
    }
}
