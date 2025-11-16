<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default admin user for testing
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        $this->call([
            DataMigrationSeeder::class,
            BedSeeder::class,
            DiagnosisCodeSeeder::class,
            InventorySeeder::class,
            SettingsSeeder::class,
            LabTestsSeeder::class,
            ServicesSeeder::class,
            CbcSeeder::class,
            UrineSeeder::class,
            StoolAnalysisSeeder::class,
        ]);
    }
}
